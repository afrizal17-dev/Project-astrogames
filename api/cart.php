<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$gameId = (int)($input['game_id'] ?? 0);
$action = $input['action'] ?? ''; // 'add' or 'remove'

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'redirect', 'url' => (defined('BASE_URL') ? BASE_URL : '') . '/login.php']);
    exit;
}

if (!$gameId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid game ID']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
if ($action === 'add') {
    if (!in_array($gameId, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $gameId;
    }
    $message = 'Game berhasil ditambahkan ke Keranjang <i class="fas fa-shopping-cart"></i>';
} elseif ($action === 'remove') {
    if (($key = array_search($gameId, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
    }
    $message = 'Game dihapus dari Keranjang';
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => $message,
    'count' => count($_SESSION['cart'])
]);
