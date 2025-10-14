<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_loja    = $_POST['nome_loja'] ?? '';
    $email_admin  = $_POST['email_admin'] ?? '';
    $senha_admin  = $_POST['senha_admin'] ?? '';

    if (empty($nome_loja) || empty($email_admin) || empty($senha_admin)) {
        $erro = "Preencha todos os campos!";
        header("Location: cadastro_admin.html?erro=" . urlencode($erro));
        exit;
    }

    // Hash seguro da senha
    $senha_hash = password_hash($senha_admin, PASSWORD_DEFAULT);

    try {
        // 1️⃣ Criar loja
        $stmt = $pdo->prepare("
            INSERT INTO lojas (nome, status, data_criacao)
            VALUES (:nome, 'fechado', NOW())
        ");
        $stmt->execute([':nome' => $nome_loja]);
        $loja_id = $pdo->lastInsertId();

        // 2️⃣ Criar administrador da loja
        $stmt = $pdo->prepare("
            INSERT INTO administradores (nome, email, senha, nivel, loja_id, data_criacao)
            VALUES (:nome, :email, :senha, 'admin', :loja_id, NOW())
        ");
        $stmt->execute([
            ':nome'    => $nome_loja,
            ':email'   => $email_admin,
            ':senha'   => $senha_hash,
            ':loja_id' => $loja_id
        ]);

        $sucesso = "Loja e administrador criados com sucesso! Faça login.";
        header("Location: login.html?sucesso=" . urlencode($sucesso));
        exit;

    } catch (PDOException $e) {
        $erro = "Erro ao criar loja/admin: " . $e->getMessage();
        header("Location: cadastro_admin.html?erro=" . urlencode($erro));
        exit;
    }
} else {
    header("Location: login.html");
    exit;
}
