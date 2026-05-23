<?php
namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Service untuk pengiriman email.
 *
 * Mendukung beberapa driver:
 *   - smtp       : PHPMailer + SMTP (default, masih tetap dipertahankan)
 *   - brevo      : HTTP API Brevo (https://api.brevo.com/v3/smtp/email)
 *   - mailersend : HTTP API MailerSend (https://api.mailersend.com/v1/email)
 *   - sendpulse  : HTTP API SendPulse (OAuth2 + https://api.sendpulse.com/smtp/emails)
 *
 * Driver API berguna sebagai alternatif kalau port SMTP (587/465) diblokir
 * oleh penyedia hosting. Bila driver API gagal (HTTP error, timeout, dll),
 * service akan otomatis fallback ke SMTP supaya email tetap terkirim.
 */
class MailerService
{
    private array $settings;
    private bool $phpmailerAvailable;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        $this->phpmailerAvailable = class_exists(PHPMailer::class);
    }

    /**
     * Apakah service siap mengirim email (paling tidak salah satu driver
     * sudah dikonfigurasi & email_enabled aktif).
     */
    public function canSend(): bool
    {
        if (empty($this->settings['email_enabled'])) {
            return false;
        }

        $driver = $this->getDriver();
        if ($driver === 'smtp') {
            return $this->canSendSmtp();
        }
        if ($driver === 'brevo') {
            return $this->getSetting('email_brevo_api_key') !== '';
        }
        if ($driver === 'mailersend') {
            return $this->getSetting('email_mailersend_api_key') !== '';
        }
        if ($driver === 'sendpulse') {
            return $this->getSetting('email_sendpulse_client_id') !== ''
                && $this->getSetting('email_sendpulse_client_secret') !== '';
        }
        return false;
    }

    /**
     * Kirim email ke 1 penerima. Mengembalikan true kalau berhasil.
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (!$this->canSend() || $toEmail === '') {
            return false;
        }

        $driver = $this->getDriver();
        $ok = false;

        if ($driver === 'brevo') {
            $ok = $this->sendBrevo($toEmail, $toName, $subject, $htmlBody, $textBody);
        } elseif ($driver === 'mailersend') {
            $ok = $this->sendMailerSend($toEmail, $toName, $subject, $htmlBody, $textBody);
        } elseif ($driver === 'sendpulse') {
            $ok = $this->sendSendPulse($toEmail, $toName, $subject, $htmlBody, $textBody);
        } elseif ($driver === 'smtp') {
            $ok = $this->sendSmtp($toEmail, $toName, $subject, $htmlBody, $textBody);
        }

        // Fallback otomatis: kalau driver API gagal & SMTP terkonfigurasi,
        // coba lagi via SMTP supaya email tetap terkirim.
        if (!$ok && $driver !== 'smtp' && $this->canSendSmtp()) {
            error_log("MailerService: fallback dari driver '{$driver}' ke SMTP");
            $ok = $this->sendSmtp($toEmail, $toName, $subject, $htmlBody, $textBody);
        }

        return $ok;
    }

    // --- DRIVER: SMTP (PHPMailer) ---

    private function canSendSmtp(): bool
    {
        if (!$this->phpmailerAvailable) {
            return false;
        }
        return $this->getSetting('email_smtp_host') !== ''
            && $this->getSetting('email_smtp_username') !== ''
            && $this->getSetting('email_smtp_password') !== '';
    }

    private function sendSmtp(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (!$this->canSendSmtp()) {
            return false;
        }

        $host = $this->getSetting('email_smtp_host');
        $port = (int) $this->getSetting('email_smtp_port', 587);
        $username = $this->getSetting('email_smtp_username');
        $password = $this->getSetting('email_smtp_password');
        $fromName = $this->getSetting('email_from_name', $this->getSetting('store_name', 'Anyeong Gift'));
        $fromAddress = $this->getSetting('email_from_address', $username);
        $encryption = strtolower($this->getSetting('email_smtp_encryption', 'tls'));

        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->Port = $port ?: 587;

            // Batasi waktu koneksi SMTP supaya request HTTP tidak menggantung
            // berlama-lama saat server SMTP tidak terjangkau (mis. port 587
            // diblok hosting). Default PHPMailer 300 detik => terlalu lama.
            $mail->Timeout = 10;
            $mail->SMTPKeepAlive = false;

            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->setFrom($fromAddress ?: $username, $fromName ?: $username);
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);

            return $mail->send();
        } catch (Exception $e) {
            error_log('MailerService SMTP send failed: ' . $e->getMessage());
            return false;
        }
    }

    // --- DRIVER: BREVO (HTTP API) ---
    // Dokumentasi: https://developers.brevo.com/reference/sendtransacemail

    private function sendBrevo(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $apiKey = $this->getSetting('email_brevo_api_key');
        if ($apiKey === '') {
            return false;
        }

        $fromAddress = $this->getSetting('email_from_address');
        $fromName = $this->getSetting('email_from_name', $this->getSetting('store_name', 'Anyeong Gift'));
        if ($fromAddress === '') {
            error_log('MailerService Brevo: email_from_address kosong');
            return false;
        }

        $payload = [
            'sender' => ['name' => $fromName ?: $fromAddress, 'email' => $fromAddress],
            'to' => [['email' => $toEmail, 'name' => $toName ?: $toEmail]],
            'subject' => $subject,
            'htmlContent' => $htmlBody,
        ];
        if ($textBody !== '') {
            $payload['textContent'] = $textBody;
        }

        $response = $this->httpPostJson(
            'https://api.brevo.com/v3/smtp/email',
            $payload,
            [
                'Accept: application/json',
                'api-key: ' . $apiKey,
            ]
        );

        if ($response['ok']) {
            return true;
        }
        error_log('MailerService Brevo send failed: HTTP ' . $response['status'] . ' ' . $response['body']);
        return false;
    }

    // --- DRIVER: MAILERSEND (HTTP API) ---
    // Dokumentasi: https://developers.mailersend.com/api/v1/email.html

    private function sendMailerSend(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $apiKey = $this->getSetting('email_mailersend_api_key');
        if ($apiKey === '') {
            return false;
        }

        $fromAddress = $this->getSetting('email_from_address');
        $fromName = $this->getSetting('email_from_name', $this->getSetting('store_name', 'Anyeong Gift'));
        if ($fromAddress === '') {
            error_log('MailerService MailerSend: email_from_address kosong');
            return false;
        }

        $payload = [
            'from' => ['email' => $fromAddress, 'name' => $fromName ?: $fromAddress],
            'to' => [['email' => $toEmail, 'name' => $toName ?: $toEmail]],
            'subject' => $subject,
            'html' => $htmlBody,
            'text' => $textBody !== '' ? $textBody : strip_tags($htmlBody),
        ];

        $response = $this->httpPostJson(
            'https://api.mailersend.com/v1/email',
            $payload,
            [
                'Accept: application/json',
                'Authorization: Bearer ' . $apiKey,
            ]
        );

        // MailerSend mengembalikan 202 Accepted untuk sukses.
        if ($response['ok']) {
            return true;
        }
        error_log('MailerService MailerSend send failed: HTTP ' . $response['status'] . ' ' . $response['body']);
        return false;
    }

    // --- DRIVER: SENDPULSE (OAuth2 + HTTP API) ---
    // Dokumentasi: https://sendpulse.com/integrations/api/smtp

    private function sendSendPulse(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $clientId = $this->getSetting('email_sendpulse_client_id');
        $clientSecret = $this->getSetting('email_sendpulse_client_secret');
        if ($clientId === '' || $clientSecret === '') {
            return false;
        }

        $token = $this->getSendPulseToken($clientId, $clientSecret);
        if ($token === null) {
            return false;
        }

        $fromAddress = $this->getSetting('email_from_address');
        $fromName = $this->getSetting('email_from_name', $this->getSetting('store_name', 'Anyeong Gift'));
        if ($fromAddress === '') {
            error_log('MailerService SendPulse: email_from_address kosong');
            return false;
        }

        $emailPayload = [
            'html' => base64_encode($htmlBody),
            'text' => $textBody !== '' ? $textBody : strip_tags($htmlBody),
            'subject' => $subject,
            'from' => ['name' => $fromName ?: $fromAddress, 'email' => $fromAddress],
            'to' => [['name' => $toName ?: $toEmail, 'email' => $toEmail]],
        ];

        $response = $this->httpPostJson(
            'https://api.sendpulse.com/smtp/emails',
            ['email' => $emailPayload],
            [
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ]
        );

        if ($response['ok']) {
            return true;
        }
        error_log('MailerService SendPulse send failed: HTTP ' . $response['status'] . ' ' . $response['body']);
        return false;
    }

    private function getSendPulseToken(string $clientId, string $clientSecret): ?string
    {
        $response = $this->httpPostJson(
            'https://api.sendpulse.com/oauth/access_token',
            [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
            ['Accept: application/json']
        );

        if (!$response['ok']) {
            error_log('MailerService SendPulse token failed: HTTP ' . $response['status'] . ' ' . $response['body']);
            return null;
        }

        $decoded = json_decode($response['body'], true);
        if (!is_array($decoded) || empty($decoded['access_token'])) {
            error_log('MailerService SendPulse token response invalid: ' . $response['body']);
            return null;
        }
        return (string) $decoded['access_token'];
    }

    // --- UTILITIES ---

    /**
     * Helper kecil untuk POST JSON ke API HTTP. Pakai cURL kalau ada,
     * fallback ke stream context. Timeout dibatasi supaya request HTTP
     * pemanggil tidak menggantung lama bila API endpoint lambat / down.
     *
     * @return array{ok: bool, status: int, body: string}
     */
    private function httpPostJson(string $url, array $payload, array $headers = []): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $headers[] = 'Content-Type: application/json';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $responseBody = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($responseBody === false) {
                return ['ok' => false, 'status' => 0, 'body' => 'cURL error: ' . $error];
            }
            return [
                'ok' => $status >= 200 && $status < 300,
                'status' => $status,
                'body' => (string) $responseBody,
            ];
        }

        // Fallback: file_get_contents + stream context (jika cURL tidak ada).
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);
        $responseBody = @file_get_contents($url, false, $context);
        $status = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $m)) {
                    $status = (int) $m[1];
                    break;
                }
            }
        }
        if ($responseBody === false) {
            return ['ok' => false, 'status' => 0, 'body' => 'stream POST failed'];
        }
        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'body' => (string) $responseBody,
        ];
    }

    private function getDriver(): string
    {
        $driver = strtolower($this->getSetting('email_driver', 'smtp'));
        if (!in_array($driver, ['smtp', 'brevo', 'mailersend', 'sendpulse'], true)) {
            return 'smtp';
        }
        return $driver;
    }

    private function getSetting(string $key, $default = ''): string
    {
        if (!isset($this->settings[$key]) || $this->settings[$key] === null) {
            return (string) $default;
        }

        $value = $this->settings[$key];
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return trim((string) $value);
    }
}
