<?php
namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class MailerService
{
    private array $settings;
    private bool $available;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        $this->available = class_exists(PHPMailer::class);
    }

    public function canSend(): bool
    {
        if (!$this->available) {
            return false;
        }

        if (empty($this->settings['email_enabled'])) {
            return false;
        }

        return $this->getSetting('email_smtp_host') !== ''
            && $this->getSetting('email_smtp_username') !== ''
            && $this->getSetting('email_smtp_password') !== '';
    }

    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (!$this->canSend() || $toEmail === '') {
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
            error_log('MailerService send failed: ' . $e->getMessage());
            return false;
        }
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
