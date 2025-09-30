<?php
session_start();
require '../includes/conexao.php';

// Verifica se o admin está logado
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Buscar dados do admin
$stmt = $pdo->prepare("SELECT * FROM administradores WHERE id=:id");
$stmt->execute([':id'=>$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrou admin
if(!$admin){
    session_destroy();
    header("Location: admin_login.php?erro=admin_nao_encontrado");
    exit;
}

// Buscar dados da loja vinculada ao admin
$loja_id = $admin['loja_id'] ?? null;
$loja = null;
if($loja_id){
    $stmt = $pdo->prepare("SELECT * FROM lojas WHERE id=:id");
    $stmt->execute([':id'=>$loja_id]);
    $loja = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Cria loja padrão se não existir
if(!$loja){
    $stmt = $pdo->prepare("INSERT INTO lojas (nome, status) VALUES (:nome, :status)");
    $stmt->execute([':nome'=>'Minha Primeira Loja', ':status'=>'aberta']);
    $loja_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("UPDATE administradores SET loja_id=:loja_id WHERE id=:admin_id");
    $stmt->execute([':loja_id'=>$loja_id, ':admin_id'=>$admin_id]);
    $admin['loja_id'] = $loja_id;
    $stmt = $pdo->prepare("SELECT * FROM lojas WHERE id=:id");
    $stmt->execute([':id'=>$loja_id]);
    $loja = $stmt->fetch(PDO::FETCH_ASSOC);
}

$mensagem = '';
$mensagem_foto = '';
$horarios = !empty($loja['horarios']) ? json_decode($loja['horarios'], true) : [];

// ==========================
// Processar POST
// ==========================
if($_SERVER['REQUEST_METHOD']==='POST'){

    // Atualizar dados do admin
    if(isset($_POST['nome'], $_POST['email'])){
        $stmt = $pdo->prepare("UPDATE administradores SET nome=:nome, email=:email WHERE id=:id");
        $stmt->execute([
            ':nome'=>trim($_POST['nome']),
            ':email'=>trim($_POST['email']),
            ':id'=>$admin_id
        ]);
        $mensagem = "Dados do administrador atualizados!";
        $admin['nome'] = trim($_POST['nome']);
        $admin['email'] = trim($_POST['email']);
    }

    // Alterar foto do admin
    if(isset($_FILES['foto']) && $_FILES['foto']['error']===0){
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid() . '.' . $ext;
        $dir = '../uploads/admin/';
        if(!is_dir($dir)) mkdir($dir,0755,true);
        move_uploaded_file($_FILES['foto']['tmp_name'], $dir.$novo_nome);

        if(!empty($admin['foto']) && file_exists('../'.$admin['foto'])){
            unlink('../'.$admin['foto']);
        }

        $stmt = $pdo->prepare("UPDATE administradores SET foto=:foto WHERE id=:id");
        $stmt->execute([':foto'=>'uploads/admin/'.$novo_nome, ':id'=>$admin_id]);
        $mensagem_foto = "Foto de perfil atualizada!";
        $admin['foto'] = 'uploads/admin/'.$novo_nome;
    }

    // Remover foto admin
    if(isset($_POST['remover_foto'])){
        if(!empty($admin['foto']) && file_exists('../'.$admin['foto'])){
            unlink('../'.$admin['foto']);
        }
        $stmt = $pdo->prepare("UPDATE administradores SET foto=NULL WHERE id=:id");
        $stmt->execute([':id'=>$admin_id]);
        $mensagem_foto = "Foto removida com sucesso!";
        $admin['foto'] = null;
    }

    // Atualizar dados da loja
    if(isset($_POST['status_loja'])){
        $status = $_POST['status_loja'];
        $nome_loja = trim($_POST['nome_loja']);
        $endereco = trim($_POST['endereco']);
        $estado = trim($_POST['estado']);
        $cidade = trim($_POST['cidade']);
        $bairro = trim($_POST['bairro']);
        $cep = trim($_POST['cep']);
        $mensagem_promocional = trim($_POST['mensagem_promocional']);

        // Upload logo
        if(isset($_FILES['logo']) && $_FILES['logo']['error']===0){
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $novo_logo = uniqid().'.'.$ext;
            $dir = '../uploads/logos/';
            if(!is_dir($dir)) mkdir($dir,0755,true);
            move_uploaded_file($_FILES['logo']['tmp_name'], $dir.$novo_logo);
            if(!empty($loja['logo']) && file_exists('../'.$loja['logo'])){
                unlink('../'.$loja['logo']);
            }
            $loja['logo'] = 'uploads/logos/'.$novo_logo;
        }

        // Horários
        $dias = ['segunda','terca','quarta','quinta','sexta','sabado','domingo'];
        $horarios_corrigidos = [];
        foreach($dias as $dia){
            $entrada = $_POST['horarios'][$dia]['entrada'] ?? '';
            $saida = $_POST['horarios'][$dia]['saida'] ?? '';
            $horarios_corrigidos[$dia] = ['entrada'=>$entrada, 'saida'=>$saida];
        }
        $horarios_json = json_encode($horarios_corrigidos);

        $stmt = $pdo->prepare("UPDATE lojas SET status=:status, nome=:nome, endereco=:endereco, estado=:estado, cidade=:cidade, bairro=:bairro, cep=:cep, logo=:logo, mensagem=:mensagem, horarios=:horarios WHERE id=:id");
        $stmt->execute([
            ':status'=>$status,
            ':nome'=>$nome_loja,
            ':endereco'=>$endereco,
            ':estado'=>$estado,
            ':cidade'=>$cidade,
            ':bairro'=>$bairro,
            ':cep'=>$cep,
            ':logo'=>$loja['logo'] ?? null,
            ':mensagem'=>$mensagem_promocional,
            ':horarios'=>$horarios_json,
            ':id'=>$loja_id
        ]);

        $loja = array_merge($loja, [
            'status'=>$status,
            'nome'=>$nome_loja,
            'endereco'=>$endereco,
            'estado'=>$estado,
            'cidade'=>$cidade,
            'bairro'=>$bairro,
            'cep'=>$cep,
            'mensagem'=>$mensagem_promocional,
            'horarios'=>$horarios_json
        ]);
        $horarios = $horarios_corrigidos;
        $mensagem = "Configurações da loja atualizadas!";
    }
}

// Navbar
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Perfil - <?= htmlspecialchars($admin['nome'] ?? 'Administrador') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.profile-img { width:150px; height:150px; object-fit:cover; border-radius:50%; border:3px solid #dc3545; }
.logo-img { width:120px; height:120px; object-fit:cover; border:2px solid #0d6efd; border-radius:10px; }
.input-horario { max-width:100px; display:inline-block; }
</style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow-sm p-4">
        <h3 class="mb-4 text-center">Perfil do Administrador</h3>

        <?php if($mensagem): ?><div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
        <?php if($mensagem_foto): ?><div class="alert alert-success"><?= htmlspecialchars($mensagem_foto) ?></div><?php endif; ?>

        <!-- ADMIN -->
        <div class="text-center mb-4">
          <img src="<?= !empty($admin['foto']) ? '../'.htmlspecialchars($admin['foto']) : 'https://via.placeholder.com/150?text=Sem+Foto' ?>" class="profile-img me-3" alt="Foto de perfil">
          <form method="POST" enctype="multipart/form-data" class="mb-2">
              <input type="file" name="foto" class="form-control mb-2">
              <button type="submit" class="btn btn-success w-100">Alterar Foto</button>
          </form>
          <?php if(!empty($admin['foto'])): ?>
          <form method="POST">
            <input type="hidden" name="remover_foto" value="1">
            <button type="submit" class="btn btn-danger w-100 mt-2">Remover Foto</button>
          </form>
          <?php endif; ?>
        </div>

        <!-- ADMIN INFO -->
        <form method="POST" class="mb-4">
          <div class="mb-3"><label>Nome</label><input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($admin['nome'] ?? '') ?>" required></div>
          <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required></div>
        </form>

        <hr>

        <!-- LOJA -->
        <h4 class="mb-3">Configurações da Loja</h4>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3"><label>Nome da Loja</label><input type="text" name="nome_loja" class="form-control" value="<?= htmlspecialchars($loja['nome'] ?? '') ?>" required></div>
            <div class="mb-3"><label>Endereço</label><input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($loja['endereco'] ?? '') ?>"></div>
            <div class="row">
              <div class="col-md-4 mb-3"><label>Estado</label><input type="text" name="estado" class="form-control" value="<?= htmlspecialchars($loja['estado'] ?? '') ?>"></div>
              <div class="col-md-4 mb-3"><label>Cidade</label><input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($loja['cidade'] ?? '') ?>"></div>
              <div class="col-md-4 mb-3"><label>Bairro</label><input type="text" name="bairro" class="form-control" value="<?= htmlspecialchars($loja['bairro'] ?? '') ?>"></div>
            </div>
            <div class="mb-3"><label>CEP</label><input type="text" name="cep" class="form-control" value="<?= htmlspecialchars($loja['cep'] ?? '') ?>"></div>
            <div class="mb-3"><label>Mensagem Promocional</label><input type="text" name="mensagem_promocional" class="form-control" value="<?= htmlspecialchars($loja['mensagem'] ?? '') ?>"></div>
            <div class="mb-3"><label>Logo da Loja</label><br>
                <?php if(!empty($loja['logo'])): ?><img src="<?= '../'.htmlspecialchars($loja['logo']) ?>" class="logo-img mb-2" alt="Logo da Loja"><br><?php endif; ?>
                <input type="file" name="logo" class="form-control">
            </div>
            <div class="mb-3"><label>Status da Loja</label>
                <select name="status_loja" class="form-control" required>
                    <option value="aberta" <?= ($loja['status']??'')=='aberta'?'selected':'' ?>>Aberta</option>
                    <option value="fechada" <?= ($loja['status']??'')=='fechada'?'selected':'' ?>>Fechada</option>
                </select>
            </div>

            <h5>Horários por Dia</h5>
            <?php foreach(['segunda','terca','quarta','quinta','sexta','sabado','domingo'] as $dia):
                $entrada = $horarios[$dia]['entrada']??'';
                $saida   = $horarios[$dia]['saida']??'';
            ?>
            <div class="row mb-2 align-items-center">
              <div class="col-md-2"><?= ucfirst($dia) ?></div>
              <div class="col-md-5"><input type="time" name="horarios[<?= $dia ?>][entrada]" class="form-control input-horario" value="<?= $entrada ?>"></div>
              <div class="col-md-5"><input type="time" name="horarios[<?= $dia ?>][saida]" class="form-control input-horario" value="<?= $saida ?>"></div>
            </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-success mt-3 w-100">Salvar Configurações da Loja</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
