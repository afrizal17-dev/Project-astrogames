<?php
// api/check-compat.php — Inline Compatibility Checker (JSON POST)
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/recommendation-engine.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['cpu']) || empty($data['gpu']) || empty($data['game_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak lengkap']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id");
$stmt->execute([':id' => (int)$data['game_id']]);
$game = $stmt->fetch();

if (!$game) {
    http_response_code(404);
    echo json_encode(['error' => 'Game tidak ditemukan']);
    exit;
}

$user_spec = [
    'cpu'  => trim($data['cpu']),
    'ram'  => (int)($data['ram'] ?? 8),
    'gpu'  => trim($data['gpu']),
    'vram' => (int)($data['vram'] ?? 4),
    'os'   => trim($data['os'] ?? 'Windows 10'),
];

$result = analyzeGameCompatibility($user_spec, $game);

echo json_encode($result);
