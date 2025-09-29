<?php
session_start();
require '../includes/conexao.php';

// 1️⃣ Verifica login do admin
if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$loja_id  = $_SESSION['loja_id'];
$mensagem = '';

// 2️⃣ Adicionar produto
if(isset($_POST['acao']) && $_POST['acao'] === 'adicionar'){
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = (float)$_POST['preco'];
    $imagem = null;

    if(isset($_FILES['imagem']) && $_FILES['imagem']['error']===0){
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid().'.'.$ext;
        $dir = '../uploads/';
        if(!is_dir($dir)) mkdir($dir, 0755);
        $caminho_arquivo = $dir.$novo_nome;

        if(move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_arquivo)){
            $imagem = '../uploads/'.$novo_nome; // 🚨 corrigido: sem "../"
        } else {
            error_log("❌ Erro ao mover arquivo: ".$_FILES['imagem']['tmp_name']." → ".$caminho_arquivo);
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO produtos (nome, descricao, preco, imagem, ativo, admin_id, loja_id) 
        VALUES (:nome, :descricao, :preco, :imagem, 1, :admin_id, :loja_id)
    ");
    $stmt->execute([
        ':nome' => $nome,
        ':descricao' => $descricao,
        ':preco' => $preco,
        ':imagem' => $imagem,
        ':admin_id' => $admin_id,
        ':loja_id' => $loja_id
    ]);

    // Debug: mostra imagem salva
    error_log("✅ Produto inserido: $nome | Imagem: $imagem");
    $mensagem = "<div class='alert alert-success'>Produto adicionado com sucesso!</div>";
}

// 3️⃣ Deletar produto
if(isset($_GET['acao']) && $_GET['acao'] === 'deletar' && isset($_GET['id'])){
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id=:id AND admin_id=:admin_id AND loja_id=:loja_id");
    $stmt->execute([':id'=>$_GET['id'], ':admin_id'=>$admin_id, ':loja_id'=>$loja_id]);
    header("Location: produtos.php");
    exit;
}

// 4️⃣ Pausar / Ativar produto
if(isset($_GET['acao'], $_GET['id'])){
    if($_GET['acao'] === 'pausar'){
        $pdo->prepare("UPDATE produtos SET ativo=0 WHERE id=:id AND admin_id=:admin_id AND loja_id=:loja_id")
            ->execute([':id'=>$_GET['id'], ':admin_id'=>$admin_id, ':loja_id'=>$loja_id]);
    } elseif($_GET['acao'] === 'ativar'){
        $pdo->prepare("UPDATE produtos SET ativo=1 WHERE id=:id AND admin_id=:admin_id AND loja_id=:loja_id")
            ->execute([':id'=>$_GET['id'], ':admin_id'=>$admin_id, ':loja_id'=>$loja_id]);
    }
    header("Location: produtos.php");
    exit;
}

// 5️⃣ Buscar produtos do admin e loja logada
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE admin_id=:admin_id AND loja_id=:loja_id ORDER BY data_criacao DESC");
$stmt->execute([':admin_id'=>$admin_id, ':loja_id'=>$loja_id]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: lista os produtos e caminho da imagem
error_log("📦 Produtos carregados:");
foreach($produtos as $pp){
    error_log("   ID {$pp['id']} | Nome {$pp['nome']} | Img: {$pp['imagem']}");
}

// Navbar
include 'navbar.php';
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Produtos - (<?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin') ?>)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.card-produto { transition: transform 0.2s; }
.card-produto:hover { transform: scale(1.03); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
.produto-img { height: 180px; object-fit: cover; border-radius: 5px; }
.badge-ativo { font-size: 0.8rem; padding: 0.4em 0.6em; }
</style>
</head>
<body class="bg-light">
<div class="container my-5">
    <h3 class="mb-4">Gerenciar Produtos</h3>
    <?php if($mensagem) echo $mensagem; ?>

    <!-- Formulário adicionar produto -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">Adicionar Produto</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="adicionar">
                <div class="mb-2">
                    <input type="text" name="nome" class="form-control" placeholder="Nome do produto" required>
                </div>
                <div class="mb-2">
                    <textarea name="descricao" class="form-control" placeholder="Descrição do produto"></textarea>
                </div>
                <div class="mb-2">
                    <input type="number" step="0.01" name="preco" class="form-control" placeholder="Preço (R$)" required>
                </div>
                <div class="mb-2">
                    <input type="file" name="imagem" class="form-control">
                </div>
                <button type="submit" class="btn btn-success w-100">Adicionar Produto</button>
            </form>
        </div>
    </div>

    <!-- Cards de produtos -->
    <div class="row g-4">
    <?php if(empty($produtos)): ?>
        <p class="text-center text-muted">Nenhum produto cadastrado ainda.</p>
    <?php endif; ?>
    
    <?php foreach($produtos as $p): ?>
    <div class="col-md-4">
        <div class="card card-produto shadow-sm">
            <!-- Debug visível -->
            <?php if(!$p['imagem']): ?>
                <small class="text-danger">⚠ Produto sem imagem</small>
            <?php endif; ?>

            <img src="<?= htmlspecialchars($p['imagem'] ?: 'img/product-placeholder.png') ?>" 
                 class="produto-img" 
                 alt="<?= htmlspecialchars($p['nome']) ?>">

            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($p['descricao']) ?></p>
                <p class="card-text text-danger fw-bold">R$ <?= number_format($p['preco'],2,",",".") ?></p>
                
                <span class="badge <?= $p['ativo'] ? 'bg-success' : 'bg-secondary' ?> badge-ativo">
                    <?= $p['ativo'] ? 'Ativo' : 'Pausado' ?>
                </span>

                <div class="d-flex justify-content-between mt-2">
                    <a href="produtos.php?acao=<?= $p['ativo'] ? 'pausar' : 'ativar' ?>&id=<?= $p['id'] ?>" 
                       class="btn btn-sm <?= $p['ativo'] ? 'btn-warning' : 'btn-success' ?>">
                        <?= $p['ativo'] ? 'Pausar' : 'Ativar' ?>
                    </a>
                    <a href="produtos_editar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="produtos.php?acao=deletar&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" 
                       onclick="return confirm('Deseja realmente deletar?')">Deletar</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
