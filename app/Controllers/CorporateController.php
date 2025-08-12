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
        $stmt = $pdo->query('SELECT id, title, category, is_super, start_at, end_at FROM corporate_gifts WHERE start_at <= NOW() AND end_at >= NOW() ORDER BY id DESC LIMIT 50');
        $items = $stmt->fetchAll();
        json_response(['items' => $items]);
    }

    // POST /corporate/draw
    public function draw(): void
    {
        $user = require_auth();
        $userId = (int)$user['id'];

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
        $stmt->execute([(int)$user['id']]);
        json_response(['items' => $stmt->fetchAll()]);
    }

    // POST /corporate/reviews
    public function review(): void
    {
        $user = require_auth();
        $data = read_json_body();
        $giftId = int_or_zero($data['giftId'] ?? 0);
        $rating = int_or_zero($data['rating'] ?? 0);
        $comment = trim((string)($data['comment'] ?? ''));
        if ($giftId <= 0 || $rating < 1 || $rating > 5) json_response(['error' => 'Invalid review'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO corporate_gift_reviews (user_id, gift_id, rating, comment) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)');
        $stmt->execute([(int)$user['id'], $giftId, $rating, $comment ?: null]);
        json_response(['ok' => true]);
    }
} 