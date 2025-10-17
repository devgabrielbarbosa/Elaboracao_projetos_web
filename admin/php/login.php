<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['erro' => 'Método inválido.']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if ($email === '' || $senha === '') {
    echo json_encode(['erro' => 'Preencha todos os campos.']);
    exit;
}

// Buscar o administrador pelo e-mail
$stmt = $pdo->prepare("SELECT id, nome, email, senha, loja_id, foto FROM administradores WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo json_encode(['erro' => 'E-mail não encontrado.']);
    exit;
}

// Verifica senha
if (!password_verify($senha, $admin['senha'])) {
    echo json_encode(['erro' => 'Senha incorreta.']);
    exit;
}

// Verifica se o admin está vinculado a uma loja
if (empty($admin['loja_id']) || (int)$admin['loja_id'] <= 0) {
    echo json_encode(['erro' => 'Administrador sem loja associada.']);
    exit;
}

// Cria sessão
$_SESSION['admin_id']   = (int)$admin['id'];
$_SESSION['admin_nome'] = $admin['nome'];
$_SESSION['loja_id']    = (int)$admin['loja_id'];
$_SESSION['admin_foto'] = $admin['foto'] ?? 'https://via.placeholder.com/200x150?text=Sem+Imagem';

// Retorna JSON de sucesso
echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Login realizado com sucesso.',
    'nome' => $admin['nome'],
    'foto' => $_SESSION['admin_foto'],
    'loja_id' => (int)$admin['loja_id']
]);
exit;
