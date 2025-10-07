<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    echo json_encode(['erro' => 'Admin ou loja não logado']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$loja_id  = $_SESSION['loja_id'];

// ------------------- Adicionar promoção -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $codigo      = trim($_POST['codigo']);
    $descricao   = trim($_POST['descricao']);
    $desconto    = $_POST['desconto'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim    = $_POST['data_fim'];
    $imagem      = null;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $imagem = file_get_contents($_FILES['imagem']['tmp_name']);
    }

    if (!$imagem) {
        echo json_encode(['erro' => 'É obrigatório enviar a imagem da promoção.']);
        exit;
    }

    // Checar código duplicado
    $check = $pdo->prepare("SELECT COUNT(*) FROM promocoes WHERE codigo=:codigo AND loja_id=:loja_id");
    $check->execute([':codigo' => $codigo, ':loja_id' => $loja_id]);

    if ($check->fetchColumn() > 0) {
        echo json_encode(['erro' => 'Já existe uma promoção com esse código.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO promocoes 
        (admin_id, loja_id, codigo, descricao, desconto, ativo, data_inicio, data_fim, imagem, data_criacao)
        VALUES (:admin_id, :loja_id, :codigo, :descricao, :desconto, 1, :data_inicio, :data_fim, :imagem, NOW())");

    $stmt->bindParam(':admin_id', $admin_id);
    $stmt->bindParam(':loja_id', $loja_id);
    $stmt->bindParam(':codigo', $codigo);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':desconto', $desconto);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->bindParam(':imagem', $imagem, PDO::PARAM_LOB);

    $stmt->execute();

    echo json_encode(['mensagem' => 'Promoção cadastrada com sucesso!']);
    exit;
}

// ------------------- Listar promoções -------------------
$stmt = $pdo->prepare("SELECT * FROM promocoes WHERE loja_id=:loja_id ORDER BY id DESC");
$stmt->execute([':loja_id' => $loja_id]);
$promocoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Converter imagens LONGBLOB para base64
foreach ($promocoes as &$p) {
    if (!empty($p['imagem'])) {
        $p['imagem'] = 'data:image/jpeg;base64,' . base64_encode($p['imagem']);
    } else {
        $p['imagem'] = 'https://via.placeholder.com/350x180?text=Sem+Imagem';
    }
}
unset($p);

echo json_encode(['promocoes' => $promocoes]);
exit;
