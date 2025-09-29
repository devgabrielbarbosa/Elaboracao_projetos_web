<?php
session_start();
require '../includes/conexao.php';

if (!isset($_SESSION['admin_id']) || !isset($_POST['id'], $_POST['acao'])) exit;

$admin_id = $_SESSION['admin_id'];
$id = (int)$_POST['id'];
$acao = $_POST['acao'];

// Verificar se o pedido pertence ao admin
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id=:id AND admin_id=:admin_id");
$stmt->execute([':id'=>$id, ':admin_id'=>$admin_id]);
$pedido = $stmt->fetch();
if(!$pedido) exit;

switch($acao){
    case 'aceitar':
        $novo_status = 'aceito';
        break;
    case 'cancelar':
        $novo_status = 'cancelado';
        break;
    case 'enviar':
        $novo_status = 'em_entrega';
        break;
    case 'finalizar':
        $novo_status = 'entregue';
        break;
    default:
        exit;
}

$stmt = $pdo->prepare("UPDATE pedidos SET status=:status WHERE id=:id AND admin_id=:admin_id");
$stmt->execute([':status'=>$novo_status, ':id'=>$id, ':admin_id'=>$admin_id]);

echo "ok";
