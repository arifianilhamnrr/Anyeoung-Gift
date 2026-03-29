<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
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

    unset($_SESSION['cart']);
    $_SESSION['checkout_success_order_id'] = $orderId;

    header('Location: ../index.php?page=orders');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die('Checkout gagal: ' . $e->getMessage());
}