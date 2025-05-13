<?php
session_start();
header('Content-Type: application/json');

$is_admin = isset($_SESSION['admin_id']) && isset($_SESSION['admin_name']);

echo json_encode([
    'is_admin' => $is_admin,
    'name' => $is_admin ? $_SESSION['admin_name'] : null
]);
?> 