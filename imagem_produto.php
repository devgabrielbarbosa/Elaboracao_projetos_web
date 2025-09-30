<?php
// imagem_produto_cliente.php
require 'includes/conexao.php'; // Conexão PDO

// Verifica se foi passado o id do produto
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    http_response_code(400);
    exit('ID do produto inválido');
}

$produto_id = (int)$_GET['id'];

// Busca a imagem na tabela fotos_produto
$stmt = $pdo->prepare("SELECT url FROM fotos_produto WHERE produto_id=:id LIMIT 1");
$stmt->execute([':id' => $produto_id]);
$img = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$img || empty($img['url'])){
    // Se não existir imagem, retorna placeholder
    header("Content-Type: image/png");
    readfile('img/product-placeholder.png');
    exit;
}

// Aqui consideramos que o campo `url` é do tipo BLOB
$data = $img['url'];

// Força o navegador a interpretar como imagem (jpeg ou png, ajuste se necessário)
$mime = 'image/jpeg'; // ou 'image/png' se todas forem PNG
header("Content-Type: $mime");
header('Content-Length: ' . strlen($data));

echo $data;
exit;
