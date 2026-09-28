<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

try {
    $body = requestBody();
    $pdo = database();
    $table = tableName((string) ($body['table'] ?? ''));
    $action = (string) ($body['action'] ?? 'select');
    $isWrite = in_array($action, ['insert', 'update', 'delete'], true);
    if ($isWrite) requireAdmin();
    if (!$isWrite && !in_array($table, PUBLIC_TABLES, true)) requireAdmin();

    $params = [];
    $where = filtersSql($pdo, $table, $body['filters'] ?? [], $params);

    if ($action === 'select') {
        $columns = safeColumns($pdo, $table, (string) ($body['columns'] ?? '*'));
        $order = '';
        if (isset($body['order'])) {
            $column = (string) ($body['order']['column'] ?? '');
            if (!in_array($column, tableColumns($pdo, $table), true)) jsonResponse(['error' => 'Kolom urut tidak diizinkan.'], 400);
            $order = " ORDER BY `{$column}` " . (($body['order']['ascending'] ?? true) ? 'ASC' : 'DESC');
        }
        $limit = '';
        if (isset($body['limit'])) {
            $limit = ' LIMIT ' . max(1, min(1000, (int) $body['limit']));
        }
        $statement = $pdo->prepare("SELECT {$columns} FROM `{$table}`{$where}{$order}{$limit}");
        $statement->execute($params);
        $rows = $statement->fetchAll();
        if (!empty($body['single'])) {
            if (!$rows && empty($body['maybeSingle'])) jsonResponse(['data' => null, 'error' => ['message' => 'Data tidak ditemukan.']], 404);
            jsonResponse(['data' => $rows[0] ?? null, 'error' => null]);
        }
        jsonResponse(['data' => $rows, 'count' => !empty($body['count']) ? count($rows) : null, 'error' => null]);
    }

    $allowed = tableColumns($pdo, $table);
    if ($action === 'insert') {
        $rows = $body['values'] ?? [];
        if (!is_array($rows) || !$rows) jsonResponse(['error' => 'Data baru wajib diisi.'], 400);
        foreach ($rows as $row) {
            if (!is_array($row) || array_diff(array_keys($row), $allowed)) jsonResponse(['error' => 'Kolom data tidak diizinkan.'], 400);
            $columns = array_keys($row);
            $sql = 'INSERT INTO `' . $table . '` (' . implode(',', array_map(static fn($c) => "`{$c}`", $columns)) . ') VALUES (' . implode(',', array_map(static fn($c) => ':' . $c, $columns)) . ')';
            $statement = $pdo->prepare($sql);
            $statement->execute($row);
        }
        jsonResponse(['data' => null, 'error' => null]);
    }

    if (!$where) jsonResponse(['error' => 'Filter wajib untuk perubahan data.'], 400);
    if ($action === 'update') {
        $values = $body['values'] ?? [];
        if (!is_array($values) || !$values || array_diff(array_keys($values), $allowed)) jsonResponse(['error' => 'Data perubahan tidak valid.'], 400);
        $set = [];
        foreach ($values as $column => $value) {
            $key = ':set_' . $column;
            $set[] = "`{$column}` = {$key}";
            $params[$key] = $value;
        }
        $statement = $pdo->prepare("UPDATE `{$table}` SET " . implode(', ', $set) . $where);
        $statement->execute($params);
        jsonResponse(['data' => null, 'error' => null]);
    }

    if ($action === 'delete') {
        $statement = $pdo->prepare("DELETE FROM `{$table}`{$where}");
        $statement->execute($params);
        jsonResponse(['data' => null, 'error' => null]);
    }

    jsonResponse(['error' => 'Aksi tidak dikenal.'], 400);
} catch (Throwable $error) {
    error_log('data.php: ' . $error->getMessage());
    jsonResponse(['error' => 'Server tidak dapat memproses data.'], 500);
}
