<?php
header('Content-Type: application/json; charset=utf-8');

// Caminho correto para a conexão
require_once __DIR__ . '/../../includes/conexao.php';

$slug = $_GET['loja'] ?? '';
if (empty($slug)) {
    echo json_encode(['erro' => 'Parâmetro "loja" não informado.']);
    exit;
}

// Busca a loja pelo slug (não mais pelo ID)
$stmt = $pdo->prepare("SELECT * FROM lojas WHERE slug = ?");
$stmt->execute([$slug]);
$loja = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$loja) {
    echo json_encode(['erro' => 'Loja não encontrada.']);
    exit;
}

// Retorna os dados da loja em JSON
echo json_encode([
    'sucesso' => true,
    'loja' => [
        'id' => $loja['id'],
        'nome' => $loja['nome'],
        'slug' => $loja['slug'],
        'logo' => !empty($loja['logo']) ? base64_encode($loja['logo']) : null,
        'endereco' => $loja['endereco'],
        'cidade' => $loja['cidade'],
        'estado' => $loja['estado'],
        'taxa_entrega_padrao' => $loja['taxa_entrega_padrao']
    ]
]);
