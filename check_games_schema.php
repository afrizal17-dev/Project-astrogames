<?php
require_once __DIR__ . '/config/database.php';
$db = getDBConnection();
$stmt = $db->query("DESCRIBE game_requirements");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
