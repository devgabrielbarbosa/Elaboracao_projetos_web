<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';

$loja_id = intval($_GET['loja_id'] ?? 0);
if (!$loja_id) {
    echo json_encode(['erro' => 'Loja não especificada']);
    exit;
}

try {
    // Busca informações da loja
    $stmt = $pdo->prepare("SELECT nome_loja AS nome, CONCAT(endereco, ' - ', cidade, '/', estado) AS endereco, logo_loja AS logo FROM lojas WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $loja_id]);
    $loja = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$loja) {
        echo json_encode(['erro' => 'Loja não encontrada']);
        exit;
    }

    // Se a logo for longblob, converte para base64
    if ($loja['logo']) {
        $loja['logo'] = 'data:image/png;base64,' . base64_encode($loja['logo']);
    }

    echo json_encode($loja);
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao acessar o banco de dados']);
}
