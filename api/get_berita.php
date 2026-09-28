<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/database.php';

try {
    $pdo = database();
    $id = trim((string) ($_GET['id'] ?? ''));

    if ($id !== '') {
        $statement = $pdo->prepare(
            'SELECT id, judul, slug, isi, foto_url, kategori, tanggal
             FROM berita WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $berita = $statement->fetch();

        if (!$berita) {
            http_response_code(404);
            echo json_encode(['error' => 'Berita tidak ditemukan.']);
            exit;
        }

        echo json_encode($berita, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $statement = $pdo->query(
        'SELECT id, judul, slug, isi, foto_url, kategori, tanggal
         FROM berita ORDER BY tanggal DESC, id DESC'
    );
    echo json_encode($statement->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log('get_berita.php: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Data berita tidak dapat dimuat.']);
}
