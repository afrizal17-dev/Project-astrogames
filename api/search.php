<?php
// api/search.php — Live Search API (JSON)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 2) {
    echo json_encode(['results' => [], 'count' => 0]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, name, slug, genre, price, cover, difficulty 
     FROM games 
     WHERE name LIKE :q OR genre LIKE :q OR developer LIKE :q OR publisher LIKE :q
     ORDER BY 
       CASE WHEN name LIKE :exact THEN 0 ELSE 1 END,
       name ASC
     LIMIT 8"
);
$stmt->execute([':q' => "%$q%", ':exact' => "$q%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$formatted = array_map(function($g) {
    return [
        'id'         => $g['id'],
        'name'       => $g['name'],
        'slug'       => $g['slug'],
        'genre'      => $g['genre'],
        'price'      => $g['price'],
        'cover'      => $g['cover'],
        'difficulty' => $g['difficulty'],
        'price_fmt'  => $g['price'] == 0 ? 'Gratis' : 'Rp ' . number_format($g['price'], 0, ',', '.'),
    ];
}, $results);

echo json_encode(['results' => $formatted, 'count' => count($formatted)]);
