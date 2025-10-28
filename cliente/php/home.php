<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';
header('Content-Type: application/json; charset=utf-8');

function respostaJSON($data, $code=200){
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if(!isset($_SESSION['loja_id'])){
    respostaJSON(['erro'=>'Loja não especificada'],401);
}

$loja_id = (int)$_SESSION['loja_id'];

$stmt = $pdo->prepare("
    SELECT p.id, p.nome, p.descricao, pl.preco_loja as preco, p.imagem_principalfotos_produto as imagem
    FROM produtos p
    INNER JOIN produtos_lojas pl ON p.id = pl.produto_id
    WHERE pl.loja_id=:loja_id AND p.ativo=1
");
$stmt->execute([':loja_id'=>$loja_id]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// converte imagem blob para base64
foreach($produtos as &$p){
    if($p['imagem']){
        $p['imagem'] = 'data:image/jpeg;base64,'.base64_encode($p['imagem']);
    }
}

respostaJSON($produtos);
