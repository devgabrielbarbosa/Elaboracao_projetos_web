<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

// Função para responder JSON
function respostaJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Verifica sessão
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    respostaJSON(['erro' => 'Admin ou loja não logado.'], 401);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];

// Pega ID do produto via GET ou POST
$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) respostaJSON(['erro' => 'Produto não especificado']);

// Buscar produto
$stmt = $pdo->prepare("
    SELECT p.id, p.nome, p.descricao, pl.preco_loja AS preco, p.imagem_principal AS imagem,
           pl.categoria_id
    FROM produtos p
    INNER JOIN produtos_lojas pl ON p.id = pl.produto_id
    WHERE p.id=:id AND pl.loja_id=:loja_id
    LIMIT 1
");
$stmt->execute([':id'=>$id, ':loja_id'=>$loja_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) respostaJSON(['erro' => 'Produto não encontrado']);

// ===== Atualizar produto =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = floatval($_POST['preco'] ?? 0);
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $imagem = $produto['imagem']; // pega imagem atual do banco

    // Se houver nova imagem, ler conteúdo e salvar como LONGBLOB
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $imagemBin = file_get_contents($_FILES['imagem']['tmp_name']);
        $stmtImg = $pdo->prepare("UPDATE produtos SET imagem_principal=:imagem WHERE id=:id");
        $stmtImg->execute([':imagem'=>$imagemBin, ':id'=>$id]);
        $imagem = base64_encode($imagemBin);
    } else if ($imagem) {
        // Se não enviou, converte a imagem existente em base64 para enviar pro front
        $imagem = base64_encode($imagem);
    }

    // Atualiza dados principais do produto
    $stmt = $pdo->prepare("UPDATE produtos SET nome=:nome, descricao=:descricao WHERE id=:id");
    $stmt->execute([
        ':nome'=>$nome,
        ':descricao'=>$descricao,
        ':id'=>$id
    ]);

    // Atualiza dados específicos da loja
    $stmt = $pdo->prepare("UPDATE produtos_lojas SET preco_loja=:preco, categoria_id=:categoria_id WHERE produto_id=:id AND loja_id=:loja_id");
    $stmt->execute([
        ':preco'=>$preco,
        ':categoria_id'=>$categoria_id,
        ':id'=>$id,
        ':loja_id'=>$loja_id
    ]);

    respostaJSON([
        'sucesso' => true,
        'mensagem' => 'Produto atualizado com sucesso!',
        'produto' => [
            'nome'=>$nome,
            'descricao'=>$descricao,
            'preco'=>$preco,
            'imagem'=>$imagem,
            'categoria_id'=>$categoria_id
        ]
    ]);
}

// ===== Retornar produto para preencher formulário =====
if ($produto['imagem']) {
    $produto['imagem'] = base64_encode($produto['imagem']);
}
respostaJSON($produto);
