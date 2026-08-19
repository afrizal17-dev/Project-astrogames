<?php
require_once __DIR__ . '/includes/functions.php';
$stmt = dbQuery("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($tables);
?>
