<?php
// checkout/callback.php - Payment Gateway Webhook / Notification Callback Endpoint
require_once __DIR__ . '/../config/database.php';
$payment_config = require __DIR__ . '/../config/payment.php';

header('Content-Type: application/json');

// Read Notification Payload
$raw_body = file_get_contents('php://input');
$notification = json_decode($raw_body, true);

if (!$notification || !isset($notification['order_id']) || !isset($notification['transaction_status'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Callback Notification Payload']);
    exit();
}

$order_code         = $notification['order_id'];
$transaction_status = $notification['transaction_status'];
$fraud_status       = $notification['fraud_status'] ?? 'accept';
$transaction_id     = $notification['transaction_id'] ?? 'TRX-' . time();
$payment_type       = $notification['payment_type'] ?? 'qris';

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = :code LIMIT 1");
    $stmt->execute([':code' => $order_code]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Order Code Not Found']);
        exit();
    }

    if ($transaction_status == 'capture' || $transaction_status == 'settlement') {
        if ($fraud_status == 'accept') {
            // Update order status to paid
            $stmt_upd = $pdo->prepare("UPDATE orders SET payment_status = 'paid', updated_at = NOW() WHERE id = :id");
            $stmt_upd->execute([':id' => $order['id']]);

            // Record payment
            $stmt_pay = $pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_id, status, paid_at) 
                                       VALUES (:oid, :method, :txid, 'SETTLEMENT', NOW())");
            $stmt_pay->execute([
                ':oid'    => $order['id'],
                ':method' => strtoupper($payment_type),
                ':txid'   => $transaction_id
            ]);

            // Activate user product
            $stmt_prod = $pdo->prepare("INSERT INTO user_products (user_id, product_id, order_id, activated_at) 
                                        VALUES (:uid, :pid, :oid, NOW())");
            $stmt_prod->execute([
                ':uid' => $order['user_id'],
                ':pid' => $order['product_id'],
                ':oid' => $order['id']
            ]);
        }
    } elseif ($transaction_status == 'cancel' || $transaction_status == 'deny' || $transaction_status == 'expire') {
        $stmt_upd = $pdo->prepare("UPDATE orders SET payment_status = 'failed', updated_at = NOW() WHERE id = :id");
        $stmt_upd->execute([':id' => $order['id']]);
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Payment status updated successfully']);
    exit();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}
