<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
requireAdmin();

const UPLOAD_BUCKETS = ['banner-images', 'berita-images', 'guru-images', 'prestasi-images', 'lulusan-images', 'struktur-images', 'kurikulum-files'];
const UPLOAD_MAX_BYTES = 10485760;

try {
    $bucket = (string) ($_POST['bucket'] ?? '');
    if (!in_array($bucket, UPLOAD_BUCKETS, true)) jsonResponse(['error' => 'Lokasi unggahan tidak diizinkan.'], 400);
    if (($_POST['action'] ?? '') === 'delete') {
        $paths = $_POST['paths'] ?? [];
        if (!is_array($paths)) jsonResponse(['error' => 'Daftar berkas tidak valid.'], 400);
        $directory = dirname(__DIR__) . '/uploads/' . $bucket;
        foreach ($paths as $path) {
            $file = $directory . '/' . basename((string) $path);
            if (is_file($file)) unlink($file);
        }
        jsonResponse(['data' => null, 'error' => null]);
    }
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) jsonResponse(['error' => 'Berkas gagal diterima.'], 400);
    $file = $_FILES['file'];
    if ($file['size'] > UPLOAD_MAX_BYTES) jsonResponse(['error' => 'Ukuran berkas maksimal 3 MB.'], 422);

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
    if (!isset($extensions[$mime])) jsonResponse(['error' => 'Format berkas tidak diizinkan.'], 422);

    $directory = dirname(__DIR__) . '/uploads/' . $bucket;
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new RuntimeException('Folder unggahan tidak dapat dibuat.');
    $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $name)) throw new RuntimeException('Berkas tidak dapat disimpan.');

    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $url = ($base === '' ? '' : $base) . '/../uploads/' . rawurlencode($bucket) . '/' . rawurlencode($name);
    jsonResponse(['data' => ['path' => $name, 'publicUrl' => $url], 'error' => null]);
} catch (Throwable $error) {
    error_log('upload.php: ' . $error->getMessage());
    jsonResponse(['error' => 'Unggahan tidak dapat diproses.'], 500);
}
