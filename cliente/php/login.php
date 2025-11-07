<?php
header('Content-Type: application/json; charset=utf-8');

// Caminho correto para a conexão
require_once __DIR__ . '/../../includes/conexao.php';

$slug = $_GET['loja'] ?? '';
if (empty($slug)) {
    echo json_encode(['erro' => 'Parâmetro "loja" não informado.']);
    exit;
}

// Busca a loja pelo slug (não mais pelo ID)
$stmt = $pdo->prepare("SELECT * FROM lojas WHERE slug = ?");
$stmt->execute([$slug]);
$loja = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$loja) {
    echo json_encode(['erro' => 'Loja não encontrada.']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $loja_id = $_POST['loja_id'];

    $sql = "SELECT * FROM clientes WHERE email = :email AND loja_id = :loja_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email, ':loja_id' => $loja_id]);
    $cliente = $stmt->fetch();

    if($cliente && password_verify($senha, $cliente['senha'])){
        echo json_encode(['success' => true, 'msg' => 'Login realizado']);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Email ou senha incorretos']);
    }
}
?>
