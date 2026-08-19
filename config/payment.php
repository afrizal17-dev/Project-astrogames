<?php
// config/payment.php
// Konfigurasi Payment Gateway Midtrans QRIS & Demo Payment Mode

return [
    // Mode Demo Pembayaran (Set true untuk menguji pembayaran QRIS lokal di XAMPP tanpa API Key)
    'ENABLE_DEMO_PAYMENT' => true,

    // Konfigurasi Midtrans Gateway (Isi saat siap dipublikasikan/production)
    'MIDTRANS_SERVER_KEY' => 'SB-Mid-server-YOUR_SERVER_KEY_PLACEHOLDER',
    'MIDTRANS_CLIENT_KEY' => 'SB-Mid-client-YOUR_CLIENT_KEY_PLACEHOLDER',
    'IS_PRODUCTION'       => false, // false untuk Sandbox, true untuk Production
    'IS_SANITIZED'        => true,
    'IS_3DS'              => true,

    // Informasi Merchant GameCheck
    'MERCHANT_NAME'       => 'GameCheck Store',
    'PAYMENT_METHODS'     => ['qris', 'gopay', 'shopeepay', 'bank_transfer'],
];
