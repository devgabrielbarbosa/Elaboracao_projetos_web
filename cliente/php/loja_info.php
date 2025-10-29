<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';

try {
    // Pega o slug da URL
    $slug = $_GET['loja'] ?? null;
    if (!$slug) {
        echo json_encode(['erro' => 'Slug da loja não informado']);
        exit;
    }

    // Busca informações da loja pelo slug
    $stmt = $pdo->prepare("SELECT id, nome, endereco, logo FROM lojas WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    $loja = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$loja) {
        echo json_encode(['erro' => 'Loja não encontrada']);
        exit;
    }

    // Se a logo for longblob, converte para base64
    if ($loja['logo']) {
        $loja['logo'] = base64_encode($loja['logo']);
    }

    // Retorna JSON compatível com o JS
    echo json_encode([
        'nome' => $loja['nome'],
        'endereco' => $loja['endereco'] ?? '',
        'logo' => $loja['logo'] ?? null
    ]);

} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao acessar o banco de dados']);
}
