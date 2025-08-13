<?php
use App\Http\Router;
use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\GiftController;
use App\Controllers\AdminController;
use App\Controllers\CorporateController;
use App\Http\Middleware\RequireAuth;
use App\Http\Middleware\RequireAdmin;

$router = new Router();

// Health
$router->get('health', fn() => json_response(['ok' => true]));

// Auth
$router->post('auth/register', [AuthController::class, 'register']);
$router->post('auth/login', [AuthController::class, 'login']);
$router->post('auth/logout', [AuthController::class, 'logout'], [RequireAuth::class]);
$router->get('auth/me', [AuthController::class, 'me']);
// Google OAuth
$router->get('auth/google/start', [AuthController::class, 'googleStart']);
$router->get('auth/google/callback', [AuthController::class, 'googleCallback']);

// Products & Orders
$router->get('products', [ProductController::class, 'list']);
$router->post('checkout', [ProductController::class, 'checkout'], [RequireAuth::class]);

// Gifts
$router->get('gifts', [GiftController::class, 'list'], [RequireAuth::class]);
$router->get('gifts/{id}', [GiftController::class, 'get'], [RequireAuth::class]);
$router->post('gifts/send', [GiftController::class, 'send'], [RequireAuth::class]);
$router->post('gifts/accept', [GiftController::class, 'accept'], [RequireAuth::class]);
$router->post('gifts/reject', [GiftController::class, 'reject'], [RequireAuth::class]);

// Corporate River
$router->get('corporate/river', [CorporateController::class, 'river']);
$router->post('corporate/draw', [CorporateController::class, 'draw'], [RequireAuth::class]);
$router->get('corporate/awards', [CorporateController::class, 'awards'], [RequireAuth::class]);
$router->post('corporate/reviews', [CorporateController::class, 'review'], [RequireAuth::class]);

// Admin
$router->get('admin/users', [AdminController::class, 'users'], [RequireAdmin::class]);
$router->post('admin/users', [AdminController::class, 'usersCreate'], [RequireAdmin::class]);
$router->patch('admin/users/{id}', [AdminController::class, 'usersUpdate'], [RequireAdmin::class]);
$router->delete('admin/users/{id}', [AdminController::class, 'usersDelete'], [RequireAdmin::class]);
$router->post('admin/users/{id}/toggle-admin', [AdminController::class, 'usersToggleAdmin'], [RequireAdmin::class]);

// Admin Gifts CRUD
$router->get('admin/gifts', [GiftController::class, 'adminList'], [RequireAdmin::class]);
$router->post('admin/gifts', [GiftController::class, 'adminCreate'], [RequireAdmin::class]);
$router->patch('admin/gifts/{id}', [GiftController::class, 'adminUpdate'], [RequireAdmin::class]);
$router->delete('admin/gifts/{id}', [GiftController::class, 'adminDelete'], [RequireAdmin::class]);

// Admin Corporate Gifts CRUD
$router->get('admin/corporate', [CorporateController::class, 'adminList'], [RequireAdmin::class]);
$router->post('admin/corporate', [CorporateController::class, 'adminCreate'], [RequireAdmin::class]);
$router->patch('admin/corporate/{id}', [CorporateController::class, 'adminUpdate'], [RequireAdmin::class]);
$router->delete('admin/corporate/{id}', [CorporateController::class, 'adminDelete'], [RequireAdmin::class]);

// Admin Media Upload (simple local storage)
$router->post('admin/upload', function () {
    if (!is_admin())
        json_response(['error' => 'Forbidden'], 403);
    if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        json_response(['error' => 'No file'], 400);
    }
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        json_response(['error' => 'Invalid file type'], 415);
    }
    // Save into api/uploads so api/index.php can serve them at /api/uploads/*
    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir))
        @mkdir($uploadsDir, 0775, true);
    $fname = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $uploadsDir . '/' . $fname;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        json_response(['error' => 'Upload failed'], 500);
    }
    $base = (function () {
        $cfg = require __DIR__ . '/config.php';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim((string) ($cfg['app']['base_path'] ?? ''), '/');
        return sprintf('%s://%s%s', $scheme, $host, $basePath);
    })();
    $url = $base . '/api/uploads/' . $fname;
    json_response(['ok' => true, 'url' => $url, 'filename' => $fname]);
}, [RequireAdmin::class]);

return $router;