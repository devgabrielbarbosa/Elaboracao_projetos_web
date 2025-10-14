<?php
session_start();
require '../includes/conexao.php';
header('Content-Type: application/json');

// Verifica sessão de admin e loja
if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    echo json_encode(['sucesso'=>false,'mensagem'=>'Sessão inválida']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$loja_id  = (int)$_SESSION['loja_id'];
$acao     = $_REQUEST['acao'] ?? '';
$id       = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

switch($acao) {
    case 'adicionar':
        $nome = trim($_POST['novo_nome'] ?? '');
        if(!$nome) { 
            echo json_encode(['sucesso'=>false,'mensagem'=>'Nome vazio']); 
            exit; 
        }
        $stmt = $pdo->prepare("INSERT INTO formas_pagamento (nome, admin_id, ativo) VALUES (:nome, :admin_id, 1)");
        $stmt->execute([
            ':nome' => $nome,
            ':admin_id' => $admin_id
        ]);

        // Vincula a forma à loja
        $forma_id = $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("UPDATE formas_pagamento SET loja_id=:loja_id WHERE id=:forma_id");
        $stmt2->execute([
            ':loja_id' => $loja_id,
            ':forma_id' => $forma_id
        ]);

        echo json_encode(['sucesso'=>true,'mensagem'=>'Forma adicionada!']);
        break;

    case 'excluir':
        if(!$id) { 
            echo json_encode(['sucesso'=>false,'mensagem'=>'ID inválido']); 
            exit; 
        }
        $stmt = $pdo->prepare("DELETE FROM formas_pagamento WHERE id=:id AND admin_id=:admin_id AND loja_id=:loja_id");
        $stmt->execute([
            ':id' => $id,
            ':admin_id' => $admin_id,
            ':loja_id' => $loja_id
        ]);
        echo json_encode(['sucesso'=>true,'mensagem'=>'Forma de pagamento excluída!']);
        break;

    case 'toggle':
        if(!$id) { 
            echo json_encode(['sucesso'=>false,'mensagem'=>'ID inválido']); 
            exit; 
        }
        $stmt = $pdo->prepare("SELECT ativo FROM formas_pagamento WHERE id=:id AND admin_id=:admin_id AND loja_id=:loja_id");
        $stmt->execute([
            ':id' => $id,
            ':admin_id' => $admin_id,
            ':loja_id' => $loja_id
        ]);
        $forma = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$forma) { 
            echo json_encode(['sucesso'=>false,'mensagem'=>'Registro não encontrado']); 
            exit; 
        }
        $novo = $forma['ativo'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE formas_pagamento SET ativo=:novo WHERE id=:id AND admin_id=:admin_id AND loja_id=:loja_id");
        $stmt->execute([
            ':novo' => $novo,
            ':id' => $id,
            ':admin_id' => $admin_id,
            ':loja_id' => $loja_id
        ]);
        $novo_status_html = $novo ? '<span class="badge bg-success">Ativa</span>' : '<span class="badge bg-secondary">Inativa</span>';
        $novo_texto_btn = $novo ? 'Desativar' : 'Ativar';
        echo json_encode(['sucesso'=>true,'novo_status_html'=>$novo_status_html,'novo_texto_btn'=>$novo_texto_btn]);
        break;

    default:
        echo json_encode(['sucesso'=>false,'mensagem'=>'Ação inválida']);
}
