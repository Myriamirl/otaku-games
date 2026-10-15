<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Génère un Token CSRF unique par session
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie l'authenticité du Token CSRF
 */
function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate Limiting : Limitation des tentatives (Protection anti-force brute)
 */
function check_rate_limit(string $key, int $max_attempts = 5, int $decay_seconds = 900): bool {
    $now = time();
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = ['attempts' => 0, 'first_attempt' => $now];
    }

    $limit = &$_SESSION['rate_limits'][$key];

    if ($now - $limit['first_attempt'] > $decay_seconds) {
        $limit['attempts'] = 0;
        $limit['first_attempt'] = $now;
    }

    $limit['attempts']++;

    return $limit['attempts'] <= $max_attempts;
}