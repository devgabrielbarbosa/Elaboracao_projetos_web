<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';
session_start();

function resposta($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Sessão
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    http_response_code(401);
    resposta(['erro' => 'Sessão expirada. Faça login novamente.']);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];
$acao     = $_REQUEST['acao'] ?? null;

try {
    // ---------- LISTAR ----------
    if (!$acao || $acao === 'listar') {
        $stmt = $pdo->prepare("
            SELECT id, nome_categoria AS nome, 1 AS ativo
            FROM categorias_produtos_lojas
            WHERE loja_id = :loja_id 
            ORDER BY nome_categoria ASC
        ");
        $stmt->execute([':loja_id' => $loja_id]);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        resposta(['categorias' => $categorias]);
    }

    // ---------- ADICIONAR ----------
    if ($acao === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') resposta(['erro' => 'Nome da categoria obrigatório.']);

        // Evitar duplicidade
        $chk = $pdo->prepare("SELECT COUNT(*) FROM categorias_produtos_lojas WHERE nome_categoria = :nome AND loja_id = :loja_id");
        $chk->execute([':nome'=>$nome, ':loja_id'=>$loja_id]);
        if ($chk->fetchColumn() > 0) resposta(['erro' => 'Já existe categoria com esse nome nesta loja.']);

        $stmt = $pdo->prepare("INSERT INTO categorias_produtos_lojas (nome_categoria, loja_id) VALUES (:nome, :loja_id)");
        $stmt->execute([':nome'=>$nome, ':loja_id'=>$loja_id]);

        resposta(['sucesso' => 'Categoria adicionada com sucesso.', 'id' => $pdo->lastInsertId()]);
    }

    // ---------- DELETAR ----------
    if ($acao === 'deletar' && isset($_REQUEST['id'])) {
        $id = (int) $_REQUEST['id'];
        if ($id <= 0) resposta(['erro' => 'ID inválido.']);

        $chk = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = :id AND loja_id = :loja_id");
        $chk->execute([':id'=>$id, ':loja_id'=>$loja_id]);
        if ($chk->fetchColumn() > 0) resposta(['erro' => 'Não é possível excluir: existem produtos vinculados a esta categoria.']);

        $stmt = $pdo->prepare("DELETE FROM categorias_produtos_lojas WHERE id = :id AND loja_id = :loja_id");
        $stmt->execute([':id'=>$id, ':loja_id'=>$loja_id]);

        resposta(['sucesso' => 'Categoria excluída com sucesso.']);
    }

    resposta(['erro' => 'Ação inválida.']);

} catch (PDOException $e) {
    http_response_code(500);
    resposta(['erro' => 'Erro no banco: '.$e->getMessage()]);
}
