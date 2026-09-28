<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCKOUT_MINUTES = 15;

try {
    $body = requestBody();
    $email = trim((string) ($body['email'] ?? ''));
    $password = (string) ($body['password'] ?? '');
    if ($email === '' || $password === '') jsonResponse(['error' => 'Email dan password wajib diisi.'], 422);

    $pdo = database();
    $identifier = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '|' . strtolower($email);

    $since = (new DateTimeImmutable("-" . LOGIN_LOCKOUT_MINUTES . " minutes"))->format('Y-m-d H:i:s');
    $countStatement = $pdo->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE identifier = :identifier AND attempted_at > :since'
    );
    $countStatement->execute(['identifier' => $identifier, 'since' => $since]);
    if ((int) $countStatement->fetchColumn() >= LOGIN_MAX_ATTEMPTS) {
        jsonResponse(['error' => 'Terlalu banyak percobaan login. Coba lagi dalam beberapa menit.'], 429);
    }

    $statement = $pdo->prepare('SELECT id, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        $insertAttempt = $pdo->prepare('INSERT INTO login_attempts (identifier) VALUES (:identifier)');
        $insertAttempt->execute(['identifier' => $identifier]);
        jsonResponse(['error' => 'Email atau password tidak valid.'], 401);
    }

    $pdo->prepare('DELETE FROM login_attempts WHERE identifier = :identifier')->execute(['identifier' => $identifier]);

    session_regenerate_id(true);
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_role'] = $user['role'] ?? 'admin';
    jsonResponse(['data' => ['session' => ['user' => ['id' => $user['id'], 'email' => $user['email']]], 'csrfToken' => csrfToken()], 'error' => null]);
} catch (Throwable $error) {
    error_log('login.php: ' . $error->getMessage());
    jsonResponse(['error' => 'Login tidak dapat diproses.'], 500);
}
