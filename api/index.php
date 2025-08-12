<?php
require __DIR__ . '/../app/autoload.php';
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/util.php';

$initialized = false;
if (!$initialized) {
    try {
        ensure_database_initialized();
    } catch (Throwable $e) {
        if ((require __DIR__ . '/config.php')['app']['debug']) {
            json_response(['error' => 'Database not initialized: ' . $e->getMessage()], 500);
        }
        json_response(['error' => 'Server setup error'], 500);
    }
    $initialized = true;
}

$router = require __DIR__ . '/routes.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Normalize path under /api/
$baseIndex = strpos($path, '/api/');
$route = $baseIndex !== false ? substr($path, $baseIndex + 5) : trim($path, '/');
$route = trim($route, '/');

// Fallback: allow query parameter routing when rewrite is disabled
if (isset($_GET['r']) && is_string($_GET['r']) && $_GET['r'] !== '') {
    $route = trim($_GET['r'], '/');
}

try {
    // Subscription left inline for brevity; others handled by controllers/routes
    if ($route === 'subscription' && $method === 'POST') {
        $user = require_auth();
        $data = read_json_body();
        $plan = in_array(($data['plan'] ?? 'basic'), ['basic','premium','business'], true) ? $data['plan'] : 'basic';
        $price = int_or_zero($data['price'] ?? 0);
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO subscriptions (user_id, plan, price, status) VALUES (?,?,?,"active")')->execute([$user['id'], $plan, $price]);
            $pdo->prepare('UPDATE users SET membership_level = ? WHERE id = ?')->execute([$plan, $user['id']]);
            $pdo->commit();
            $_SESSION['user']['membershipLevel'] = $plan;
            json_response(['ok' => true, 'plan' => $plan]);
        } catch (Throwable $e) {
            $pdo->rollBack();
            json_response(['error' => 'Subscription failed'], 500);
        }
    }

    // Let the router handle the rest
    $router->dispatch($route, $method);
} catch (Throwable $e) {
    if ((require __DIR__ . '/config.php')['app']['debug']) {
        json_response(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
    }
    json_response(['error' => 'Server error'], 500);
} 