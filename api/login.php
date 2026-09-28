<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

try {
    $body = requestBody();
    $email = trim((string) ($body['email'] ?? ''));
    $password = (string) ($body['password'] ?? '');
    if ($email === '' || $password === '') jsonResponse(['error' => 'Email dan password wajib diisi.'], 422);

    $statement = database()->prepare('SELECT id, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        jsonResponse(['error' => 'Email atau password tidak valid.'], 401);
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_role'] = $user['role'] ?? 'admin';
    jsonResponse(['data' => ['session' => ['user' => ['id' => $user['id'], 'email' => $user['email']]]], 'error' => null]);
} catch (Throwable $error) {
    error_log('login.php: ' . $error->getMessage());
    jsonResponse(['error' => 'Login tidak dapat diproses.'], 500);
}
