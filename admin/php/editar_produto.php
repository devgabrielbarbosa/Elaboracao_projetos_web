<?php
session_start();
require '../includes/conexao.php';
header('Content-Type: application/json; charset=utf-8');

// Sessão
if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    echo json_encode(['erro' => 'Sessão expirada']);
    exit;
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];
$id       = (int) ($_GET['id'] ?? 0);

if(!$id){
    echo json_encode(['erro' => 'Produto não especificado']);
    exit;
}

// Buscar produto apenas da loja do admin
$stmt = $pdo->prepare("
    SELECT p.id, p.nome, p.descricao, pl.preco_loja as preco, p.imagem_principalfotos_produto as imagem
    FROM produtos p
    INNER JOIN produtos_lojas pl ON p.id = pl.produto_id
    WHERE p.id=:id AND pl.loja_id=:loja_id
    LIMIT 1
");
$stmt->execute([':id'=>$id, ':loja_id'=>$loja_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$produto){
    echo json_encode(['erro' => 'Produto não encontrado']);
    exit;
}

// Atualizar produto
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome      = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco     = floatval($_POST['preco'] ?? 0);
    $imagem    = $produto['imagem'];

    // Upload da imagem
    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0){
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid().'.'.$ext;
        $dir = '../uploads/';
        if(!is_dir($dir)) mkdir($dir, 0755);
        move_uploaded_file($_FILES['imagem']['tmp_name'], $dir.$novo_nome);
        $imagem = 'uploads/'.$novo_nome;
    }

    // Atualizar tabela produtos (nome, descrição)
    $stmt = $pdo->prepare("UPDATE produtos SET nome=:nome, descricao=:descricao, imagem_principalfotos_produto=:imagem WHERE id=:id");
    $stmt->execute([
        ':nome'=>$nome,
        ':descricao'=>$descricao,
        ':imagem'=>$imagem,
        ':id'=>$id
    ]);

    // Atualizar preço na tabela produtos_lojas
    $stmt = $pdo->prepare("UPDATE produtos_lojas SET preco_loja=:preco WHERE produto_id=:id AND loja_id=:loja_id");
    $stmt->execute([
        ':preco'=>$preco,
        ':id'=>$id,
        ':loja_id'=>$loja_id
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Produto atualizado com sucesso!',
        'produto' => [
            'nome'=>$nome,
            'descricao'=>$descricao,
            'preco'=>$preco,
            'imagem'=>$imagem
        ]
    ]);
    exit;
}

// Retornar produto para preencher formulário via AJAX
echo json_encode($produto);
