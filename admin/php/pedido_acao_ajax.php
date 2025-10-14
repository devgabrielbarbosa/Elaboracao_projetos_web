<?php
session_start();
require '../includes/conexao.php';

if (!isset($_SESSION['admin_id'], $_POST['id'], $_POST['acao'])) {
    http_response_code(400);
    exit('Parâmetros inválidos.');
}

$admin_id = (int)$_SESSION['admin_id'];
$id       = (int)$_POST['id'];
$acao     = $_POST['acao'];

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
