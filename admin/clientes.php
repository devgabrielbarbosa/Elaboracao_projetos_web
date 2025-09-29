<?php
session_start();
require '../includes/conexao.php';
if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

// Listar clientes
$clientes = $pdo->query("SELECT * FROM clientes ORDER BY data_criacao DESC")->fetchAll();
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Clientes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
<h3>Clientes Cadastrados</h3>

<table class="table table-bordered table-striped bg-white">
    <thead class="table-dark">
        <tr>
            <th>Nome</th>
            <th>CPF</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Endereço</th>
            <th>Data Cadastro</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($clientes as $c): ?>
        <tr>
            <td><?php echo htmlspecialchars($c['nome']); ?></td>
            <td><?php echo $c['cpf']; ?></td>
            <td><?php echo htmlspecialchars($c['email']); ?></td>
            <td><?php echo $c['telefone']; ?></td>
            <td>
                <?php
                    echo $c['logradouro'].", ".$c['numero'];
                    if($c['complemento']) echo " - ".$c['complemento'];
                    echo " / ".$c['bairro']." - ".$c['cidade']."/".$c['estado'];
                ?>
            </td>
            <td><?php echo $c['data_criacao']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
</body>
</html>
