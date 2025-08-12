<?php
namespace App\Services;

class LotteryService
{
    /**
     * Select an active corporate gift id or return null if none.
     * Strategy: choose random from currently active gifts (equal weight).
     * Later: weight by remaining supply per day.
     */
    public function pickActiveGiftId(): ?int
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT id FROM corporate_gifts WHERE start_at <= NOW() AND end_at >= NOW() ORDER BY id DESC LIMIT 200');
        $ids = array_map(fn($r) => (int)$r['id'], $stmt->fetchAll());
        if (empty($ids)) return null;
        $idx = random_int(0, count($ids) - 1);
        return $ids[$idx];
    }

    public function recordAttempt(int $userId, ?int $giftId, string $result): void
    {
        $pdo = db();
        $pdo->prepare('INSERT INTO corporate_draw_attempts (user_id, gift_id, result, attempt_date) VALUES (?,?,?, CURDATE())')
            ->execute([$userId, $giftId, $result]);
    }

    public function awardGift(int $userId, int $giftId): int
    {
        $pdo = db();
        $pdo->prepare('INSERT INTO corporate_awards (user_id, gift_id, status) VALUES (?,?,"pending")')->execute([$userId, $giftId]);
        return (int)$pdo->lastInsertId();
    }
} 