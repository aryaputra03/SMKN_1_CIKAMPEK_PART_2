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

// Beberapa tabel hasil migrasi dari Supabase memakai id UUID manual
// (bukan AUTO_INCREMENT). Fungsi ini mendeteksi itu supaya data.php
// bisa membuatkan UUID sendiri saat insert tidak menyertakan id.
function primaryKeyNeedsManualId(PDO $pdo, string $table): bool
{
    $rows = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();
    foreach ($rows as $row) {
        if ($row['Field'] === 'id') {
            return stripos((string) $row['Extra'], 'auto_increment') === false;
        }
    }
    return false;
}

function uuidV4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    $hex = bin2hex($data);
    return implode('-', [
        substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4),
        substr($hex, 16, 4), substr($hex, 20, 12),
    ]);
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
