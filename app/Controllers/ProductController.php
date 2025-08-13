<?php
namespace App\Controllers;

class ProductController
{
    public function list(): void
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT id, name, description, price, category, icon FROM products ORDER BY id DESC');
        json_response(['items' => $stmt->fetchAll()]);
    }

    public function checkout(): void
    {
        $user = require_auth();
        $data = read_json_body();
        $items = $data['items'] ?? [];
        if (!is_array($items) || empty($items))
            json_response(['error' => 'Cart empty'], 422);
        $total = 0;
        foreach ($items as $it) {
            $qty = int_or_zero($it['quantity'] ?? 0);
            $price = int_or_zero($it['price'] ?? 0);
            if ($qty <= 0 || $price <= 0)
                continue;
            $total += $qty * $price;
        }
        if ($total <= 0)
            json_response(['error' => 'Invalid cart'], 422);
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, "paid")')->execute([$user['id'], $total]);
            $orderId = (int) $pdo->lastInsertId();
            $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)');
            foreach ($items as $it) {
                $ins->execute([$orderId, int_or_zero($it['id'] ?? 0), int_or_zero($it['quantity'] ?? 1), int_or_zero($it['price'] ?? 0)]);
            }
            $pdo->commit();
            json_response(['ok' => true, 'orderId' => $orderId, 'total' => $total]);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            json_response(['error' => 'Checkout failed'], 500);
        }
    }
}