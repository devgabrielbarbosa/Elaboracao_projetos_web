<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

header('Content-Type: application/json; charset=utf-8');

function respostaJSON($data, $code=200){
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if(!isset($_SESSION['cliente_id'], $_SESSION['loja_id'])){
    respostaJSON(['erro'=>'Cliente não logado'],401);
}

$cliente_id = (int)$_SESSION['cliente_id'];
$loja_id = (int)$_SESSION['loja_id'];

$stmt = $pdo->prepare("SELECT id,nome,email,foto_perfil FROM clientes WHERE id=:id AND loja_id=:loja_id");
$stmt->execute([':id'=>$cliente_id,':loja_id'=>$loja_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$cliente){
    respostaJSON(['erro'=>'Cliente não encontrado'],404);
}

// Converte blob para base64
if($cliente['foto_perfil']){
    $cliente['foto_perfil'] = 'data:image/jpeg;base64,'.base64_encode($cliente['foto_perfil']);
}

respostaJSON($cliente);
