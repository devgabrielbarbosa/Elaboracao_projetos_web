<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';
session_start();


// Retorna o loja_id da sessão
if (!isset($_SESSION['loja_id'])) {
    echo json_encode(['erro' => 'Sessão expirada']);
    exit;
}

echo json_encode(['loja_id' => (int) $_SESSION['loja_id']]);