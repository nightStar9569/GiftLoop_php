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
$router->post('gifts/delete', [GiftController::class, 'adminDelete'], [RequireAdmin::class]);
$router->post('gifts/toggle', [GiftController::class, 'adminToggle'], [RequireAdmin::class]);

return $router; 