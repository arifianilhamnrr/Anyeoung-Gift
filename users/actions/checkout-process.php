<?php
session_start();
require_once '../../config/database.php';
require_once __DIR__ . '/email-helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

// Pilih sumber data checkout: bucket buy_now (instant checkout dari tombol
// "Bayar Sekarang" di halaman produk) atau cart biasa. Keranjang utama tidak
// pernah dipakai untuk buy_now agar tidak ikut tercheckout.
$isBuyNow = !empty($_SESSION['buy_now']);
$cart = $isBuyNow ? $_SESSION['buy_now'] : ($_SESSION['cart'] ?? []);
$userId = $_SESSION['user_id'];

if (empty($cart)) {
    header('Location: ../index.php?page=cart');
    exit;
}

$addressId = $_POST['address_id'] ?? null;
$paymentMethodId = $_POST['payment_method_id'] ?? null;

if (!$addressId || !$paymentMethodId) {
    header('Location: ../index.php?page=checkout');
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM addresses
    WHERE id = ? AND user_id = ? AND type = 'user'
    LIMIT 1
");
$stmt->execute([$addressId, $userId]);
$address = $stmt->fetch();

if (!$address) {
    header('Location: ../index.php?page=checkout');
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM payment_methods
    WHERE id = ? AND is_active = 1
    LIMIT 1
");
$stmt->execute([$paymentMethodId]);
$paymentMethod = $stmt->fetch();

if (!$paymentMethod) {
    header('Location: ../index.php?page=checkout');
    exit;
}

$grandTotal = 0;
foreach ($cart as $item) {
    $grandTotal += (int) $item['price'];
}

$addressSnapshot = json_encode([
    'id' => $address['id'],
    'recipient_name' => $address['recipient_name'],
    'whatsapp_number' => $address['whatsapp_number'],
    'address_text' => $address['address_text'],
    'notes' => $address['notes']
], JSON_UNESCAPED_UNICODE);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, address_snapshot, total_price, status, created_at)
        VALUES (?, ?, ?, 'waiting_payment', NOW())
    ");
    $stmt->execute([$userId, $addressSnapshot, $grandTotal]);

    $orderId = $pdo->lastInsertId();

    foreach ($cart as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name_snapshot, base_price, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['product_name'],
            0,
            $item['price']
        ]);

        $orderItemId = $pdo->lastInsertId();

        if (!empty($item['options']) && is_array($item['options'])) {

            foreach ($item['options'] as $optionName => $optionValue) {
                if (is_array($optionValue)) {
                    foreach ($optionValue as $singleValue) {
                        $stmt = $pdo->prepare("
                    INSERT INTO order_item_options (
                        order_item_id,
                        option_name_snapshot,
                        option_value_snapshot,
                        additional_price,
                        custom_value
                    ) VALUES (?, ?, ?, ?, ?)
                ");
                        $stmt->execute([
                            $orderItemId,
                            $optionName,
                            $singleValue,
                            0,
                            null
                        ]);
                    }
                } else {
                    $stmt = $pdo->prepare("
                INSERT INTO order_item_options (
                    order_item_id,
                    option_name_snapshot,
                    option_value_snapshot,
                    additional_price,
                    custom_value
                ) VALUES (?, ?, ?, ?, ?)
            ");
                    $stmt->execute([
                        $orderItemId,
                        $optionName,
                        $optionValue,
                        0,
                        null
                    ]);
                }
            }
        }

        if (!empty($item['custom_input'])) {
            $stmt = $pdo->prepare("
        INSERT INTO order_item_options (
            order_item_id,
            option_name_snapshot,
            option_value_snapshot,
            additional_price,
            custom_value
        ) VALUES (?, ?, ?, ?, ?)
    ");
            $stmt->execute([
                $orderItemId,
                'Tulisan Pita',
                '-',
                0,
                $item['custom_input']
            ]);
        }

        if (!empty($item['custom_input'])) {
            $stmt = $pdo->prepare("
                INSERT INTO order_item_options (
                    order_item_id,
                    option_name_snapshot,
                    option_value_snapshot,
                    additional_price,
                    custom_value
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderItemId,
                'Tulisan Custom',
                '-',
                0,
                $item['custom_input']
            ]);
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO payments (
            order_id,
            payment_method_id,
            amount,
            status,
            proof_image,
            paid_at,
            created_at
        ) VALUES (?, ?, ?, 'pending', NULL, NULL, NOW())
    ");
    $stmt->execute([
        $orderId,
        $paymentMethodId,
        $grandTotal
    ]);

    $pdo->commit();

    // Siapkan data buat email (tetapi belum kirim). Kirim email dilakukan
    // setelah response di-flush ke browser supaya halaman tidak ngegantung
    // menunggu SMTP.
    $emailData = null;
    try {
        $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user && !empty($user['email'])) {
            $orderNumber = 'ORD-' . str_pad((string) $orderId, 5, '0', STR_PAD_LEFT);
            $storeSettings = fetchStoreSettings($pdo);
            $storeName = $storeSettings['store_name'] ?? 'Anyeong Gift';
            $statusLabel = formatOrderStatusLabel('waiting_payment');
            $totalFormatted = formatRupiah((int) $grandTotal);
            $methodName = $paymentMethod['name'] ?? 'Metode pembayaran';
            $methodType = strtolower($paymentMethod['type'] ?? '');
            $storeAddress = fetchStoreAddress($pdo);
            $storeAddressText = $storeAddress['address_text'] ?? '';
            $storeWhatsapp = $storeSettings['whatsapp_admin'] ?? '';

            $orderDate = date('d M Y H:i');
            $itemRows = '';
            $itemLines = [];

            foreach ($cart as $item) {
                $name = htmlspecialchars((string) ($item['product_name'] ?? '-'));
                $price = (int) ($item['price'] ?? 0);
                $optionLines = [];

                if (!empty($item['options']) && is_array($item['options'])) {
                    foreach ($item['options'] as $optionName => $optionValue) {
                        if (is_array($optionValue)) {
                            $value = implode(', ', $optionValue);
                        } else {
                            $value = (string) $optionValue;
                        }
                        $optionLines[] = htmlspecialchars((string) $optionName) . ': ' . htmlspecialchars($value);
                    }
                }

                if (!empty($item['custom_input'])) {
                    $optionLines[] = 'Catatan: ' . htmlspecialchars((string) $item['custom_input']);
                }

                $optionHtml = '';
                if (!empty($optionLines)) {
                    $optionHtml = '<div style="margin-top:4px;color:#555;font-size:12px;">' . implode('<br>', $optionLines) . '</div>';
                }

                $itemRows .= "<tr><td style=\"padding:8px 10px;border-bottom:1px solid #eee;\"><strong>{$name}</strong>{$optionHtml}</td><td style=\"padding:8px 10px;border-bottom:1px solid #eee;text-align:right;white-space:nowrap;\">Rp " . number_format($price, 0, ',', '.') . "</td></tr>";
                $itemLines[] = '- ' . ($item['product_name'] ?? '-') . ' : Rp ' . number_format($price, 0, ',', '.');
            }

            if ($itemRows === '') {
                $itemRows = '<tr><td style="padding:10px;color:#666;" colspan="2">Tidak ada item.</td></tr>';
            }

            $nextStep = $methodType === 'onsite'
                ? 'Silakan lakukan pembayaran saat pengambilan di toko.'
                : 'Silakan selesaikan pembayaran dan unggah bukti transfer agar pesanan segera diproses.';

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scriptDir = rtrim(str_replace('/actions', '', dirname($_SERVER['SCRIPT_NAME'])), '/');
            $paymentLink = $scheme . '://' . $host . $scriptDir . '/index.php?page=payment_upload&order_id=' . (int) $orderId;
            $logoUrl = $scheme . '://' . $host . '/assets/images/anyeong-logo.svg';
            $paymentLinkHtml = $methodType !== 'onsite'
                ? '<p style="margin:8px 0 0;">Link pembayaran: <a href="' . $paymentLink . '">' . $paymentLink . '</a></p>'
                : '';

            $customerSubject = "Pesanan {$orderNumber} Berhasil Dibuat - {$storeName}";
            $customerBody = "
                <div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #111;\">
                    <div style=\"margin-bottom:12px;\"><img src=\"{$logoUrl}\" alt=\"Logo {$storeName}\" style=\"height:48px;\" /></div>
                    <h2 style=\"margin: 0 0 8px;\">Konfirmasi Pesanan</h2>
                    <p>Halo <strong>" . htmlspecialchars($user['name']) . "</strong>,</p>
                    <p>Terima kasih telah berbelanja di {$storeName}. Pesanan kamu sudah berhasil dibuat.</p>
                    <table style=\"width:100%;border-collapse:collapse;margin:12px 0 8px;\">
                        <tr><td style=\"padding:6px 0;\">Nomor Pesanan</td><td style=\"padding:6px 0;text-align:right;\"><strong>{$orderNumber}</strong></td></tr>
                        <tr><td style=\"padding:6px 0;\">Tanggal Pesanan</td><td style=\"padding:6px 0;text-align:right;\">{$orderDate}</td></tr>
                        <tr><td style=\"padding:6px 0;\">Status</td><td style=\"padding:6px 0;text-align:right;\">{$statusLabel}</td></tr>
                        <tr><td style=\"padding:6px 0;\">Metode Pembayaran</td><td style=\"padding:6px 0;text-align:right;\">" . htmlspecialchars($methodName) . "</td></tr>
                        <tr><td style=\"padding:6px 0;font-weight:bold;\">Total</td><td style=\"padding:6px 0;text-align:right;font-weight:bold;\">Rp {$totalFormatted}</td></tr>
                    </table>
                    <h4 style=\"margin:16px 0 8px;\">Detail Pesanan</h4>
                    <table style=\"width:100%;border-collapse:collapse;\">
                        <thead>
                            <tr>
                                <th style=\"text-align:left;background:#111;color:#fff;padding:8px 10px;font-size:12px;\">Item</th>
                                <th style=\"text-align:right;background:#111;color:#fff;padding:8px 10px;font-size:12px;\">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemRows}
                        </tbody>
                    </table>
                    <p style=\"margin:12px 0 0;\">{$nextStep}</p>
                    {$paymentLinkHtml}
                    <h4 style=\"margin:16px 0 8px;\">Alamat Toko</h4>
                    <p style=\"margin:0;white-space:pre-line;\">" . htmlspecialchars($storeAddressText) . "</p>
                    <p style=\"margin:8px 0 0;\">WhatsApp Toko: " . htmlspecialchars($storeWhatsapp) . "</p>
                    <p style=\"margin-top:16px;\">Terima kasih telah berbelanja di {$storeName}.</p>
                </div>
            ";
            $customerText = "Halo {$user['name']}, terima kasih telah berbelanja di {$storeName}.\n";
            $customerText .= "Nomor pesanan: {$orderNumber}\n";
            $customerText .= "Tanggal pesanan: {$orderDate}\n";
            $customerText .= "Status: {$statusLabel}\n";
            $customerText .= "Metode pembayaran: {$methodName}\n";
            $customerText .= "Total: Rp {$totalFormatted}\n\n";
            $customerText .= "Detail pesanan:\n" . implode("\n", $itemLines) . "\n\n";
            $customerText .= $nextStep . "\n";
            if ($methodType !== 'onsite') {
                $customerText .= "Link pembayaran: {$paymentLink}\n\n";
            }
            if ($storeAddressText !== '') {
                $customerText .= "Alamat toko:\n{$storeAddressText}\n";
            }
            if ($storeWhatsapp !== '') {
                $customerText .= "WhatsApp toko: {$storeWhatsapp}\n";
            }
            $customerText .= "\nTerima kasih telah berbelanja di {$storeName}.";

            $adminRecipients = fetchAdminRecipients($pdo);
            if (!empty($adminRecipients)) {
                $adminSubject = "Pesanan Baru {$orderNumber} - {$storeName}";
                $adminBody = "
                    <div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #111;\">
                        <div style=\"margin-bottom:12px;\"><img src=\"{$logoUrl}\" alt=\"Logo {$storeName}\" style=\"height:48px;\" /></div>
                        <h2 style=\"margin: 0 0 8px;\">Pesanan Baru Masuk</h2>
                        <p>Pesanan baru telah dibuat.</p>
                        <table style=\"width:100%;border-collapse:collapse;margin:12px 0 8px;\">
                            <tr><td style=\"padding:6px 0;\">Nomor Pesanan</td><td style=\"padding:6px 0;text-align:right;\"><strong>{$orderNumber}</strong></td></tr>
                            <tr><td style=\"padding:6px 0;\">Tanggal Pesanan</td><td style=\"padding:6px 0;text-align:right;\">{$orderDate}</td></tr>
                            <tr><td style=\"padding:6px 0;\">Metode Pembayaran</td><td style=\"padding:6px 0;text-align:right;\">" . htmlspecialchars($methodName) . "</td></tr>
                            <tr><td style=\"padding:6px 0;font-weight:bold;\">Total</td><td style=\"padding:6px 0;text-align:right;font-weight:bold;\">Rp {$totalFormatted}</td></tr>
                        </table>
                        <h4 style=\"margin:16px 0 8px;\">Detail Pesanan</h4>
                        <table style=\"width:100%;border-collapse:collapse;\">
                            <thead>
                                <tr>
                                    <th style=\"text-align:left;background:#111;color:#fff;padding:8px 10px;font-size:12px;\">Item</th>
                                    <th style=\"text-align:right;background:#111;color:#fff;padding:8px 10px;font-size:12px;\">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$itemRows}
                            </tbody>
                        </table>
                        <p style=\"margin-top:16px;\">Silakan cek dashboard admin untuk proses lebih lanjut.</p>
                    </div>
                ";
                $adminText = "Pesanan baru {$orderNumber} telah dibuat. Total Rp {$totalFormatted}.";
                $adminText .= "\nDetail pesanan:\n" . implode("\n", $itemLines);
            } else {
                $adminSubject = '';
                $adminBody = '';
                $adminText = '';
            }

            $emailData = [
                'customer_email' => $user['email'],
                'customer_name' => $user['name'],
                'customer_subject' => $customerSubject,
                'customer_body' => $customerBody,
                'customer_text' => $customerText,
                'admin_recipients' => $adminRecipients,
                'admin_subject' => $adminSubject,
                'admin_body' => $adminBody,
                'admin_text' => $adminText,
            ];
        }
    } catch (Exception $e) {
        error_log('Checkout email prep failed: ' . $e->getMessage());
    }

    // Bersihkan hanya bucket yang dipakai. Buy now tidak menyentuh keranjang
    // utama, jadi isi keranjang tetap utuh setelah pesanan buy now selesai.
    if ($isBuyNow) {
        unset($_SESSION['buy_now']);
    } else {
        unset($_SESSION['cart']);
    }
    $_SESSION['checkout_success_order_id'] = $orderId;

    // Untuk pembayaran non-onsite (transfer/QRIS/e-wallet) pembeli perlu
    // mengunggah bukti pembayaran. Arahkan langsung ke halaman pembayaran
    // pesanan baru. Pembayaran COD (onsite) tetap diarahkan ke daftar
    // pesanan agar pembeli melihat status pending.
    $methodType = strtolower($paymentMethod['type'] ?? '');
    if ($methodType !== 'onsite') {
        header('Location: ../index.php?page=payment_upload&order_id=' . (int) $orderId);
    } else {
        header('Location: ../index.php?page=orders');
    }

    // Lepaskan response ke browser dulu (header redirect sudah dikirim).
    // Email SMTP dikirim setelah ini di background supaya halaman checkout
    // tidak menggantung menunggu SMTP merespons.
    flushResponseAndContinue();

    if ($emailData !== null) {
        try {
            sendConfiguredEmail(
                $pdo,
                $emailData['customer_email'],
                $emailData['customer_name'],
                $emailData['customer_subject'],
                $emailData['customer_body'],
                $emailData['customer_text']
            );

            foreach ($emailData['admin_recipients'] as $admin) {
                $adminEmail = $admin['email'] ?? '';
                if ($adminEmail === '') {
                    continue;
                }
                $adminName = $admin['name'] ?? 'Admin';
                sendConfiguredEmail(
                    $pdo,
                    $adminEmail,
                    $adminName,
                    $emailData['admin_subject'],
                    $emailData['admin_body'],
                    $emailData['admin_text']
                );
            }
        } catch (Exception $e) {
            error_log('Checkout background email failed: ' . $e->getMessage());
        }
    }

    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die('Checkout gagal: ' . $e->getMessage());
}
