<?php
session_start();
require '../includes/conexao.php';
header('Content-Type: application/json; charset=utf-8');

if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão expirada']);
    exit;
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];
$acao     = $_REQUEST['acao'] ?? null;

try {
    // ---------- LISTAR ----------
    if (!$acao || $acao === 'listar') {
        $stmt = $pdo->prepare("SELECT * FROM formas_pagamento WHERE admin_id = :admin_id ORDER BY id DESC");
        $stmt->execute([':admin_id' => $admin_id]);
        $formas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['formas' => $formas]);
        exit;
    }

    // ---------- ADICIONAR ----------
    if ($acao === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['novo_nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $chave_pix = $_POST['chave_pix'] ?? null;
        $responsavel_nome = $_POST['responsavel_nome'] ?? null;
        $responsavel_conta = $_POST['responsavel_conta'] ?? null;
        $responsavel_doc = $_POST['responsavel_doc'] ?? null;

        if ($nome === '') {
            echo json_encode(['erro' => 'Nome obrigatório.']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO formas_pagamento 
            (nome, tipo, chave_pix, responsavel_nome, responsavel_conta, responsavel_doc, ativo, admin_id) 
            VALUES (:nome,:tipo,:chave_pix,:responsavel_nome,:responsavel_conta,:responsavel_doc,1,:admin_id)
        ");
        $stmt->execute([
            ':nome'=>$nome,
            ':tipo'=>$tipo,
            ':chave_pix'=>$chave_pix,
            ':responsavel_nome'=>$responsavel_nome,
            ':responsavel_conta'=>$responsavel_conta,
            ':responsavel_doc'=>$responsavel_doc,
            ':admin_id'=>$admin_id
        ]);

        echo json_encode(['sucesso'=>'Forma adicionada!', 'id'=>$pdo->lastInsertId()]);
        exit;
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

        if (!$id || $nome === '') {
            echo json_encode(['erro' => 'Dados inválidos.']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE formas_pagamento SET
            nome=:nome, tipo=:tipo, chave_pix=:chave_pix, responsavel_nome=:responsavel_nome,
            responsavel_conta=:responsavel_conta, responsavel_doc=:responsavel_doc
            WHERE id=:id AND admin_id=:admin_id
        ");
        $stmt->execute([
            ':nome'=>$nome,
            ':tipo'=>$tipo,
            ':chave_pix'=>$chave_pix,
            ':responsavel_nome'=>$responsavel_nome,
            ':responsavel_conta'=>$responsavel_conta,
            ':responsavel_doc'=>$responsavel_doc,
            ':id'=>$id,
            ':admin_id'=>$admin_id
        ]);

        echo json_encode(['sucesso'=>'Forma atualizada!']);
        exit;
    }

    // ---------- EXCLUIR ----------
    if ($acao === 'excluir' && isset($_REQUEST['id'])) {
        $id = (int) $_REQUEST['id'];
        if (!$id) {
            echo json_encode(['erro'=>'ID inválido']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM formas_pagamento WHERE id=:id AND admin_id=:admin_id");
        $stmt->execute([':id'=>$id, ':admin_id'=>$admin_id]);
        echo json_encode(['sucesso'=>'Forma excluída!']);
        exit;
    }

    // ---------- ATIVAR / DESATIVAR ----------
    if ($acao === 'toggle' && isset($_REQUEST['id'])) {
        $id = (int) $_REQUEST['id'];
        $stmt = $pdo->prepare("SELECT ativo FROM formas_pagamento WHERE id=:id AND admin_id=:admin_id");
        $stmt->execute([':id'=>$id, ':admin_id'=>$admin_id]);
        $forma = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$forma){
            echo json_encode(['erro'=>'Registro não encontrado']);
            exit;
        }

        $novo = $forma['ativo'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE formas_pagamento SET ativo=:ativo WHERE id=:id AND admin_id=:admin_id");
        $stmt->execute([':ativo'=>$novo, ':id'=>$id, ':admin_id'=>$admin_id]);

        echo json_encode(['sucesso'=>'Status atualizado!', 'novo_status'=>$novo]);
        exit;
    }

    echo json_encode(['erro'=>'Ação inválida']);

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['erro'=>'Erro no banco: '.$e->getMessage()]);
    exit;
}
