<?php
session_start();
require '../includes/conexao.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$mensagem = "";

// ========================
// Adicionar nova forma
// ========================
if (isset($_POST['novo_nome'], $_POST['tipo'])) {
    $nome = trim($_POST['novo_nome']);
    $tipo = $_POST['tipo'];
    $chave_pix = $_POST['chave_pix'] ?? null;
    $responsavel_nome = $_POST['responsavel_nome'] ?? null;
    $responsavel_conta = $_POST['responsavel_conta'] ?? null;
    $responsavel_doc = $_POST['responsavel_doc'] ?? null;

    if ($nome !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO formas_pagamento 
            (nome, tipo, chave_pix, responsavel_nome, responsavel_conta, responsavel_doc, ativo, admin_id) 
            VALUES 
            (:nome, :tipo, :chave_pix, :responsavel_nome, :responsavel_conta, :responsavel_doc, 1, :admin_id)
        ");
        $stmt->execute([
            ':nome' => $nome,
            ':tipo' => $tipo,
            ':chave_pix' => $chave_pix,
            ':responsavel_nome' => $responsavel_nome,
            ':responsavel_conta' => $responsavel_conta,
            ':responsavel_doc' => $responsavel_doc,
            ':admin_id' => $admin_id
        ]);
        $mensagem = "Forma de pagamento '$nome' adicionada!";
    }
}

// ========================
// Editar forma existente
// ========================
if (isset($_POST['editar_id'], $_POST['editar_nome'], $_POST['tipo'])) {
    $id = (int)$_POST['editar_id'];
    $nome = trim($_POST['editar_nome']);
    $tipo = $_POST['tipo'];
    $chave_pix = $_POST['chave_pix'] ?? null;
    $responsavel_nome = $_POST['responsavel_nome'] ?? null;
    $responsavel_conta = $_POST['responsavel_conta'] ?? null;
    $responsavel_doc = $_POST['responsavel_doc'] ?? null;

    if ($nome !== '') {
        $stmt = $pdo->prepare("
            UPDATE formas_pagamento SET 
                nome=:nome, 
                tipo=:tipo, 
                chave_pix=:chave_pix, 
                responsavel_nome=:responsavel_nome, 
                responsavel_conta=:responsavel_conta, 
                responsavel_doc=:responsavel_doc
            WHERE id=:id AND admin_id=:admin_id
        ");
        $stmt->execute([
            ':nome' => $nome,
            ':tipo' => $tipo,
            ':chave_pix' => $chave_pix,
            ':responsavel_nome' => $responsavel_nome,
            ':responsavel_conta' => $responsavel_conta,
            ':responsavel_doc' => $responsavel_doc,
            ':id' => $id,
            ':admin_id' => $admin_id
        ]);
        $mensagem = "Forma de pagamento atualizada!";
    }
}

// ========================
// Excluir forma
// ========================
if (isset($_GET['excluir_id'])) {
    $id = (int)$_GET['excluir_id'];
    $stmt = $pdo->prepare("DELETE FROM formas_pagamento WHERE id=:id AND admin_id=:admin_id");
    $stmt->execute([':id' => $id, ':admin_id' => $admin_id]);
    $mensagem = "Forma de pagamento excluída!";
}

// ========================
// Ativar / desativar forma
// ========================
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("SELECT ativo FROM formas_pagamento WHERE id=:id AND admin_id=:admin_id");
    $stmt->execute([':id' => $id, ':admin_id' => $admin_id]);
    $forma = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($forma) {
        $novo_estado = $forma['ativo'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE formas_pagamento SET ativo=:ativo WHERE id=:id AND admin_id=:admin_id");
        $stmt->execute([':ativo' => $novo_estado, ':id' => $id, ':admin_id' => $admin_id]);
        $mensagem = $novo_estado ? "Forma ativada!" : "Forma desativada!";
    }
}

// ========================
// Buscar formas do admin logado
// ========================
$stmt = $pdo->prepare("SELECT * FROM formas_pagamento WHERE admin_id=:admin_id ORDER BY id DESC");
$stmt->execute([':admin_id' => $admin_id]);
$formas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Formas de Pagamento - Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.card { border-radius: 0.75rem; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
.card-header { background-color: #dc3545; color: #fff; font-weight: bold; }
.table th, .table td { vertical-align: middle; }
.btn-sm { min-width: 70px; margin-bottom: 3px; }
.input-group .form-control { border-radius: 0.375rem 0 0 0.375rem; }
.input-group .btn { border-radius: 0 0.375rem 0.375rem 0; }
</style>
</head>
<body class="bg-light">
<div class="container my-5">

    <h3 class="text-danger mb-4">Formas de Pagamento da Loja (ID <?= $admin_id ?>)</h3>

    <?php if($mensagem): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <!-- Adicionar nova forma -->
    <div class="card mb-4">
        <div class="card-header">Adicionar Nova Forma</div>
        <div class="card-body">
            <form method="POST" class="row g-2">
                <div>
                    <input type="text" name="novo_nome" class="form-control" placeholder="Nome da forma" required>
                </div>
                <div>
                    <select name="tipo" class="form-select" required>
                        <option value="">Tipo</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="cartao">Cartão</option>
                        <option value="pix">PIX</option>
                    </select>
                </div>
                <div>
                    <input type="text" name="chave_pix" class="form-control" placeholder="Chave PIX">
                </div>
                <div>
                    <input type="text" name="responsavel_nome" class="form-control" placeholder="Responsável">
                </div>
                <div>
                    <input type="text" name="responsavel_conta" class="form-control" placeholder="Conta/Agência">
                </div>
                <div>
                    <button type="submit" class="btn btn-success w-100">Adicionar</button>
                </div>
            </form>
        </div>
    </div>

  <!-- Lista de formas -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white fw-bold">
        Formas Cadastradas
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-danger text-center">
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Chave PIX</th>
                        <th>Responsável</th>
                        <th>Conta</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($formas): ?>
                        <?php foreach($formas as $f): ?>
                        <tr class="text-center">
                            <td class="text-start"><?= htmlspecialchars($f['nome']) ?></td>
                            <td><?= htmlspecialchars($f['tipo']) ?></td>
                            <td><?= htmlspecialchars($f['chave_pix']) ?: '-' ?></td>
                            <td class="text-start"><?= htmlspecialchars($f['responsavel_nome']) ?: '-' ?></td>
                            <td><?= htmlspecialchars($f['responsavel_conta']) ?: '-' ?></td>
                            <td>
                                <span class="badge <?= $f['ativo'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $f['ativo'] ? 'Ativa' : 'Inativa' ?>
                                </span>
                            </td>
                            <td class="flex-column flex-md-row gap-1 justify-content-center">
                                <a href="?editar_id=<?= $f['id'] ?>" class="btn btn-primary btn-sm w-100 w-md-auto">Editar</a>
                                <a href="?toggle_id=<?= $f['id'] ?>" class="btn btn-warning btn-sm w-100 w-md-auto">Ativar/Desativar</a>
                                <a href="?excluir_id=<?= $f['id'] ?>" onclick="return confirm('Deseja realmente excluir?')" class="btn btn-danger btn-sm w-100 w-md-auto">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Nenhuma forma cadastrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
