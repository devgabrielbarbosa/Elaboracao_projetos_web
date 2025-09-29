<?php
session_start();
require 'includes/conexao.php'; // Conexão PDO

if(!isset($_SESSION['cliente_id'])){
    header("Location: login.php");
    exit;
}

$cliente_id = $_SESSION['cliente_id'];
$mensagem = '';

// ===============================
// Buscar dados do cliente
// ===============================
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = :id");
$stmt->execute([':id' => $cliente_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

$cliente_foto = $cliente['foto_perfil'] ?? 'img/default-avatar.png';
$cliente_nome = $cliente['nome'] ?? '';
$cliente_email = $cliente['email'] ?? '';
$cliente_telefone = $cliente['telefone'] ?? '';

// ===============================
// Editar dados do cliente
// ===============================
if(isset($_POST['acao']) && $_POST['acao'] === 'editar_cliente'){
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $senha = $_POST['senha'] ?? null;
    $foto = $cliente_foto;

    // Upload da foto
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] === 0){
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid().'.'.$ext;
        $dir = 'uploads/';
        if(!is_dir($dir)) mkdir($dir, 0755);
        move_uploaded_file($_FILES['foto']['tmp_name'], $dir.$novo_nome);
        $foto = $dir.$novo_nome;
    }

    // Atualizar cliente
    if($senha){
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE clientes SET nome=:nome, telefone=:telefone, senha=:senha, foto_perfil=:foto WHERE id=:id");
        $stmt->execute([
            ':nome'=>$nome,
            ':telefone'=>$telefone,
            ':senha'=>$senha_hash,
            ':foto'=>$foto,
            ':id'=>$cliente_id
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE clientes SET nome=:nome, telefone=:telefone, foto_perfil=:foto WHERE id=:id");
        $stmt->execute([
            ':nome'=>$nome,
            ':telefone'=>$telefone,
            ':foto'=>$foto,
            ':id'=>$cliente_id
        ]);
    }
    $mensagem = "<div class='alert alert-success'>Perfil atualizado com sucesso!</div>";
}

// ===============================
// Endereços
// ===============================

// Adicionar endereço
if(isset($_POST['acao']) && $_POST['acao'] === 'adicionar_endereco'){
    $logradouro = $_POST['logradouro'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];
    $principal = isset($_POST['principal']) ? 1 : 0;

    if($principal){
        // Remove principal antigo
        $pdo->prepare("UPDATE enderecos SET principal=0 WHERE cliente_id=:cliente_id")->execute([':cliente_id'=>$cliente_id]);
    }

    $stmt = $pdo->prepare("INSERT INTO enderecos (cliente_id, logradouro, numero, bairro, cidade, estado, cep, principal) VALUES (:cliente_id,:logradouro,:numero,:bairro,:cidade,:estado,:cep,:principal)");
    $stmt->execute([
        ':cliente_id'=>$cliente_id,
        ':logradouro'=>$logradouro,
        ':numero'=>$numero,
        ':bairro'=>$bairro,
        ':cidade'=>$cidade,
        ':estado'=>$estado,
        ':cep'=>$cep,
        ':principal'=>$principal
    ]);
    $mensagem = "<div class='alert alert-success'>Endereço adicionado com sucesso!</div>";
}

// Editar endereço
if(isset($_POST['acao']) && $_POST['acao'] === 'editar_endereco' && isset($_POST['id'])){
    $id = (int)$_POST['id'];
    $logradouro = $_POST['logradouro'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];
    $principal = isset($_POST['principal']) ? 1 : 0;

    if($principal){
        $pdo->prepare("UPDATE enderecos SET principal=0 WHERE cliente_id=:cliente_id")->execute([':cliente_id'=>$cliente_id]);
    }

    $stmt = $pdo->prepare("UPDATE enderecos SET logradouro=:logradouro, numero=:numero, bairro=:bairro, cidade=:cidade, estado=:estado, cep=:cep, principal=:principal WHERE id=:id AND cliente_id=:cliente_id");
    $stmt->execute([
        ':logradouro'=>$logradouro,
        ':numero'=>$numero,
        ':bairro'=>$bairro,
        ':cidade'=>$cidade,
        ':estado'=>$estado,
        ':cep'=>$cep,
        ':principal'=>$principal,
        ':id'=>$id,
        ':cliente_id'=>$cliente_id
    ]);
    $mensagem = "<div class='alert alert-success'>Endereço atualizado com sucesso!</div>";
}

// Deletar endereço
if(isset($_GET['acao']) && $_GET['acao'] === 'deletar_endereco' && isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM enderecos WHERE id=:id AND cliente_id=:cliente_id");
    $stmt->execute([':id'=>$id, ':cliente_id'=>$cliente_id]);
    $mensagem = "<div class='alert alert-success'>Endereço deletado!</div>";
}

// Listar endereços
$enderecos = $pdo->prepare("SELECT * FROM enderecos WHERE cliente_id=:cliente_id ORDER BY principal DESC, id ASC");
$enderecos->execute([':cliente_id'=>$cliente_id]);
$enderecos = $enderecos->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Perfil - Cliente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .profile-img { width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid #fff; }
  .btn-brand { background-color:#7B1E3D; color:#fff; }
  .btn-brand:hover { background-color:#a22b52; }
</style>
</head>
<body class="bg-light">

<div class="container my-5">
<h3>Meu Perfil</h3>
<?php if($mensagem) echo $mensagem; ?>

<!-- Card Perfil -->
<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <div class="d-flex align-items-center mb-3">
      <img src="<?php echo htmlspecialchars($cliente_foto); ?>" class="profile-img me-3" alt="Foto de perfil">
      <form method="POST" enctype="multipart/form-data" class="flex-grow-1">
        <input type="hidden" name="acao" value="editar_cliente">
        <div class="mb-2">
          <input type="text" name="nome" class="form-control" placeholder="Nome" value="<?php echo htmlspecialchars($cliente_nome); ?>" required>
        </div>
        <div class="mb-2">
          <input type="text" name="telefone" class="form-control" placeholder="Telefone" value="<?php echo htmlspecialchars($cliente_telefone); ?>" required>
        </div>
        <div class="mb-2">
          <input type="password" name="senha" class="form-control" placeholder="Nova senha (opcional)">
        </div>
        <div class="mb-2">
          <input type="file" name="foto" class="form-control">
        </div>
        <button type="submit" class="btn btn-brand w-100">Atualizar Perfil</button>
      </form>
    </div>
  </div>
</div>

<!-- Endereços -->
<div class="card mb-4 shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Meus Endereços</span>
    <!-- Botão abrir modal adicionar -->
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalAddEndereco">Adicionar Endereço</button>
  </div>
  <div class="card-body">
    <?php if(empty($enderecos)): ?>
      <div class="alert alert-info">Nenhum endereço cadastrado.</div>
    <?php else: ?>
      <?php foreach($enderecos as $end): ?>
      <div class="border rounded p-3 mb-2">
        <strong><?php echo htmlspecialchars($end['logradouro'].', '.$end['numero']); ?></strong>
        <div><?php echo htmlspecialchars($end['bairro'].' - '.$end['cidade'].'/'.$end['estado'].' CEP: '.$end['cep']); ?></div>
        <div>
          <span class="badge bg-primary"><?php echo $end['principal'] ? 'Principal' : ''; ?></span>
          <a href="perfil.php?acao=deletar_endereco&id=<?php echo $end['id']; ?>" class="btn btn-sm btn-danger ms-2" onclick="return confirm('Deseja realmente deletar?')">Excluir</a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</div>

<!-- Modal Adicionar Endereço -->
<div class="modal fade" id="modalAddEndereco" tabindex="-1" aria-labelledby="modalAddEnderecoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="acao" value="adicionar_endereco">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddEnderecoLabel">Adicionar Endereço</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2"><input type="text" name="logradouro" class="form-control" placeholder="Logradouro" required></div>
          <div class="mb-2"><input type="text" name="numero" class="form-control" placeholder="Número" required></div>
          <div class="mb-2"><input type="text" name="bairro" class="form-control" placeholder="Bairro" required></div>
          <div class="mb-2"><input type="text" name="cidade" class="form-control" placeholder="Cidade" required></div>
          <div class="mb-2"><input type="text" name="estado" class="form-control" placeholder="Estado" required></div>
          <div class="mb-2"><input type="text" name="cep" class="form-control" placeholder="CEP" required></div>
          <div class="form-check">
            <input type="checkbox" class="form-check-input" name="principal" id="principal">
            <label for="principal" class="form-check-label">Principal</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Adicionar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
