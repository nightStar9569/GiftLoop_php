<?php
namespace App\Services;

class TicketService
{
    public function getTicketCount(int $userId): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity),0) AS qty FROM tickets WHERE user_id = ? AND type = "corporate_draw"');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return (int)($row['qty'] ?? 0);
    }

    public function grant(int $userId, int $quantity, string $reason): void
    {
        if ($quantity === 0) return;
        $pdo = db();
        $pdo->prepare('INSERT INTO tickets (user_id, type, quantity, reason) VALUES (?,?,?,?)')
            ->execute([$userId, 'corporate_draw', $quantity, $reason]);
    }

    public function consumeOne(int $userId): bool
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $available = $this->getTicketCount($userId);
            if ($available <= 0) { $pdo->rollBack(); return false; }
            $pdo->prepare('INSERT INTO tickets (user_id, type, quantity, reason) VALUES (?,?,?,?)')
                ->execute([$userId, 'corporate_draw', -1, 'consume_draw']);
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function hasDailyFreeAvailable(int $userId): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT COUNT(1) AS c FROM corporate_draw_attempts WHERE user_id = ? AND attempt_date = CURDATE()');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return ((int)($row['c'] ?? 0)) === 0;
    }
} 