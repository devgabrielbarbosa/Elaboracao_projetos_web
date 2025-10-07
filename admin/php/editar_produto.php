<?php
session_start();
require '../includes/conexao.php';

if(!isset($_SESSION['admin_id'])){
    echo json_encode(['erro' => 'Sessão expirada']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$id = $_GET['id'] ?? null;

if(!$id){
    echo json_encode(['erro' => 'Produto não especificado']);
    exit;
}

// Buscar produto
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id=:id AND admin_id=:admin_id");
$stmt->execute([':id'=>$id, ':admin_id'=>$admin_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$produto){
    echo json_encode(['erro' => 'Produto não encontrado']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $imagem = $produto['imagem'];

    if(isset($_FILES['imagem']) && $_FILES['imagem']['error']===0){
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid().'.'.$ext;
        $dir = '../uploads/';
        if(!is_dir($dir)) mkdir($dir, 0755);
        move_uploaded_file($_FILES['imagem']['tmp_name'], $dir.$novo_nome);
        $imagem = 'uploads/'.$novo_nome;
    }

    $stmt = $pdo->prepare("UPDATE produtos SET nome=:nome, descricao=:descricao, preco=:preco, imagem=:imagem WHERE id=:id AND admin_id=:admin_id");
    $stmt->execute([
        ':nome'=>$nome,
        ':descricao'=>$descricao,
        ':preco'=>$preco,
        ':imagem'=>$imagem,
        ':id'=>$id,
        ':admin_id'=>$admin_id
    ]);

    echo json_encode(['sucesso' => 'Produto atualizado com sucesso!', 'produto' => ['nome'=>$nome,'descricao'=>$descricao,'preco'=>$preco,'imagem'=>$imagem]]);
    exit;
}

// Retornar produto para preencher o formulário via AJAX
echo json_encode($produto);
