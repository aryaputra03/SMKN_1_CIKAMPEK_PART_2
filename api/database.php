<?php
declare(strict_types=1);

/**
 * Koneksi PDO untuk API publik.
 *
 * Atur DB_HOST, DB_NAME, DB_USER, dan DB_PASS pada konfigurasi PHP/cPanel.
 * Jangan menaruh password database di JavaScript atau mengunggahnya ke Git.
 */
function database(): PDO
{
    $localConfig = is_file(__DIR__ . '/config.local.php')
        ? require __DIR__ . '/config.local.php'
        : [];

    $host = $localConfig['host'] ?? getenv('DB_HOST');
    $name = $localConfig['name'] ?? getenv('DB_NAME');
    $user = $localConfig['user'] ?? getenv('DB_USER');
    $pass = $localConfig['pass'] ?? getenv('DB_PASS');

    if (!$host || !$name || !$user) {
        throw new RuntimeException('Konfigurasi database server belum lengkap.');
    }

    return new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $user,
        $pass ?: '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}
