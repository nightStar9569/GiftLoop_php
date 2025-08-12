<?php
// Basic configuration for GiftLoop PHP backend
// Adjust for your local XAMPP if needed.

return [
    'db' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'giftloop',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    'session' => [
        'name' => 'GIFTSESSID',
        'cookie_lifetime' => 60 * 60 * 24 * 7, // 7 days
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    'app' => [
        'base_path' => '/GiftLoop', // Change if your project root differs
        'debug' => true,
    ],
    'oauth' => [
        'google' => [
            // TODO: fill these with your Google Cloud OAuth credentials
            'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        ],
        'redirect_after_login' => '/mypage.php',
    ],
]; 