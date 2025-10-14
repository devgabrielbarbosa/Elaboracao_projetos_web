<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';
session_start();

// Sessão
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];
$acao     = $_REQUEST['acao'] ?? null;

try {
    // ---------- LISTAR ----------
    if (!$acao || $acao === 'listar') {
        $stmt = $pdo->prepare("
            SELECT id, nome, ativo 
            FROM categorias_produtos_lojas 
            WHERE loja_id = :loja_id 
            ORDER BY nome ASC
        ");
        $stmt->execute([':loja_id' => $loja_id]);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['sucesso'=>true,'categorias' => $categorias]);
        exit;
    }

    // ---------- ADICIONAR ----------
    if ($acao === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') {
            echo json_encode(['sucesso'=>false,'erro' => 'Nome da categoria obrigatório.']);
            exit;
        }

        // Evitar duplicidade no mesmo loja
        $chk = $pdo->prepare("
            SELECT COUNT(*) 
            FROM categorias_produtos_lojas 
            WHERE nome = :nome AND loja_id = :loja_id
        ");
        $chk->execute([':nome'=>$nome, ':loja_id'=>$loja_id]);
        if ($chk->fetchColumn() > 0) {
            echo json_encode(['sucesso'=>false,'erro' => 'Já existe categoria com esse nome nesta loja.']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO categorias_produtos_lojas (nome, loja_id, ativo) 
            VALUES (:nome, :loja_id, 1)
        ");
        $stmt->execute([':nome'=>$nome, ':loja_id'=>$loja_id]);
        echo json_encode(['sucesso'=>true,'mensagem' => 'Categoria adicionada com sucesso.', 'id' => $pdo->lastInsertId()]);
        exit;
    }

    // ---------- DELETAR ----------
    if ($acao === 'deletar' && isset($_REQUEST['id'])) {
        $id = (int) $_REQUEST['id'];
        if ($id <= 0) {
            echo json_encode(['sucesso'=>false,'erro' => 'ID inválido.']);
            exit;
        }

        // Verificar produtos vinculados a esta categoria na mesma loja
        $chk = $pdo->prepare("
            SELECT COUNT(*) 
            FROM produtos 
            WHERE categoria_id = :id AND loja_id = :loja_id
        ");
        $chk->execute([':id'=>$id, ':loja_id'=>$loja_id]);
        if ($chk->fetchColumn() > 0) {
            echo json_encode(['sucesso'=>false,'erro' => 'Não é possível excluir: existem produtos vinculados a esta categoria.']);
            exit;
        }

        $stmt = $pdo->prepare("
            DELETE FROM categorias_produtos_lojas 
            WHERE id = :id AND loja_id = :loja_id
        ");
        $stmt->execute([':id'=>$id, ':loja_id'=>$loja_id]);
        echo json_encode(['sucesso'=>true,'mensagem' => 'Categoria excluída com sucesso.']);
        exit;
    }

    echo json_encode(['sucesso'=>false,'erro' => 'Ação inválida.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso'=>false,'erro' => 'Erro no banco: ' . $e->getMessage()]);
    exit;
}
