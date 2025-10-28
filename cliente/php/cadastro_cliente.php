<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';

$data = $_POST;
$loja_id = intval($data['loja_id'] ?? 0);

if (!$loja_id) {
    echo json_encode(['erro' => 'Loja não especificada']);
    exit;
}

$nome = trim($data['nome'] ?? '');
$email = trim($data['email'] ?? '');
$senha = trim($data['senha'] ?? '');
$telefone = trim($data['telefone'] ?? '');
$cpf = trim($data['cpf'] ?? '');

if (!$nome || !$email || !$senha) {
    echo json_encode(['erro' => 'Campos obrigatórios não preenchidos']);
    exit;
}

// Verifica se já existe email
$stmt = $pdo->prepare("SELECT id FROM clientes WHERE email=:email AND loja_id=:loja_id");
$stmt->execute([':email'=>$email, ':loja_id'=>$loja_id]);
if ($stmt->fetch()) {
    echo json_encode(['erro' => 'Email já cadastrado']);
    exit;
}

// Cria cliente
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO clientes (loja_id, nome, email, senha, telefone, cpf, status, data_criacao) VALUES (:loja_id, :nome, :email, :senha, :telefone, :cpf, 'ativo', NOW())");
$stmt->execute([
    ':loja_id'=>$loja_id,
    ':nome'=>$nome,
    ':email'=>$email,
    ':senha'=>$senha_hash,
    ':telefone'=>$telefone,
    ':cpf'=>$cpf
]);

$cliente_id = $pdo->lastInsertId();

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Cadastro realizado com sucesso!',
    'cliente_id' => $cliente_id
]);
