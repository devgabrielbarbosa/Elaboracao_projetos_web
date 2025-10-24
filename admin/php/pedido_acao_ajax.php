<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

function respostaJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// ===== Sessão =====
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    respostaJSON(['erro' => 'Admin ou loja não logado.'], 401);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];

// Verifica se o pedido pertence ao admin
$stmt = $pdo->prepare("SELECT id FROM pedidos WHERE id = :id AND admin_id = :admin_id LIMIT 1");
$stmt->execute([':id' => $id, ':admin_id' => $admin_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    http_response_code(403);
    exit('Pedido não encontrado ou não autorizado.');
}

// Define o novo status
$map_status = [
    'aceitar'   => 'aceito',
    'cancelar'  => 'cancelado',
    'enviar'    => 'em_entrega',
    'finalizar' => 'entregue'
];

if (!array_key_exists($acao, $map_status)) {
    http_response_code(400);
    exit('Ação inválida.');
}

$novo_status = $map_status[$acao];

// Atualiza o status no banco
$stmt = $pdo->prepare("UPDATE pedidos SET status = :status WHERE id = :id AND admin_id = :admin_id");
$stmt->execute([
    ':status'    => $novo_status,
    ':id'        => $id,
    ':admin_id'  => $admin_id
]);

echo json_encode(['sucesso' => true, 'status' => $novo_status]);
