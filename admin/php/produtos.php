<?php
session_start();
require '../includes/conexao.php';

if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$loja_id  = $_SESSION['loja_id'];
$mensagem = '';

// ADICIONAR PRODUTO
if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'adicionar'){
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = (float)$_POST['preco'];
    $imagem_blob = null;

    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0){
        $imagem_blob = file_get_contents($_FILES['imagem']['tmp_name']);
    }

    $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, preco, ativo, loja_id, admin_id) VALUES (:nome, :descricao, :preco, 1, :loja_id, :admin_id)");
    $stmt->execute([
        ':nome'=>$nome,
        ':descricao'=>$descricao,
        ':preco'=>$preco,
        ':loja_id'=>$loja_id,
        ':admin_id'=>$admin_id
    ]);

    $produto_id = $pdo->lastInsertId();
    if($imagem_blob){
        $stmt2 = $pdo->prepare("INSERT INTO fotos_produto (produto_id, url) VALUES (:produto_id, :imagem)");
        $stmt2->bindParam(':produto_id',$produto_id,PDO::PARAM_INT);
        $stmt2->bindParam(':imagem',$imagem_blob,PDO::PARAM_LOB);
        $stmt2->execute();
    }

    $mensagem = "Produto adicionado com sucesso!";
}

// AÇÕES GET (deletar, ativar, pausar)
if(isset($_GET['acao'], $_GET['id'])){
    $produto_id = (int)$_GET['id'];

    if($_GET['acao']==='deletar'){
        $pdo->prepare("DELETE FROM fotos_produto WHERE produto_id=:id")->execute([':id'=>$produto_id]);
        $pdo->prepare("DELETE FROM produtos WHERE id=:id AND admin_id=:admin_id AND loja_id=:loja_id")
            ->execute([':id'=>$produto_id, ':admin_id'=>$admin_id, ':loja_id'=>$loja_id]);
        header("Location: ../paginas/produtos.html");
        exit;
    }

    if($_GET['acao']==='pausar'){
        $pdo->prepare("UPDATE produtos SET ativo=0 WHERE id=:id AND loja_id=:loja_id AND admin_id=:admin_id")
            ->execute([':id'=>$produto_id, ':loja_id'=>$loja_id, ':admin_id'=>$admin_id]);
    } elseif($_GET['acao']==='ativar'){
        $pdo->prepare("UPDATE produtos SET ativo=1 WHERE id=:id AND loja_id=:loja_id AND admin_id=:admin_id")
            ->execute([':id'=>$produto_id, ':loja_id'=>$loja_id, ':admin_id'=>$admin_id]);
    }
    header("Location: ../paginas/produtos.html");
    exit;
}

// LISTAR PRODUTOS
$stmt = $pdo->prepare("
    SELECT p.*, fp.id AS foto_id
    FROM produtos p
    LEFT JOIN fotos_produto fp ON fp.produto_id = p.id
    WHERE p.loja_id=:loja_id AND p.admin_id=:admin_id
    ORDER BY p.data_criacao DESC
");
$stmt->execute([':loja_id'=>$loja_id, ':admin_id'=>$admin_id]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gerar JSON para o HTML
header('Content-Type: application/json');
echo json_encode([
    'mensagem' => $mensagem,
    'produtos' => $produtos
]);
?>