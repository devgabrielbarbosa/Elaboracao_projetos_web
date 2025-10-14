<?php
require __DIR__ . '/../../includes/conexao.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("HTTP/1.0 400 Bad Request");
    echo "ID inválido";
    exit;
}

// Buscar imagem do produto
$stmt = $pdo->prepare("SELECT imagem, tipo_imagem FROM produtos WHERE id = :id");
$stmt->execute([':id' => $id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($produto && $produto['imagem']) {
    // Definir o tipo correto de imagem se estiver armazenado no banco, senão default para jpeg
    $tipo = $produto['tipo_imagem'] ?? 'image/jpeg';
    header("Content-Type: $tipo");
    echo $produto['imagem'];
    exit;
}

// Caso não exista imagem
header("HTTP/1.0 404 Not Found");
echo "Imagem não encontrada";
exit;
