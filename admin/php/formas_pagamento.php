<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

// Desliga HTML de erro para não quebrar JSON
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

// Função para responder em JSON
function respostaJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// ===== Sessão =====
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    respostaJSON(['erro' => 'Admin ou loja não logado.'], 401);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];
$acao     = $_REQUEST['acao'] ?? null;

try {
    // ---------- LISTAR ----------
    if (!$acao || $acao === 'listar') {
        $stmt = $pdo->prepare("
            SELECT * FROM formas_pagamento 
            WHERE admin_id = :admin_id AND loja_id = :loja_id
            ORDER BY id DESC
        ");
        $stmt->execute([':admin_id' => $admin_id, ':loja_id' => $loja_id]);
        $formas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        respostaJSON(['formas' => $formas]);
    }

    // ---------- ADICIONAR ----------
    if ($acao === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['novo_nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $chave_pix = $_POST['chave_pix'] ?? null;
        $responsavel_nome = $_POST['responsavel_nome'] ?? null;
        $responsavel_conta = $_POST['responsavel_conta'] ?? null;
        $responsavel_doc = $_POST['responsavel_doc'] ?? null;

        if ($nome === '' || $tipo === '') {
            respostaJSON(['erro' => 'Nome e tipo são obrigatórios.']);
        }

        $stmt = $pdo->prepare("
            INSERT INTO formas_pagamento 
            (nome, tipo, chave_pix, responsavel_nome, responsavel_conta, responsavel_doc, ativo, loja_id, admin_id)
            VALUES (:nome, :tipo, :chave_pix, :responsavel_nome, :responsavel_conta, :responsavel_doc, 1, :loja_id, :admin_id)
        ");
        $stmt->execute([
            ':nome' => $nome,
            ':tipo' => $tipo,
            ':chave_pix' => $chave_pix,
            ':responsavel_nome' => $responsavel_nome,
            ':responsavel_conta' => $responsavel_conta,
            ':responsavel_doc' => $responsavel_doc,
            ':loja_id' => $loja_id,
            ':admin_id' => $admin_id
        ]);

        respostaJSON(['sucesso' => 'Forma adicionada!', 'id' => $pdo->lastInsertId()]);
    }

    // ---------- EDITAR ----------
    if ($acao === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim($_POST['editar_nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $chave_pix = $_POST['chave_pix'] ?? null;
        $responsavel_nome = $_POST['responsavel_nome'] ?? null;
        $responsavel_conta = $_POST['responsavel_conta'] ?? null;
        $responsavel_doc = $_POST['responsavel_doc'] ?? null;

        if (!$id || $nome === '' || $tipo === '') {
            respostaJSON(['erro' => 'Dados inválidos.']);
        }

        $stmt = $pdo->prepare("
            UPDATE formas_pagamento SET
            nome = :nome,
            tipo = :tipo,
            chave_pix = :chave_pix,
            responsavel_nome = :responsavel_nome,
            responsavel_conta = :responsavel_conta,
            responsavel_doc = :responsavel_doc
            WHERE id = :id AND admin_id = :admin_id AND loja_id = :loja_id
        ");
        $stmt->execute([
            ':nome' => $nome,
            ':tipo' => $tipo,
            ':chave_pix' => $chave_pix,
            ':responsavel_nome' => $responsavel_nome,
            ':responsavel_conta' => $responsavel_conta,
            ':responsavel_doc' => $responsavel_doc,
            ':id' => $id,
            ':admin_id' => $admin_id,
            ':loja_id' => $loja_id
        ]);

        respostaJSON(['sucesso' => 'Forma atualizada!']);
    }

    // ---------- EXCLUIR ----------
    if ($acao === 'excluir' && isset($_REQUEST['id'])) {
        $id = (int) $_REQUEST['id'];
        if (!$id) {
            respostaJSON(['erro' => 'ID inválido']);
        }

        $stmt = $pdo->prepare("
            DELETE FROM formas_pagamento 
            WHERE id = :id AND admin_id = :admin_id AND loja_id = :loja_id
        ");
        $stmt->execute([':id' => $id, ':admin_id' => $admin_id, ':loja_id' => $loja_id]);

        respostaJSON(['sucesso' => 'Forma excluída!']);
    }

    // ---------- ATIVAR / DESATIVAR ----------
    if ($acao === 'toggle' && isset($_REQUEST['id'])) {
        $id = (int) $_REQUEST['id'];
        $stmt = $pdo->prepare("
            SELECT ativo FROM formas_pagamento 
            WHERE id = :id AND admin_id = :admin_id AND loja_id = :loja_id
        ");
        $stmt->execute([':id' => $id, ':admin_id' => $admin_id, ':loja_id' => $loja_id]);
        $forma = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$forma) {
            respostaJSON(['erro' => 'Registro não encontrado']);
        }

        $novo = $forma['ativo'] ? 0 : 1;
        $stmt = $pdo->prepare("
            UPDATE formas_pagamento 
            SET ativo = :ativo 
            WHERE id = :id AND admin_id = :admin_id AND loja_id = :loja_id
        ");
        $stmt->execute([
            ':ativo' => $novo,
            ':id' => $id,
            ':admin_id' => $admin_id,
            ':loja_id' => $loja_id
        ]);

        respostaJSON(['sucesso' => 'Status atualizado!', 'novo_status' => $novo]);
    }

    // ---------- AÇÃO INVÁLIDA ----------
    respostaJSON(['erro' => 'Ação inválida']);
} catch (PDOException $e) {
    respostaJSON(['erro' => 'Erro no banco: ' . $e->getMessage()], 500);
}
