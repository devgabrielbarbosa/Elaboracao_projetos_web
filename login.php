<?php
session_start();
require 'includes/conexao.php'; // $pdo

$mensagem = '';

// =====================
// 1️⃣ Identificar a loja
// =====================
if (isset($_SESSION['loja_id']) && $_SESSION['loja_id'] > 0) {
    $loja_id = $_SESSION['loja_id'];
} elseif (isset($_GET['loja_id']) && $_GET['loja_id'] > 0) {
    $loja_id = (int)$_GET['loja_id'];
    $_SESSION['loja_id'] = $loja_id;
} else {
    $loja_id = 1; // padrão
    $_SESSION['loja_id'] = $loja_id;
}

// =====================
// 2️⃣ Buscar dados da loja
// =====================
$stmt = $pdo->prepare("SELECT * FROM lojas WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $loja_id]);
$loja = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrou, cria loja padrão
if (!$loja) {
    $stmt = $pdo->prepare("INSERT INTO lojas (nome, status) VALUES (:nome, :status)");
    $stmt->execute([':nome' => 'Minha Primeira Loja', ':status' => 'aberta']);
    $loja_id = $pdo->lastInsertId();
    $_SESSION['loja_id'] = $loja_id;

    $stmt = $pdo->prepare("SELECT * FROM lojas WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $loja_id]);
    $loja = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Definir defaults caso algum campo esteja vazio
$loja = array_merge([
    'nome' => 'Loja não encontrada',
    'logo' => 'https://via.placeholder.com/150?text=Sem+Logo',
    'endereco' => '',
    'cidade' => '',
    'cep'=> '',
    'estado' => '',
    'mensagem' => ''
], $loja);

// =====================
// 3️⃣ Buscar dados do admin da loja
// =====================
$stmt = $pdo->prepare("SELECT nome, foto FROM administradores WHERE loja_id = :loja_id ORDER BY id ASC LIMIT 1");
$stmt->execute([':loja_id' => $loja_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$nome_admin = $admin['nome'] ?? 'Administrador';
$foto_admin = !empty($admin['foto']) ? '../'.$admin['foto'] : 'https://via.placeholder.com/150?text=Sem+Foto';

// =====================
// 4️⃣ Processar login do cliente
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email']) && !empty($_POST['senha'])) {
    $login = trim($_POST['email']);
    $senha_digitada = $_POST['senha'];

    // Detectar se é CPF (apenas números) ou e-mail
    $cpf = preg_replace('/\D/', '', $login);
    $isCPF = strlen($cpf) === 11;

    try {
        if ($isCPF) {
            $stmt = $pdo->prepare("SELECT * FROM clientes WHERE cpf = :cpf AND loja_id = :loja_id LIMIT 1");
            $stmt->execute([':cpf' => $cpf, ':loja_id' => $loja_id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = :email AND loja_id = :loja_id LIMIT 1");
            $stmt->execute([':email' => $login, ':loja_id' => $loja_id]);
        }

        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            if ($senha_digitada === $cliente['senha']) { // futuramente use password_verify()
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nome'] = $cliente['nome'];
                $_SESSION['loja_id'] = $loja_id;
                header("Location: index.php");
                exit;
            } else {
                $mensagem = "<div class='alert alert-danger text-center'>Senha incorreta.</div>";
            }
        } else {
            $mensagem = "<div class='alert alert-warning text-center'>Cliente não encontrado. Cadastre-se primeiro.</div>";
        }
    } catch(PDOException $e) {
        $mensagem = "<div class='alert alert-danger text-center'>Erro interno. Entre em contato.</div>";
        // Para debug temporário:
        // $mensagem .= "<br>Error: " . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - <?= htmlspecialchars($loja['nome']) ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
body { background-color: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.btn-brand { background-color: #800000; color: #fff; font-weight: 600; }
.btn-brand:hover { background-color: #a22b52; color:#fff; }
.login-card { border-radius: 12px; }
.aside-panel { background-color: #800000; border-top-right-radius: 12px; border-bottom-right-radius: 12px; color: #fff; }
.logo-circle img { width: 80px; height: 80px; border-radius: 50%; border: 3px solid #fff; object-fit:cover; }
.profile-circle img { width: 50px; height: 50px; border-radius: 50%; border: 2px solid #fff; object-fit:cover; }
</style>
</head>
<body>

<div class="container py-5">
  <div class="d-flex justify-content-center align-items-start">
    <div class="card login-card shadow-sm w-100" style="max-width: 900px;">
      <div class="row g-0">

        <!-- Form de login -->
        <div class="col-12 col-md-7 p-4 p-md-5">
          <h3 class="mb-1 text-center text-md-start">Bem-vindo(a) de volta!</h3>
          <p class="text-muted small mb-4 text-center text-md-start">Faça login para acessar sua conta.</p>

          <?php if($mensagem) echo $mensagem; ?>

          <form method="POST" action="login.php">
            <input type="hidden" name="loja_id" value="<?= htmlspecialchars($loja_id) ?>">
            <div class="mb-3">
              <label for="email" class="form-label">E-mail ou CPF</label>
              <input type="text" id="email" name="email" class="form-control" placeholder="Digite seu e-mail ou CPF" required>
            </div>
            <div class="mb-3">
              <label for="senha" class="form-label">Senha</label>
              <div class="input-group">
                <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha" required>
                <button class="btn btn-outline-secondary" type="button" id="btn-eye">
                  <i class="fa-regular fa-eye" id="eye-icon"></i>
                </button>
              </div>
            </div>
            <button type="submit" class="btn btn-brand w-100 mt-3">Entrar</button>
            <div class="text-center mt-3">
              <small class="text-muted">Não tem conta? 
              <a href="cadastro.php?loja_id=<?= htmlspecialchars($loja_id) ?>" class="text-danger">Cadastre-se</a></small>
            </div>
          </form>
        </div>

        <!-- Aside com informações da loja e admin -->
        <div class="col-12 col-md-5 aside-panel d-none d-md-flex align-items-center justify-content-center p-4">
          <div class="text-center">
            <div class="logo-circle mb-3">
              <img src="<?= htmlspecialchars($loja['logo']) ?>" alt="Logo da Loja">
            </div>
            <h5 class="mb-1"><?= htmlspecialchars($loja['nome']) ?></h5>
            <p class="small mb-0">
              <?= htmlspecialchars($loja['endereco']) ?><br>
               <?= htmlspecialchars($loja['cep']) ?><br>
              <?= htmlspecialchars($loja['cidade']) ?> <?= htmlspecialchars($loja['estado']) ?>
            </p>
            <?php if (!empty($loja['mensagem'])): ?>
              <p class="text-white fst-italic mt-2">
                <?= htmlspecialchars($loja['mensagem']) ?>
              </p>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const eyeBtn = document.getElementById("btn-eye");
const senhaInput = document.getElementById("senha");
eyeBtn.addEventListener("click", () => {
    const icon = document.getElementById("eye-icon");
    if (senhaInput.type === "password") {
        senhaInput.type = "text";
        icon.classList.replace("fa-eye","fa-eye-slash");
    } else {
        senhaInput.type = "password";
        icon.classList.replace("fa-eye-slash","fa-eye");
    }
});
</script>

</body>
</html>
