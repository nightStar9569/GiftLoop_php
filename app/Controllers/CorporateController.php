<?php
namespace App\Controllers;

use App\Services\TicketService;
use App\Services\LotteryService;

class CorporateController
{
    private TicketService $tickets;
    private LotteryService $lottery;

    public function __construct()
    {
        $this->tickets = new TicketService();
        $this->lottery = new LotteryService();
    }

    // GET /corporate/river
    public function river(): void
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT g.id, g.title, g.category, g.is_super, g.start_at, g.end_at, (SELECT url FROM corporate_gift_images i WHERE i.gift_id=g.id ORDER BY sort_order ASC, id ASC LIMIT 1) AS image_url FROM corporate_gifts g WHERE g.start_at <= NOW() AND g.end_at >= NOW() ORDER BY g.id DESC LIMIT 50');
        $items = $stmt->fetchAll();
        json_response(['items' => $items]);
    }

    // POST /corporate/draw
    public function draw(): void
    {
        $user = require_auth();
        $userId = (int) $user['id'];

        // Enforce 1 free per day; consume ticket if available and free already used
        $usedFree = !$this->tickets->hasDailyFreeAvailable($userId);
        if ($usedFree) {
            $ok = $this->tickets->consumeOne($userId);
            if (!$ok) {
                json_response(['error' => 'No tickets available'], 409);
            }
        }

        $giftId = $this->lottery->pickActiveGiftId();
        if ($giftId === null) {
            // Record attempt
            $this->lottery->recordAttempt($userId, null, 'lose');
            json_response(['ok' => true, 'result' => 'lose']);
        }

        // Naive 30% win rate baseline; adjust as needed
        $win = (random_int(1, 100) <= 30);
        if ($win) {
            $awardId = $this->lottery->awardGift($userId, $giftId);
            $this->lottery->recordAttempt($userId, $giftId, 'win');
            json_response(['ok' => true, 'result' => 'win', 'awardId' => $awardId, 'giftId' => $giftId]);
        }
        $this->lottery->recordAttempt($userId, $giftId, 'lose');
        json_response(['ok' => true, 'result' => 'lose']);
    }

    // GET /corporate/awards
    public function awards(): void
    {
        $user = require_auth();
        $pdo = db();
        $stmt = $pdo->prepare('SELECT a.id, a.gift_id, a.status, a.created_at, g.title, g.category FROM corporate_awards a JOIN corporate_gifts g ON g.id = a.gift_id WHERE a.user_id = ? ORDER BY a.id DESC LIMIT 200');
        $stmt->execute([(int) $user['id']]);
        json_response(['items' => $stmt->fetchAll()]);
    }

    // POST /corporate/reviews
    public function review(): void
    {
        $user = require_auth();
        $data = read_json_body();
        $giftId = int_or_zero($data['giftId'] ?? 0);
        $rating = int_or_zero($data['rating'] ?? 0);
        $comment = trim((string) ($data['comment'] ?? ''));
        if ($giftId <= 0 || $rating < 1 || $rating > 5)
            json_response(['error' => 'Invalid review'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO corporate_gift_reviews (user_id, gift_id, rating, comment) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)');
        $stmt->execute([(int) $user['id'], $giftId, $rating, $comment ?: null]);
        json_response(['ok' => true]);
    }

    // Admin CRUD for corporate gifts
    public function adminList(): void
    {
        if (!is_admin())
            json_response(['error' => 'Forbidden'], 403);
        $pdo = db();
        $stmt = $pdo->query('SELECT g.*, (SELECT url FROM corporate_gift_images i WHERE i.gift_id=g.id ORDER BY sort_order ASC, id ASC LIMIT 1) AS image_url FROM corporate_gifts g ORDER BY g.id DESC LIMIT 500');
        json_response(['items' => $stmt->fetchAll()]);
    }

    public function adminCreate(): void
    {
        if (!is_admin())
            json_response(['error' => 'Forbidden'], 403);
        $d = read_json_body();
        $title = trim((string) ($d['title'] ?? ''));
        $category = trim((string) ($d['category'] ?? 'other'));
        $isSuper = !empty($d['isSuper']) ? 1 : 0;
        $supplyPerDay = int_or_zero($d['supplyPerDay'] ?? 0);
        $totalSupply = isset($d['totalSupply']) ? int_or_zero($d['totalSupply']) : null;
        $startAt = (string) ($d['startAt'] ?? '');
        $endAt = (string) ($d['endAt'] ?? '');
        $imageUrl = isset($d['imageUrl']) ? trim((string) $d['imageUrl']) : null;
        if (!$title || !$startAt || !$endAt)
            json_response(['error' => 'Invalid input'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO corporate_gifts (title, description, category, is_super, supply_per_day, total_supply, start_at, end_at) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$title, $d['description'] ?? null, $category, $isSuper, $supplyPerDay, $totalSupply, $startAt, $endAt]);
        $id = (int) $pdo->lastInsertId();
        if ($imageUrl) {
            $pdo->prepare('INSERT INTO corporate_gift_images (gift_id, url, sort_order) VALUES (?,?,0)')->execute([$id, $imageUrl]);
        }
        json_response(['ok' => true, 'id' => $id]);
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
            'title' => 'title',
            'description' => 'description',
            'category' => 'category',
            'isSuper' => 'is_super',
            'supplyPerDay' => 'supply_per_day',
            'totalSupply' => 'total_supply',
            'startAt' => 'start_at',
            'endAt' => 'end_at'
        ] as $in => $col) {
            if (array_key_exists($in, $d)) {
                $val = $d[$in];
                if ($col === 'is_super')
                    $val = !empty($val) ? 1 : 0;
                if (in_array($col, ['supply_per_day', 'total_supply'], true))
                    $val = int_or_zero($val);
                $fields[] = "$col = ?";
                $values[] = $val;
            }
        }
        if (!$fields && empty($d['imageUrl']))
            json_response(['error' => 'No fields'], 422);
        if ($fields) {
            $values[] = $id;
            $sql = 'UPDATE corporate_gifts SET ' . implode(', ', $fields) . ' WHERE id = ?';
            db()->prepare($sql)->execute($values);
        }
        if (isset($d['imageUrl'])) {
            $url = trim((string) $d['imageUrl']);
            // Upsert first image as cover
            $pdo = db();
            $exists = $pdo->prepare('SELECT id FROM corporate_gift_images WHERE gift_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1');
            $exists->execute([$id]);
            $row = $exists->fetch();
            if ($row) {
                $pdo->prepare('UPDATE corporate_gift_images SET url = ? WHERE id = ?')->execute([$url ?: null, (int) $row['id']]);
            } else if ($url) {
                $pdo->prepare('INSERT INTO corporate_gift_images (gift_id, url, sort_order) VALUES (?,?,0)')->execute([$id, $url]);
            }
        }
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
        $pdo->prepare('DELETE FROM corporate_gifts WHERE id = ?')->execute([$id]);
        json_response(['ok' => true]);
    }
}