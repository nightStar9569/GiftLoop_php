<?php
namespace App\Controllers;

class AdminController
{
    public function users(): void
    {
        if (!is_admin()) json_response(['error' => 'Forbidden'], 403);
        $pdo = db();
        $stmt = $pdo->query('SELECT id, email, first_name, last_name, birth_date, avatar_url, gifts_sent, gifts_received, membership_level, is_admin, created_at FROM users ORDER BY id DESC LIMIT 500');
        json_response(['items' => $stmt->fetchAll()]);
    }

    public function usersCreate(): void
    {
        if (!is_admin()) json_response(['error' => 'Forbidden'], 403);
        $d = read_json_body();
        $email = strtolower(sanitize_email($d['email'] ?? ''));
        $password = (string)($d['password'] ?? '');
        $first = trim((string)($d['firstName'] ?? ''));
        $last = trim((string)($d['lastName'] ?? ''));
        $birth = !empty($d['birthDate']) ? $d['birthDate'] : null;
        if (!$email || !$password) json_response(['error' => 'Invalid input'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) json_response(['error' => 'Email already exists'], 409);
        $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, birth_date) VALUES (?,?,?,?,?)')->execute([$email, hash_password($password), $first ?: null, $last ?: null, $birth ?: null]);
        json_response(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    }

    public function usersUpdate(array $params): void
    {
        if (!is_admin()) json_response(['error' => 'Forbidden'], 403);
        $id = int_or_zero($params['id'] ?? 0);
        if ($id <= 0) json_response(['error' => 'Invalid id'], 422);
        $d = read_json_body();
        $first = isset($d['firstName']) ? trim((string)$d['firstName']) : null;
        $last = isset($d['lastName']) ? trim((string)$d['lastName']) : null;
        $level = isset($d['membershipLevel']) ? (string)$d['membershipLevel'] : null;
        $birth = array_key_exists('birthDate', $d) ? ($d['birthDate'] ?: null) : null; // allow nulling
        $pdo = db();
        $fields = [];
        $paramsSql = [];
        if ($first !== null) { $fields[] = 'first_name = ?'; $paramsSql[] = $first ?: null; }
        if ($last !== null) { $fields[] = 'last_name = ?'; $paramsSql[] = $last ?: null; }
        if ($level !== null) { $fields[] = 'membership_level = ?'; $paramsSql[] = in_array($level, ['basic','premium','business'], true) ? $level : 'basic'; }
        if (array_key_exists('birthDate', $d)) { $fields[] = 'birth_date = ?'; $paramsSql[] = $birth; }
        if (array_key_exists('avatarUrl', $d)) { $fields[] = 'avatar_url = ?'; $paramsSql[] = (trim((string)$d['avatarUrl']) ?: null); }
        if (!$fields) json_response(['error' => 'No fields'], 422);
        $paramsSql[] = $id;
        $sql = 'UPDATE users SET ' . implode(',', $fields) . ' WHERE id = ?';
        $pdo->prepare($sql)->execute($paramsSql);
        json_response(['ok' => true]);
    }

    public function usersDelete(array $params): void
    {
        if (!is_admin()) json_response(['error' => 'Forbidden'], 403);
        $id = int_or_zero($params['id'] ?? 0);
        if ($id <= 0) json_response(['error' => 'Invalid id'], 422);
        $pdo = db();
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        json_response(['ok' => true]);
    }

    public function usersToggleAdmin(array $params): void
    {
        if (!is_admin()) json_response(['error' => 'Forbidden'], 403);
        $id = int_or_zero($params['id'] ?? 0);
        if ($id <= 0) json_response(['error' => 'Invalid id'], 422);
        $pdo = db();
        $row = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
        $row->execute([$id]);
        $r = $row->fetch();
        if (!$r) json_response(['error' => 'Not found'], 404);
        $next = (int)!((int)$r['is_admin']);
        $pdo->prepare('UPDATE users SET is_admin = ? WHERE id = ?')->execute([$next, $id]);
        json_response(['ok' => true, 'is_admin' => $next]);
    }
} 