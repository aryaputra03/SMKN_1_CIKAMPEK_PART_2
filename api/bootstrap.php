<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('smkn1_admin');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
    csrfToken();
}

const API_TABLES = [
    'banner', 'berita', 'guru', 'kurikulum', 'kurikulum_url', 'laporan_keuangan',
    'lulusan_terbaik', 'prestasi', 'struktur_organisasi', 'video_profil', 'visi_misi',
];
const PUBLIC_TABLES = [
    'banner', 'berita', 'guru', 'kurikulum', 'kurikulum_url', 'lulusan_terbaik',
    'prestasi', 'struktur_organisasi', 'video_profil', 'visi_misi',
];

function jsonResponse(array $body, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requestBody(): array
{
    $body = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($body)) jsonResponse(['error' => 'JSON tidak valid.'], 400);
    return $body;
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_id'])) jsonResponse(['error' => 'Sesi admin diperlukan.'], 401);
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function requireCsrf(): void
{
    $token = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        jsonResponse(['error' => 'Token keamanan tidak valid, silakan muat ulang halaman.'], 403);
    }
}

function tableName(string $table): string
{
    if (!in_array($table, API_TABLES, true)) jsonResponse(['error' => 'Tabel tidak diizinkan.'], 400);
    return $table;
}

function tableColumns(PDO $pdo, string $table): array
{
    $rows = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();
    return array_column($rows, 'Field');
}

function safeColumns(PDO $pdo, string $table, string $requested): string
{
    $allowed = tableColumns($pdo, $table);
    if ($requested === '*' || trim($requested) === '') return '*';
    $columns = array_filter(array_map('trim', explode(',', $requested)));
    if (!$columns || array_diff($columns, $allowed)) jsonResponse(['error' => 'Kolom tidak diizinkan.'], 400);
    return implode(', ', array_map(static fn($column) => "`{$column}`", $columns));
}

function filtersSql(PDO $pdo, string $table, array $filters, array &$params): string
{
    $allowed = tableColumns($pdo, $table);
    $clauses = [];
    foreach ($filters as $index => $filter) {
        if (!is_array($filter) || !in_array($filter['operator'] ?? '', ['eq', 'neq'], true)) {
            jsonResponse(['error' => 'Filter tidak valid.'], 400);
        }
        $column = (string) ($filter['column'] ?? '');
        if (!in_array($column, $allowed, true)) jsonResponse(['error' => 'Kolom filter tidak diizinkan.'], 400);
        $key = ':filter' . $index;
        $clauses[] = "`{$column}` " . ($filter['operator'] === 'eq' ? '=' : '!=') . " {$key}";
        $params[$key] = $filter['value'] ?? null;
    }
    return $clauses ? ' WHERE ' . implode(' AND ', $clauses) : '';
}
