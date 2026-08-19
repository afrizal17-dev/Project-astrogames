<?php
require_once __DIR__ . '/config/database.php';
$db = getDBConnection();
$stmt = $db->query("DESCRIBE users");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
