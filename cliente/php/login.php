<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';

$data = $_POST;
$loja_id = intval($data['loja_id'] ?? 0);
$email = trim($data['email'] ?? '');
$senha = trim($data['senha'] ?? '');

if (!$loja_id || !$email || !$senha) {
    echo json_encode(['erro' => 'Campos obrigatórios não preenchidos']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, senha, nome FROM clientes WHERE email=:email AND loja_id=:loja_id AND status='ativo' LIMIT 1");
$stmt->execute([':email'=>$email, ':loja_id'=>$loja_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente || !password_verify($senha, $cliente['senha'])) {
    echo json_encode(['erro' => 'Email ou senha inválidos']);
    exit;
}

// Autentica cliente na sessão
$_SESSION['cliente_id'] = $cliente['id'];
$_SESSION['loja_id'] = $loja_id;
$_SESSION['cliente_nome'] = $cliente['nome'];

echo json_encode(['sucesso'=>true]);
