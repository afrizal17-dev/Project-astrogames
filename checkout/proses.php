<?php
// checkout/proses.php - Backend Processing Pembayaran QRIS & Otomasi Aktivasi Produk
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/produk.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$order_code = trim($_POST['order_code']);
$amount     = (float)$_POST['amount'];

if ($product_id <= 0 || empty($order_code) || $amount <= 0) {
    $_SESSION['flash_error'] = "Data pembayaran tidak valid.";
    header("Location: " . BASE_URL . "/produk.php");
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Check or Insert Order
    $stmt_check = $pdo->prepare("SELECT * FROM orders WHERE order_code = :code LIMIT 1");
    $stmt_check->execute([':code' => $order_code]);
    $order = $stmt_check->fetch();

    if (!$order) {
        $stmt_ins = $pdo->prepare("INSERT INTO orders (user_id, product_id, order_code, amount, payment_status) 
                                    VALUES (:uid, :pid, :code, :amount, 'pending')");
        $stmt_ins->execute([
            ':uid'    => $user_id,
            ':pid'    => $product_id,
            ':code'   => $order_code,
            ':amount' => $amount
        ]);
        $order_id = $pdo->lastInsertId();
    } else {
        $order_id = $order['id'];
    }

    // 2. Mark Payment as PAID (Simulasi Settlement QRIS / Midtrans Callback)
    $stmt_upd_order = $pdo->prepare("UPDATE orders SET payment_status = 'paid', updated_at = NOW() WHERE id = :oid");
    $stmt_upd_order->execute([':oid' => $order_id]);

    // 3. Create Payment Log Entry
    $trx_id = 'TRX-QRIS-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $stmt_pay = $pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_id, status, paid_at) 
                               VALUES (:oid, 'QRIS', :txid, 'SETTLEMENT', NOW())");
    $stmt_pay->execute([
        ':oid'  => $order_id,
        ':txid' => $trx_id
    ]);

    // 4. Activate Product to User Account (user_products)
    $stmt_up = $pdo->prepare("INSERT INTO user_products (user_id, product_id, order_id, activated_at) 
                              VALUES (:uid, :pid, :oid, NOW())");
    $stmt_up->execute([
        ':uid' => $user_id,
        ':pid' => $product_id,
        ':oid' => $order_id
    ]);

    $pdo->commit();

    $_SESSION['flash_success'] = "Pembayaran QRIS Berhasil! Produk otomatis aktif di akun Anda.";
    header("Location: " . BASE_URL . "/member/produk.php");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = "Terjadi kesalahan saat memproses transaksi: " . $e->getMessage();
    header("Location: " . BASE_URL . "/checkout/index.php?product_id=" . $product_id);
    exit();
}
