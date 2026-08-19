<?php
require_once __DIR__ . '/config/database.php';

echo "<h3>TEST KONEKSI DATABASE HOSTING</h3>";

try {
    $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=%s", DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "<b style='color:green;'>KONEKSI SUKSES!</b> Database terhubung.<br>";
} catch (PDOException $e) {
    echo "<b style='color:red;'>KONEKSI GAGAL (Koneksi ke DB Name):</b> " . $e->getMessage() . "<br><br>";
    
    echo "Mencoba koneksi tanpa nama database...<br>";
    try {
        $hostDsn = sprintf("mysql:host=%s;port=%s;charset=%s", DB_HOST, DB_PORT, DB_CHARSET);
        $hostPdo = new PDO($hostDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "<b style='color:green;'>KONEKSI SUKSES (Tanpa DB)!</b> User dan Password Hostinger Anda benar, tapi database `" . DB_NAME . "` mungkin tidak ada atau Anda tidak punya izin mengaksesnya.<br>";
    } catch (PDOException $ex) {
        echo "<b style='color:red;'>KONEKSI GAGAL TOTAL:</b> " . $ex->getMessage() . "<br>";
        echo "<br><b>KESIMPULAN:</b> Website Anda GAGAL terhubung ke database. Itulah kenapa semua akun dianggap kosong.<br>";
        echo "Silakan cek kembali <code>config/database.php</code> Anda (DB_NAME, DB_USER, DB_PASS, dan DB_HOST).";
    }
}
?>
