<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    echo json_encode(['logado' => false]);
    exit;
}

echo json_encode([
    'logado' => true,
    'admin_id' => $_SESSION['admin_id'],
    'admin_nome' => $_SESSION['admin_nome'],
    'loja_id' => $_SESSION['loja_id']
]);
?>