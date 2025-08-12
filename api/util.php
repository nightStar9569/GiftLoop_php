<?php

function read_json_body(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function sanitize_email(string $email): string {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL) ?: '';
}

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function int_or_zero($value): int {
    return is_numeric($value) ? (int) $value : 0;
} 