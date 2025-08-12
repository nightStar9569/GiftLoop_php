<?php
namespace App\Controllers;

class AdminController
{
    public function users(): void
    {
        if (!is_admin()) json_response(['error' => 'Forbidden'], 403);
        $pdo = db();
        $stmt = $pdo->query('SELECT id, email, first_name, last_name, gifts_sent, gifts_received, membership_level, created_at FROM users ORDER BY id DESC LIMIT 500');
        json_response(['items' => $stmt->fetchAll()]);
    }
} 