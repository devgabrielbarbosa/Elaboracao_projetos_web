<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['erro' => 'Método inválido.']);
    exit;
}

// Pega dados do POST
$nome_loja   = trim($_POST['nome_loja'] ?? '');
$email_admin = trim($_POST['email_admin'] ?? '');
$senha_admin = trim($_POST['senha_admin'] ?? '');

// Validação básica
if (!$nome_loja || !$email_admin || !$senha_admin) {
    echo json_encode(['erro' => 'Preencha todos os campos.']);
    exit;
}

// Checar duplicidade de email
$stmt = $pdo->prepare("SELECT COUNT(*) FROM administradores WHERE email = :email");
$stmt->execute([':email' => $email_admin]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['erro' => 'E-mail do administrador já cadastrado.']);
    exit;
}

// Hash da senha
$senha_hash = password_hash($senha_admin, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    // 1️⃣ Criar loja
    $stmt = $pdo->prepare("INSERT INTO lojas (nome, status, data_criacao) VALUES (:nome, 'fechado', NOW())");
    $stmt->execute([':nome' => $nome_loja]);
    $loja_id = $pdo->lastInsertId();

    // 2️⃣ Criar administrador vinculado à loja
    $stmt = $pdo->prepare("
        INSERT INTO administradores (nome, email, senha, nivel, loja_id, data_criacao)
        VALUES (:nome, :email, :senha, 'admin', :loja_id, NOW())
    ");
    $stmt->execute([
        ':nome' => $nome_loja,
        ':email' => $email_admin,
        ':senha' => $senha_hash,
        ':loja_id' => $loja_id
    ]);

    $pdo->commit();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Loja e administrador criados com sucesso!',
        'loja_id' => $loja_id
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'erro' => 'Falha ao criar loja/admin: ' . $e->getMessage()
    ]);
}
exit;