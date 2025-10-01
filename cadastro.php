<?php
session_start();
require 'includes/conexao.php'; // PDO $pdo

// Pegar o loja_id de GET ou sessão
$loja_id = isset($_GET['loja_id']) ? (int)$_GET['loja_id'] : ($_SESSION['loja_id'] ?? 0);
if ($loja_id > 0) $_SESSION['loja_id'] = $loja_id;

// Inicializa mensagem
$mensagem = '';

// Buscar informações da loja para exibir no lado
try {
    $stmt = $pdo->prepare("SELECT nome, logo, endereco, cidade, estado, mensagem FROM lojas WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $loja_id]);
    $loja = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$loja) {
        $loja = [
            'nome' => 'Loja não encontrada',
            'logo' => 'https://via.placeholder.com/150?text=Sem+Logo',
            'endereco' => '',
            'cidade' => '',
            'estado' => '',
            'mensagem' => ''
        ];
    }
} catch (PDOException $e) {
    $loja = [
        'nome' => 'Loja não encontrada',
        'logo' => 'https://via.placeholder.com/150?text=Sem+Logo',
        'endereco' => '',
        'cidade' => '',
        'estado' => '',
        'mensagem' => ''
    ];
}

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? null;

    $cep = trim($_POST['cep'] ?? '');
    $logradouro = trim($_POST['logradouro'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');

    $foto_perfil = null;
    if (!empty($_FILES['foto_perfil']['name']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid() . "." . $ext;
        $dir = "uploads/";
        if (!is_dir($dir)) mkdir($dir, 0755);
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $dir . $novo_nome)) {
            $foto_perfil = $dir . $novo_nome;
        }
    }


    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE cpf = :cpf AND loja_id = :loja_id");
        $stmt->execute([':cpf' => $cpf, ':loja_id' => $loja_id]);
        if ($stmt->fetchColumn() > 0) {
            $mensagem = "<div class='alert alert-warning text-center'>CPF já cadastrado nesta loja.</div>";
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE email = :email AND loja_id = :loja_id");
            $stmt->execute([':email' => $email, ':loja_id' => $loja_id]);
            if ($stmt->fetchColumn() > 0) {
                $mensagem = "<div class='alert alert-warning text-center'>Email já cadastrado nesta loja.</div>";
            } else {
                $sql = "INSERT INTO clientes
                    (nome, cpf, telefone, email, senha, foto_perfil, data_nascimento, status, email_verificado, data_criacao, loja_id, cep, logradouro, numero, complemento, bairro, cidade, estado)
                    VALUES (:nome, :cpf, :telefone, :email, :senha, :foto_perfil, :data_nascimento, 'ativo', 0, NOW(), :loja_id, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nome' => $nome,
                    ':cpf' => $cpf,
                    ':telefone' => $telefone,
                    ':email' => $email,
                    ':senha' => $senha,
                    ':foto_perfil' => $foto_perfil,
                    ':data_nascimento' => $data_nascimento,
                    ':loja_id' => $loja_id,
                    ':cep' => $cep,
                    ':logradouro' => $logradouro,
                    ':numero' => $numero,
                    ':complemento' => $complemento,
                    ':bairro' => $bairro,
                    ':cidade' => $cidade,
                    ':estado' => $estado
                ]);
                $mensagem = "<div class='alert alert-success text-center'>Cadastro realizado com sucesso!</div>";
            }
        }
    } catch (PDOException $e) {
        $mensagem = "<div class='alert alert-danger text-center'>Erro interno. Entre em contato.</div>";
        // Para debug temporário:
         $mensagem .= "<br>Error: " . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cadastro - <?= htmlspecialchars($loja['nome']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
:root{--vinho:#800000;--vinho-dark:#a00000;}
body { background-color: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.card-cadastro { border-radius: 12px; overflow: hidden; }
.aside-panel { background-color: var(--vinho); color: #fff; }
.aside-panel .logo-circle img { width: 80px; border-radius: 50%; border: 2px solid #fff; }
.btn-brand { background-color: var(--vinho); color: #fff; font-weight: 600; }
.btn-brand:hover { background-color: var(--vinho-dark); color:#fff; }
.form-control:focus { box-shadow: none; border-color: var(--vinho); }
input[type="file"] { padding: .375rem .75rem; }
.form-label { font-weight: 500; }
</style>
</head>
<body>
<main class="main">
<div class="container py-5">
<div class="d-flex justify-content-center">
<div class="card card-cadastro shadow-sm w-100" style="max-width: 980px;">
<div class="row g-0">

<!-- Form cadastro -->
<div class="col-12 col-md-8 p-4 p-md-5">
  <div class="mb-4">
    <h3 class="mb-1">Cadastre-se:</h3>
    <p class="text-muted small mb-0">Preencha os dados abaixo para criar sua conta.</p>
  </div>

  <?php if($mensagem) echo $mensagem; ?>

  <form method="POST" enctype="multipart/form-data" novalidate>
    <h5 class="mb-3">Informações Pessoais:</h5>
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-6">
        <label class="form-label">Nome:</label>
        <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">CPF:</label>
        <input type="text" name="cpf" class="form-control" placeholder="Somente números" required value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Telefone:</label>
        <input type="tel" name="telefone" class="form-control" required value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">E-mail:</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Senha:</label>
        <input type="password" name="senha" class="form-control" required>
      </div> <div class="col-12 col-md-6">
        <label class="form-label">Confirma Senha:</label>
        <input type="password" name="senha" class="form-control" required>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Data de Nascimento:</label>
        <input type="date" name="data_nascimento" class="form-control" value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Foto de Perfil:</label>
        <input type="file" name="foto_perfil" class="form-control">
      </div>
    </div>

    <h5 class="mb-3">Endereço:</h5>
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-4">
        <label class="form-label">CEP:</label>
        <input type="text" name="cep" class="form-control" required value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-8">
        <label class="form-label">Rua:</label>
        <input type="text" name="logradouro" class="form-control" required value="<?= htmlspecialchars($_POST['logradouro'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Número:</label>
        <input type="text" name="numero" class="form-control" required value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-8">
        <label class="form-label">Complemento:</label>
        <input type="text" name="complemento" class="form-control" placeholder="Opcional" value="<?= htmlspecialchars($_POST['complemento'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Bairro:</label>
        <input type="text" name="bairro" class="form-control" required value="<?= htmlspecialchars($_POST['bairro'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Cidade:</label>
        <input type="text" name="cidade" class="form-control" required value="<?= htmlspecialchars($_POST['cidade'] ?? $loja['cidade']) ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Estado (UF):</label>
        <input type="text" name="estado" class="form-control" maxlength="2" required value="<?= htmlspecialchars($_POST['estado'] ?? $loja['estado']) ?>">
      </div>
    </div>

    <div class="d-grid mb-2">
      <button type="submit" class="btn btn-brand btn-lg">Cadastrar Cliente</button>
    </div>
    <div class="text-center">
      <small class="text-muted">Já tem uma conta? 
        <a href="login.php?loja_id=<?= $loja_id ?>" class="text-danger">Faça login</a>
      </small>
    </div>
  </form>
</div>

<!-- Aside com informações da loja -->
<div class="col-4 aside-panel d-none d-md-flex align-items-center justify-content-center p-4">
  <div class="text-center px-3">
    <div class="logo-circle mb-3">
      <img src="<?= htmlspecialchars($loja['logo']) ?>" alt="<?= htmlspecialchars($loja['nome']) ?>" class="img-fluid rounded-circle">
    </div>
    <h5 class="mb-1"><?= htmlspecialchars($loja['nome']) ?></h5>
    <p class="small mb-0"><?= htmlspecialchars($loja['endereco']) ?><br><?= htmlspecialchars($loja['cidade']) ?> <?= htmlspecialchars($loja['estado']) ?></p>
    <?php if (!empty($loja['mensagem'])): ?>
      <p class="text-white fst-italic mt-2"><?= htmlspecialchars($loja['mensagem']) ?></p>
    <?php endif; ?>
  </div>
</div>

</div>
</div>
</div>
</div>
</main>

<footer class="footer py-3 text-center">
  <small class="text-muted">© 2025 <?= htmlspecialchars($loja['nome']) ?>. Todos os direitos reservados.</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
ska jsdbfdsanfasndfkãs
