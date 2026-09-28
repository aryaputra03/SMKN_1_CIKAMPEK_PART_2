<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/database.php';

try {
    $statement = database()->query(
        'SELECT url_kurikulum FROM kurikulum_url WHERE id = 1 LIMIT 1'
    );
    $kurikulum = $statement->fetch();

    if (!$kurikulum) {
        http_response_code(404);
        echo json_encode(['error' => 'Link kurikulum tidak ditemukan.']);
        exit;
    }

    echo json_encode($kurikulum, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log('get_kurikulum.php: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Link kurikulum tidak dapat dimuat.']);
}
