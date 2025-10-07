<?php
session_start();
require '../includes/conexao.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) exit;

$admin_id = $_SESSION['admin_id'];
$id = (int)$_GET['id'];

// Buscar pedido do admin logado
$stmt = $pdo->prepare("
    SELECT p.*, c.nome AS cliente_nome, c.telefone AS cliente_telefone
    FROM pedidos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.id=:id AND p.admin_id=:admin_id
");
$stmt->execute([':id'=>$id, ':admin_id'=>$admin_id]);
$pedido = $stmt->fetch();

if(!$pedido){
    echo "<div class='alert alert-danger'>Pedido não encontrado.</div>";
    exit;
}

// Detalhes do pedido
echo "<div class='card mt-2 p-2 bg-light'>";
echo "<p><strong>Cliente:</strong> ".htmlspecialchars($pedido['cliente_nome'])."</p>";
echo "<p><strong>Telefone:</strong> ".htmlspecialchars($pedido['cliente_telefone'])."</p>";
echo "<p><strong>Total:</strong> R$ ".number_format($pedido['total'] + $pedido['taxa_entrega'],2,",",".")."</p>";
echo "<p><strong>Pagamento:</strong> ".htmlspecialchars($pedido['metodo_pagamento'])."</p>";
echo "<p><strong>Status:</strong> ".htmlspecialchars($pedido['status'])."</p>";
echo "<p><strong>Data:</strong> ".date('d/m/Y H:i', strtotime($pedido['data_criacao']))."</p>";
echo "</div>";
