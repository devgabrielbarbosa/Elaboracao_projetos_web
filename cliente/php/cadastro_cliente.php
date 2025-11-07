<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . './../../includes/conexao.php';

// ===== Garante que é POST =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['erro' => 'Método inválido.']);
    exit;
}

// ===== Coleta e sanitiza os dados =====
$nome     = trim($_POST['nome'] ?? '');
$email    = trim($_POST['email'] ?? '');
$cpf      = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$senha    = $_POST['senha'] ?? '';
$loja_id  = $_POST['loja_id'] ?? null;

// ===== Valida campos obrigatórios =====
if (empty($nome) || empty($email) || empty($telefone) || empty($senha) || empty($loja_id)) {
    echo json_encode(['erro' => 'Preencha todos os campos obrigatórios.']);
    exit;
}

try {
    // ===== Verifica duplicidade de e-mail =====
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['erro' => 'E-mail já cadastrado.']);
        exit;
    }

    // ===== Verifica duplicidade de CPF (se informado) =====
    if (!empty($cpf)) {
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE cpf = ?");
        $stmt->execute([$cpf]);
        if ($stmt->fetch()) {
            echo json_encode(['erro' => 'CPF já cadastrado.']);
            exit;
        }
    }

    // ===== Criptografa a senha =====
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // ===== Insere o cliente =====
    $stmt = $pdo->prepare("
        INSERT INTO clientes (nome, cpf, telefone, email, senha, loja_id, status)
        VALUES (?, ?, ?, ?, ?, ?, 'ativo')
    ");
    $stmt->execute([$nome, $cpf, $telefone, $email, $senhaHash, $loja_id]);

    // ===== Retorna sucesso =====
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Cadastro realizado com sucesso!'
    ]);
} catch (PDOException $e) {
    // Só retorna JSON, nunca o objeto PDO
    echo json_encode(['erro' => 'Erro ao salvar cliente: ' . $e->getMessage()]);
}
