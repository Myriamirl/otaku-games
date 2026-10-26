<?php
// Configuration sécurisée des cookies de session AVANT le session_start()=> renfo des cookies de session
if (session_status() === PHP_SESSION_NONE) {
    // Détection automatique : true si HTTPS, false si HTTP (localhost)
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    session_set_cookie_params([
        'lifetime' => 7200,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
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
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate Limiting basé sur l'IP en BDD
 */
function check_ip_rate_limit(PDO $pdo, string $action_key = 'login_attempt', int $max_attempts = 10, int $decay_seconds = 900): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $now = date('Y-m-d H:i:s');

    // Nettoyage automatique des enregistrements expirés
    $stmtClean = $pdo->prepare("DELETE FROM rate_limits WHERE TIMESTAMPDIFF(SECOND, first_attempt, NOW()) > ?");
    $stmtClean->execute([$decay_seconds]);

    // Récupération des tentatives pour cette IP
    $stmt = $pdo->prepare("SELECT id, attempts FROM rate_limits WHERE ip_address = ? AND action_key = ?");
    $stmt->execute([$ip, $action_key]);
    $record = $stmt->fetch();

    if ($record) {
        if ($record['attempts'] >= $max_attempts) {
            return false; // IP bloquée temporairement
        }
        $stmtUpdate = $pdo->prepare("UPDATE rate_limits SET attempts = attempts + 1 WHERE id = ?");
        $stmtUpdate->execute([$record['id']]);
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO rate_limits (ip_address, action_key, attempts, first_attempt) VALUES (?, ?, 1, ?)");
        $stmtInsert->execute([$ip, $action_key, $now]);
    }

    return true;
}