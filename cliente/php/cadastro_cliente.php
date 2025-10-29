<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validação dos campos obrigatórios
    $slug = trim($data['slug'] ?? '');
    $nome = trim($data['nome'] ?? '');
    $cpf = trim($data['cpf'] ?? '');
    $telefone = trim($data['telefone'] ?? '');
    $email = trim($data['email'] ?? '');
    $senha = trim($data['senha'] ?? '');
    $data_nascimento = trim($data['data_nascimento'] ?? null);

    if (!$slug || !$nome || !$cpf || !$telefone || !$email || !$senha) {
        echo json_encode(['erro' => 'Campos obrigatórios não preenchidos']);
        exit;
    }

    // Busca a loja pelo slug
    $stmtLoja = $pdo->prepare("SELECT id FROM lojas WHERE slug=:slug LIMIT 1");
    $stmtLoja->execute([':slug' => $slug]);
    $loja = $stmtLoja->fetch(PDO::FETCH_ASSOC);

    if (!$loja) {
        echo json_encode(['erro' => 'Loja não encontrada']);
        exit;
    }

    $loja_id = (int)$loja['id'];

    // Verifica se o email já existe na loja
    $stmtCheck = $pdo->prepare("SELECT id FROM clientes WHERE email=:email AND loja_id=:loja_id LIMIT 1");
    $stmtCheck->execute([':email' => $email, ':loja_id' => $loja_id]);
    if ($stmtCheck->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['erro' => 'E-mail já cadastrado nessa loja']);
        exit;
    }

    // Insere o cliente no banco
    $stmt = $pdo->prepare("
        INSERT INTO clientes 
        (loja_id, nome, cpf, telefone, email, senha, data_nascimento, status, email_verificado)
        VALUES 
        (:loja_id, :nome, :cpf, :telefone, :email, :senha, :data_nascimento, 'ativo', 0)
    ");

    $stmt->execute([
        ':loja_id' => $loja_id,
        ':nome' => $nome,
        ':cpf' => $cpf,
        ':telefone' => $telefone,
        ':email' => $email,
        ':senha' => password_hash($senha, PASSWORD_DEFAULT),
        ':data_nascimento' => $data_nascimento ?: null
    ]);

    echo json_encode(['sucesso' => true]);

} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro no servidor: '.$e->getMessage()]);
}
