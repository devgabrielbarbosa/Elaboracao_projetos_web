<?php
session_start();
require '../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    // compara texto puro
    if ($senha === $admin['senha']) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];
        if(isset($admin['loja_id']) && $admin['loja_id'] > 0){
            $_SESSION['loja_id'] = (int)$admin['loja_id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $erro = "Loja não associada a este admin!";
        }
    } else {
        $erro = "E-mail ou senha incorretos!";
    }
} else {
    $erro = "E-mail ou senha incorretos!";
}

}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #ff4d4d, #ff9900);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .card {
      border-radius: 15px;
    }
    .btn-primary {
      background-color: #ff4d4d;
      border: none;
    }
    .btn-primary:hover {
      background-color: #e63939;
    }
  </style>
</head>
<body>
  <div class="card shadow-lg p-4" style="width: 350px;">
    <h3 class="text-center mb-3">🍔 Delivery Login</h3>
    <?php if(!empty($erro)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" class="form-control" placeholder="Digite seu e-mail" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Senha</label>
        <input type="password" name="senha" class="form-control" placeholder="Digite sua senha" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
    <p class="text-center mt-3 mb-0">Não tem conta? <a href="admin_cadastro.php">Cadastre-se</a></p>
  </div>
</body>
</html>
