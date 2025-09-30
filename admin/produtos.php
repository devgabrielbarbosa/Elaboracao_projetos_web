<?php
session_start();
require '../includes/conexao.php';

// Verifica login do admin
if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$loja_id  = $_SESSION['loja_id'];
$mensagem = '';

// ------------------- ADICIONAR PRODUTO -------------------
if(isset($_POST['acao']) && $_POST['acao'] === 'adicionar'){
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = (float)$_POST['preco'];
    $imagem_blob = null;

    // pega imagem enviada
    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0){
        $imagem_blob = file_get_contents($_FILES['imagem']['tmp_name']);
    }

    // insere produto
    $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, preco, ativo, loja_id, admin_id) VALUES (:nome, :descricao, :preco, 1, :loja_id, :admin_id)");
    $stmt->execute([
        ':nome'=>$nome,
        ':descricao'=>$descricao,
        ':preco'=>$preco,
        ':loja_id'=>$loja_id,
        ':admin_id'=>$admin_id
    ]);

    $produto_id = $pdo->lastInsertId();

    // insere imagem na tabela fotos_produto
    if($imagem_blob){
        $stmt2 = $pdo->prepare("INSERT INTO fotos_produto (produto_id, url) VALUES (:produto_id, :imagem)");
        $stmt2->bindParam(':produto_id',$produto_id,PDO::PARAM_INT);
        $stmt2->bindParam(':imagem',$imagem_blob,PDO::PARAM_LOB);
        $stmt2->execute();
    }

    $mensagem = "<div class='alert alert-success'>Produto adicionado com sucesso!</div>";
}

if(isset($_GET['acao'], $_GET['id']) && $_GET['acao'] === 'deletar'){
    $produto_id = (int)$_GET['id'];

    // ✅ Se você não usou ON DELETE CASCADE, deleta as imagens primeiro
    $pdo->prepare("DELETE FROM fotos_produto WHERE produto_id=:id")->execute([':id' => $produto_id]);

    // Deleta o produto
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id=:id AND admin_id=:admin_id AND loja_id=:loja_id");
    $stmt->execute([':id'=>$produto_id, ':admin_id'=>$admin_id, ':loja_id'=>$loja_id]);

    header("Location: produtos.php");
    exit;
}


// ------------------- ATIVAR / PAUSAR -------------------
if(isset($_GET['acao'], $_GET['id'])){
    if($_GET['acao']==='pausar'){
        $pdo->prepare("UPDATE produtos SET ativo=0 WHERE id=:id AND loja_id=:loja_id AND admin_id=:admin_id")
            ->execute([':id'=>$_GET['id'], ':loja_id'=>$loja_id, ':admin_id'=>$admin_id]);
    } elseif($_GET['acao']==='ativar'){
        $pdo->prepare("UPDATE produtos SET ativo=1 WHERE id=:id AND loja_id=:loja_id AND admin_id=:admin_id")
            ->execute([':id'=>$_GET['id'], ':loja_id'=>$loja_id, ':admin_id'=>$admin_id]);
    }
    header("Location: produtos.php");
    exit;
}

// ------------------- LISTAR PRODUTOS -------------------
$stmt = $pdo->prepare("
    SELECT p.*, fp.id AS foto_id
    FROM produtos p
    LEFT JOIN fotos_produto fp ON fp.produto_id = p.id
    WHERE p.loja_id=:loja_id AND p.admin_id=:admin_id
    ORDER BY p.data_criacao DESC
");
$stmt->execute([':loja_id'=>$loja_id, ':admin_id'=>$admin_id]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'navbar.php';
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Produtos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.card-produto{transition: .2s;}
.card-produto:hover{transform: scale(1.03); box-shadow:0 5px 15px rgba(0,0,0,0.3);}
.produto-img{height:180px; object-fit:cover; border-radius:5px;}
.badge-ativo{font-size:.8rem; padding:.4em .6em;}
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
            <input type="text" name="nome" class="form-control mb-2" placeholder="Nome" required>
            <textarea name="descricao" class="form-control mb-2" placeholder="Descrição"></textarea>
            <input type="number" step="0.01" name="preco" class="form-control mb-2" placeholder="Preço" required>
            <input type="file" name="imagem" class="form-control mb-2">
            <button type="submit" class="btn btn-success w-100">Adicionar Produto</button>
        </form>
    </div>
</div>

<!-- Lista de produtos -->
<div class="row g-4">
<?php if(empty($produtos)): ?>
<p class="text-center text-muted">Nenhum produto cadastrado.</p>
<?php endif; ?>

<?php foreach($produtos as $p): ?>
<div class="col-md-4">
    <div class="card card-produto shadow-sm">
        <img src="imagem_produto.php?id=<?= $p['id'] ?>" class="produto-img" alt="<?= htmlspecialchars($p['nome']) ?>">
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($p['descricao']) ?></p>
            <p class="card-text text-danger fw-bold">R$ <?= number_format($p['preco'],2,",",".") ?></p>
            <span class="badge <?= $p['ativo']?'bg-success':'bg-secondary' ?> badge-ativo">
                <?= $p['ativo']?'Ativo':'Pausado' ?>
            </span>
            <div class="d-flex justify-content-between mt-2">
                <a href="produtos.php?acao=<?= $p['ativo']?'pausar':'ativar' ?>&id=<?= $p['id'] ?>" class="btn btn-sm <?= $p['ativo']?'btn-warning':'btn-success' ?>"><?= $p['ativo']?'Pausar':'Ativar' ?></a>
                <a href="produtos_editar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                <a href="produtos.php?acao=deletar&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente deletar?')">Deletar</a>
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
