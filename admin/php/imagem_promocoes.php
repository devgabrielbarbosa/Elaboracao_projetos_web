<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("HTTP/1.0 400 Bad Request");
    echo "ID inválido";
    exit;
}

// Buscar imagem da promoção
$stmt = $pdo->prepare("SELECT imagem, tipo_imagem FROM promocoes WHERE id = :id");
$stmt->execute([':id' => $id]);
$promo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($promo && $promo['imagem']) {
    $tipo = $promo['tipo_imagem'] ?? 'image/png'; // default png
    header("Content-Type: $tipo");
    echo $promo['imagem'];
    exit;
}

header("HTTP/1.0 404 Not Found");
echo "Imagem não encontrada";
exit;
