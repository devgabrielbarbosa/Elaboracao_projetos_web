<?php
session_start();
require '../includes/conexao.php';

// Proteção: apenas admin logado
if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id']; // Identificador do admin logado
$mensagem = '';

// ========================
// Adicionar promoção
// ========================
if(isset($_POST['acao']) && $_POST['acao'] === 'adicionar'){
    $codigo = trim($_POST['codigo']);
    $descricao = trim($_POST['descricao']);
    $desconto = $_POST['desconto'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $imagem = null;

    // Upload da imagem
    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0){
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid().'.'.$ext;
        $dir = '../uploads/';
        if(!is_dir($dir)) mkdir($dir, 0755, true);
        move_uploaded_file($_FILES['imagem']['tmp_name'], $dir.$novo_nome);
        $imagem = '../uploads/'.$novo_nome;
    }

    if(!$imagem){
        $mensagem = "<div class='alert alert-danger'>É obrigatório enviar a imagem do banner da promoção.</div>";
    } else {
        // Verifica se já existe código duplicado para este admin
        $check = $pdo->prepare("SELECT COUNT(*) FROM promocoes WHERE codigo = :codigo AND admin_id = :admin_id");
        $check->execute([':codigo' => $codigo, ':admin_id'=>$admin_id]);

        if($check->fetchColumn() > 0) {
            $mensagem = "<div class='alert alert-danger'>Já existe uma promoção com esse código.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO promocoes 
                (codigo, descricao, desconto, data_inicio, data_fim, imagem, ativo, admin_id) 
                VALUES (:codigo, :descricao, :desconto, :data_inicio, :data_fim, :imagem, 1, :admin_id)");
            $stmt->execute([
                ':codigo'=>$codigo,
                ':descricao'=>$descricao,
                ':desconto'=>$desconto,
                ':data_inicio'=>$data_inicio,
                ':data_fim'=>$data_fim,
                ':imagem'=>$imagem,
                ':admin_id'=>$admin_id
            ]);
            $mensagem = "<div class='alert alert-success'>Promoção adicionada com sucesso!</div>";
        }
    }
}

// ========================
// Ativar / Desativar / Deletar promoção
// ========================
if(isset($_GET['acao'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if($_GET['acao'] === 'ativar') {
        $pdo->prepare("UPDATE promocoes SET ativo = 1 WHERE id = :id AND admin_id = :admin_id")
            ->execute([':id'=>$id, ':admin_id'=>$admin_id]);
    } elseif($_GET['acao'] === 'desativar') {
        $pdo->prepare("UPDATE promocoes SET ativo = 0 WHERE id = :id AND admin_id = :admin_id")
            ->execute([':id'=>$id, ':admin_id'=>$admin_id]);
    } elseif($_GET['acao'] === 'deletar') {
        $pdo->prepare("DELETE FROM promocoes WHERE id = :id AND admin_id = :admin_id")
            ->execute([':id'=>$id, ':admin_id'=>$admin_id]);
    }
    header("Location: promocoes.php");
    exit;
}

// ========================
// Buscar promoções do admin logado
// ========================
$stmt = $pdo->prepare("SELECT * FROM promocoes WHERE admin_id = :admin_id ORDER BY id DESC");
$stmt->execute([':admin_id' => $admin_id]);
$promocoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Navbar
include 'navbar.php';
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Promoções / Cupons (<?= htmlspecialchars($nome_admin) ?>)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body {
        background: #f7f9fc;
    }
    .banner-img { 
        width: 100%; 
        height: 180px; 
        object-fit: cover; 
        border-top-left-radius: 0.75rem; 
        border-top-right-radius: 0.75rem; 
    }
    .card-promocao {
        border-radius: 0.75rem;
        transition: 0.3s;
    }
    .card-promocao:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 8px 20px rgba(0,0,0,0.15); 
    }
    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
    }
</style>
</head>
<body>

<div class="container my-5">

    <h3 class="mb-4 text-primary fw-bold">
        <i class="bi bi-ticket-perforated-fill me-2"></i>
        Gerenciar Promoções <small class="text-muted">(<?= htmlspecialchars($nome_admin) ?>)</small>
    </h3>
    <?php if($mensagem) echo $mensagem; ?>

    <!-- Formulário adicionar promoção -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-plus-circle me-2"></i> Adicionar Nova Promoção
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="adicionar">

                <div class="mb-3">
                    <label for="codigo" class="form-label">Código da Promoção</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="desconto" class="form-label">Desconto (%)</label>
                    <input type="number" step="0.01" id="desconto" name="desconto" class="form-control" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="data_inicio" class="form-label">Data de Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="data_fim" class="form-label">Data de Fim</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição da Promoção</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="imagem" class="form-label">Imagem do Banner</label>
                    <input type="file" id="imagem" name="imagem" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-save me-2"></i> Adicionar Promoção
                </button>
            </form>
        </div>
    </div>

    <!-- Lista de Promoções -->
    <div class="row g-4">
        <?php if(empty($promocoes)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-emoji-frown me-2"></i> Nenhuma promoção cadastrada no momento.
                </div>
            </div>
        <?php else: ?>
            <?php foreach($promocoes as $p): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 card-promocao">
                    <img src="<?= $p['imagem'] ?: 'https://via.placeholder.com/350x180?text=Sem+Imagem' ?>" 
                         class="banner-img" 
                         alt="<?= htmlspecialchars($p['codigo']) ?>">

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-primary fw-bold">
                            <?= htmlspecialchars($p['codigo']) ?>
                            <span class="badge bg-success ms-2">-<?= htmlspecialchars($p['desconto']) ?>%</span>
                        </h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($p['descricao']) ?></p>
                        <p class="mb-2">
                            <small>
                                <i class="bi bi-calendar-event me-1"></i>
                                <?= date('d/m/Y', strtotime($p['data_inicio'])) ?> até <?= date('d/m/Y', strtotime($p['data_fim'])) ?>
                            </small>
                        </p>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <a href="editar_promocao.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square me-1"></i> Editar
                                </a>
                                <a href="promocoes.php?acao=deletar&id=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Deseja realmente deletar esta promoção?')">
                                   <i class="bi bi-trash3 me-1"></i> Deletar
                                </a>
                            </div>
                            <?php if($p['ativo']): ?>
                                <a href="promocoes.php?acao=desativar&id=<?= $p['id'] ?>" class="badge bg-success text-decoration-none p-2">
                                    <i class="bi bi-check-circle me-1"></i> Ativa
                                </a>
                            <?php else: ?>
                                <a href="promocoes.php?acao=ativar&id=<?= $p['id'] ?>" class="badge bg-secondary text-decoration-none p-2">
                                    <i class="bi bi-pause-circle me-1"></i> Inativa
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
