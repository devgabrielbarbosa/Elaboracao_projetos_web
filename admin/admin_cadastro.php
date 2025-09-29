<?php
session_start();
require '../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_loja    = $_POST['nome_loja'] ?? '';
    $email_admin  = $_POST['email_admin'] ?? '';
    $senha_admin  = $_POST['senha_admin'] ?? '';

    if (empty($nome_loja) || empty($email_admin) || empty($senha_admin)) {
        $erro = "Preencha todos os campos!";
    } else {
        $senha_hash = $senha_admin ;

        try {
            // 1️⃣ Criar loja
            $stmt = $pdo->prepare("INSERT INTO lojas (nome) VALUES (:nome)");
            $stmt->execute([':nome' => $nome_loja]);
            $loja_id = $pdo->lastInsertId();

            // 2️⃣ Criar admin automaticamente
            $stmt = $pdo->prepare("
                INSERT INTO administradores (nome, email, senha, nivel, loja_id)
                VALUES (:nome, :email, :senha, 'admin', :loja_id)
            ");
            $stmt->execute([
                ':nome'    => $nome_loja, // pode ser outro nome se quiser
                ':email'   => $email_admin,
                ':senha'   => $senha_hash,
                ':loja_id' => $loja_id
            ]);

            $_SESSION['sucesso'] = "Loja e admin criados com sucesso! Faça login.";
            header("Location: admin_login.php");
            exit;

        } catch (PDOException $e) {
            $erro = "Erro ao criar loja/admin: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro Loja/Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #ff9900, #ff4d4d);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
.card { border-radius: 15px; }
.btn-success { background-color: #28a745; border: none; }
.btn-success:hover { background-color: #218838; }
</style>
</head>
<body>
<div class="card shadow-lg p-4" style="width: 400px;">
    <h3 class="text-center mb-3">🍟 Cadastro Loja & Admin</h3>

    <?php if(!empty($erro)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nome da Loja</label>
            <input type="text" name="nome_loja" class="form-control" placeholder="Digite o nome da loja" required>
        </div>
        <div class="mb-3">
            <label class="form-label">E-mail do Admin</label>
            <input type="email" name="email_admin" class="form-control" placeholder="E-mail do admin" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Senha do Admin</label>
            <input type="password" name="senha_admin" class="form-control" placeholder="Senha do admin" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Cadastrar Loja & Admin</button>
    </form>
    <p class="text-center mt-3 mb-0">Já tem conta? <a href="admin_login.php">Faça login</a></p>
</div>
</body>
</html>
