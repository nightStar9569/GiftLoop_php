<?php
namespace App\Controllers;

class GiftController
{
    public function list(): void
    {
        require_auth();
        $pdo = db();
        $stmt = $pdo->query('SELECT * FROM gifts ORDER BY created_at DESC LIMIT 200');
        json_response(['items' => $stmt->fetchAll()]);
    }

    public function get(array $params): void
    {
        require_auth();
        $id = isset($params['id']) ? int_or_zero($params['id']) : 0;
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM gifts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row)
            json_response(['error' => 'Not found'], 404);
        json_response(['item' => $row]);
    }

    public function send(): void
    {
        $user = require_auth();
        $data = read_json_body();
        $name = trim($data['name'] ?? '');
        $price = int_or_zero($data['price'] ?? 0);
        $category = trim($data['category'] ?? 'other');
        $isSuper = !empty($data['isSuper']) ? 1 : 0;
        $recipientName = trim($data['recipientName'] ?? '');
        $recipientEmail = trim($data['recipientEmail'] ?? '');
        if (!$name || $price <= 0)
            json_response(['error' => 'Invalid gift'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO gifts (name, description, price, category, is_super, status, sender_id, owner_id, recipient_name, recipient_email, sent_at) VALUES (?,?,?,?,?,"sent",?,?,?,?,NOW())');
        $stmt->execute([$name, $data['description'] ?? null, $price, $category, $isSuper, $user['id'], $user['id'], $recipientName ?: null, $recipientEmail ?: null]);
        json_response(['ok' => true, 'giftId' => (int) db()->lastInsertId()]);
    }

    public function accept(): void
    {
        $user = require_auth();
        $data = read_json_body();
        $giftId = int_or_zero($data['giftId'] ?? 0);
        if ($giftId <= 0)
            json_response(['error' => 'Invalid gift'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE gifts SET status = "accepted", receiver_id = ?, owner_id = ?, received_at = NOW() WHERE id = ?');
        $stmt->execute([$user['id'], $user['id'], $giftId]);
        $pdo->prepare('UPDATE users SET gifts_received = gifts_received + 1 WHERE id = ?')->execute([$user['id']]);
        json_response(['ok' => true]);
    }

    public function reject(): void
    {
        require_auth();
        $data = read_json_body();
        $giftId = int_or_zero($data['giftId'] ?? 0);
        if ($giftId <= 0)
            json_response(['error' => 'Invalid gift'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE gifts SET status = "rejected" WHERE id = ?');
        $stmt->execute([$giftId]);
        json_response(['ok' => true]);
    }

    public function adminDelete(array $params): void
    {
        if (!is_admin())
            json_response(['error' => 'Forbidden'], 403);
        $id = int_or_zero($params['id'] ?? 0);
        if ($id <= 0)
            json_response(['error' => 'Invalid id'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('DELETE FROM gifts WHERE id = ?');
        $stmt->execute([$id]);
        json_response(['ok' => true]);
    }

    public function adminToggle(): void
    {
        if (!is_admin())
            json_response(['error' => 'Forbidden'], 403);
        $data = read_json_body();
        $giftId = int_or_zero($data['giftId'] ?? 0);
        if ($giftId <= 0)
            json_response(['error' => 'Invalid gift'], 422);
        $pdo = db();
        // Cycle through statuses
        $stmt = $pdo->prepare('SELECT status FROM gifts WHERE id = ?');
        $stmt->execute([$giftId]);
        $row = $stmt->fetch();
        if (!$row)
            json_response(['error' => 'Not found'], 404);
        $order = ['created', 'sent', 'pending', 'accepted', 'rejected', 'owned'];
        $current = $row['status'];
        $idx = array_search($current, $order, true);
        $next = $order[(($idx === false ? 0 : $idx) + 1) % count($order)];
        $pdo->prepare('UPDATE gifts SET status = ? WHERE id = ?')->execute([$next, $giftId]);
        json_response(['ok' => true, 'status' => $next]);
    }

    // Admin CRUD for general gifts
    public function adminList(): void
    {
        if (!is_admin())
            json_response(['error' => 'Forbidden'], 403);
        $pdo = db();
        $stmt = $pdo->query('SELECT * FROM gifts ORDER BY id DESC LIMIT 500');
        json_response(['items' => $stmt->fetchAll()]);
    }

    public function adminCreate(): void
    {
        if (!is_admin())
            json_response(['error' => 'Forbidden'], 403);
        $d = read_json_body();
        $name = trim((string) ($d['name'] ?? ''));
        $price = int_or_zero($d['price'] ?? 0);
        $category = trim((string) ($d['category'] ?? 'other'));
        $desc = isset($d['description']) ? (string) $d['description'] : null;
        $isSuper = !empty($d['isSuper']) ? 1 : 0;
        $imageUrl = isset($d['imageUrl']) ? trim((string) $d['imageUrl']) : null;
        if (!$name || $price <= 0)
            json_response(['error' => 'Invalid input'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO gifts (name, description, price, category, is_super, status, image_url) VALUES (?,?,?,?,?,"created",?)');
        $stmt->execute([$name, $desc, $price, $category, $isSuper, $imageUrl ?: null]);
        json_response(['ok' => true, 'id' => (int) $pdo->lastInsertId()]);
    }

    public function adminUpdate(array $params): void
    {
        if (!is_admin())
            json_response(['error' => 'Forbidden'], 403);
        $id = int_or_zero($params['id'] ?? 0);
        if ($id <= 0)
            json_response(['error' => 'Invalid id'], 422);
        $d = read_json_body();
        $fields = [];
        $values = [];
        foreach ([
            'name' => 'name',
            'description' => 'description',
            'price' => 'price',
            'category' => 'category',
            'isSuper' => 'is_super',
            'status' => 'status',
            'imageUrl' => 'image_url'
        ] as $in => $col) {
            if (array_key_exists($in, $d)) {
                $val = $d[$in];
                if ($col === 'is_super')
                    $val = !empty($val) ? 1 : 0;
                if ($col === 'price')
                    $val = int_or_zero($val);
                $fields[] = "$col = ?";
                $values[] = ($col === 'image_url') ? (trim((string) $val) ?: null) : $val;
            }
        }
        if (!$fields)
            json_response(['error' => 'No fields'], 422);
        $values[] = $id;
        $sql = 'UPDATE gifts SET ' . implode(', ', $fields) . ' WHERE id = ?';
        db()->prepare($sql)->execute($values);
        json_response(['ok' => true]);
    }
}