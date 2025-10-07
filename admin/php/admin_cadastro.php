<?php
session_start();
require '../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_loja    = $_POST['nome_loja'] ?? '';
    $email_admin  = $_POST['email_admin'] ?? '';
    $senha_admin  = $_POST['senha_admin'] ?? '';

    if (empty($nome_loja) || empty($email_admin) || empty($senha_admin)) {
        $erro = "Preencha todos os campos!";
        header("Location: cadastro_admin.html?erro=" . urlencode($erro));
        exit;
    } else {
        $senha_hash = $senha_admin; // se quiser pode hash com password_hash()

        try {
            // 1️⃣ Criar loja
            $stmt = $pdo->prepare("INSERT INTO lojas (nome) VALUES (:nome)");
            $stmt->execute([':nome' => $nome_loja]);
            $loja_id = $pdo->lastInsertId();

            // 2️⃣ Criar admin
            $stmt = $pdo->prepare("
                INSERT INTO administradores (nome, email, senha, nivel, loja_id)
                VALUES (:nome, :email, :senha, 'admin', :loja_id)
            ");
            $stmt->execute([
                ':nome'    => $nome_loja,
                ':email'   => $email_admin,
                ':senha'   => $senha_hash,
                ':loja_id' => $loja_id
            ]);

            $sucesso = "Loja e admin criados com sucesso! Faça login.";
            header("Location: login.html?sucesso=" . urlencode($sucesso));
            exit;

        } catch (PDOException $e) {
            $erro = "Erro ao criar loja/admin: " . $e->getMessage();
            header("Location: cadastro_admin.html?erro=" . urlencode($erro));
            exit;
        }
    }
} else {
    header("Location: login.html");
    exit;
}
