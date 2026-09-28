<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$session = empty($_SESSION['admin_id']) ? null : ['user' => ['id' => $_SESSION['admin_id'], 'email' => $_SESSION['admin_email']]];
jsonResponse(['data' => ['session' => $session], 'error' => null]);
