<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';
header('Content-Type: application/json; charset=utf-8');

function respostaJSON($data,$code=200){
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if(!isset($_SESSION['cliente_id'],$_SESSION['loja_id'])){
    respostaJSON(['erro'=>'Cliente não logado'],401);
}

$cliente_id = (int)$_SESSION['cliente_id'];
$loja_id = (int)$_SESSION['loja_id'];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $itens = json_decode(file_get_contents('php://input'),true);
    if(!$itens) respostaJSON(['erro'=>'Carrinho vazio'],400);

    // Cria pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id,loja_id,status,data_criacao) VALUES (:cliente_id,:loja_id,'pendente',NOW())");
    $stmt->execute([':cliente_id'=>$cliente_id,':loja_id'=>$loja_id]);
    $pedido_id = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare("INSERT INTO itens_pedido (pedido_id,produto_id,quantidade,preco_unitario,loja_id) VALUES (:pedido_id,:produto_id,:quantidade,:preco_unitario,:loja_id)");
    foreach($itens as $i){
        $stmtItem->execute([
            ':pedido_id'=>$pedido_id,
            ':produto_id'=>$i['produto_id'],
            ':quantidade'=>$i['quantidade'],
            ':preco_unitario'=>$i['preco_unitario'],
            ':loja_id'=>$loja_id
        ]);
    }

    respostaJSON(['sucesso'=>true,'pedido_id'=>$pedido_id]);
}
