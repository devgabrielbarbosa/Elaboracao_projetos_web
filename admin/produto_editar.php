<?php
session_start();
require '../includes/conexao.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$mensagem = '';
$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: produtos.php");
    exit;
}

// Buscar produto do admin logado
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id=:id AND admin_id=:admin_id");
$stmt->execute([':id'=>$id, ':admin_id'=>$admin_id]);
$produto = $stmt->fetch();

if(!$produto){
    header("Location: produtos.php");
    exit;
}

// Atualizar produto
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $imagem = $produto['imagem']; // manter imagem existente

    if(isset($_FILES['imagem']) && $_FILES['imagem']['error']===0){
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid().'.'.$ext;
        $dir = '../uploads/';
        if(!is_dir($dir)) mkdir($dir, 0755);
        move_uploaded_file($_FILES['imagem']['tmp_name'], $dir.$novo_nome);
        $imagem = '../uploads/'.$novo_nome;
    }

    $stmt = $pdo->prepare("UPDATE produtos 
                           SET nome=:nome, descricao=:descricao, preco=:preco, imagem=:imagem 
                           WHERE id=:id AND admin_id=:admin_id");
    $stmt->execute([
        ':nome'=>$nome,
        ':descricao'=>$descricao,
        ':preco'=>$preco,
        ':imagem'=>$imagem,
        ':id'=>$id,
        ':admin_id'=>$admin_id
    ]);

    $mensagem = "<div class='alert alert-success'>Produto atualizado com sucesso!</div>";
    // atualizar dados do formulário
    $produto['nome'] = $nome;
    $produto['descricao'] = $descricao;
    $produto['preco'] = $preco;
    $produto['imagem'] = $imagem;
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Editar Produto (<?= htmlspecialchars($nome_admin) ?>)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.card-editar { max-width: 600px; margin: auto; margin-top:50px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.2);}
.produto-img { width:100%; height:250px; object-fit:cover; border-radius:10px 10px 0 0;}
</style>
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="card card-editar">
    <img src="<?= $produto['imagem'] ?? 'https://via.placeholder.com/600x250?text=Sem+Imagem' ?>" class="produto-img" alt="<?= htmlspecialchars($produto['nome']) ?>">
    <div class="card-body">
        <h4 class="card-title mb-3">Editar Produto</h4>
        <?php if($mensagem) echo $mensagem; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Nome</label>
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($produto['nome']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Descrição</label>
                <textarea name="descricao" class="form-control"><?= htmlspecialchars($produto['descricao']) ?></textarea>
            </div>
            <div class="mb-3">
                <label>Preço (R$)</label>
                <input type="number" step="0.01" name="preco" class="form-control" value="<?= $produto['preco'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Imagem (opcional)</label>
                <input type="file" name="imagem" class="form-control">
            </div>
            <button type="submit" class="btn btn-success w-100">Atualizar Produto</button>
            <a href="produtos.php" class="btn btn-secondary w-100 mt-2">Voltar</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

