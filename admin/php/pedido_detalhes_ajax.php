<?php
session_start();
require '../includes/conexao.php';

if (!isset($_SESSION['admin_id'], $_GET['id'])) {
    echo "<div class='alert alert-danger'>Parâmetros inválidos.</div>";
    exit;
}

$admin_id = (int)$_SESSION['admin_id'];
$id       = (int)$_GET['id'];

// Buscar pedido do admin logado, juntando info do cliente
$stmt = $pdo->prepare("
    SELECT p.*, c.nome AS cliente_nome, c.telefone AS cliente_telefone
    FROM pedidos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.id = :id AND p.admin_id = :admin_id
    LIMIT 1
");
$stmt->execute([':id'=>$id, ':admin_id'=>$admin_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "<div class='alert alert-danger'>Pedido não encontrado ou não autorizado.</div>";
    exit;
}

// Função para evitar XSS
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Exibir detalhes do pedido
echo "<div class='card mt-2 p-3 bg-light'>";
echo "<p><strong>Cliente:</strong> " . h($pedido['cliente_nome'] ?? 'Não informado') . "</p>";
echo "<p><strong>Telefone:</strong> " . h($pedido['cliente_telefone'] ?? '-') . "</p>";
echo "<p><strong>Total:</strong> R$ " . number_format(($pedido['total'] + $pedido['taxa_entrega']), 2, ',', '.') . "</p>";
echo "<p><strong>Pagamento:</strong> " . h($pedido['metodo_pagamento'] ?? '-') . "</p>";
echo "<p><strong>Status:</strong> " . h($pedido['status']) . "</p>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($pedido['data_criacao'])) . "</p>";
echo "</div>";
