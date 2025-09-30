<?php
// ===============================
// Navbar do Cliente
// ===============================

// Inicia sessão se ainda não tiver
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona se o cliente não estiver logado
if (!isset($_SESSION['cliente_id'], $_SESSION['loja_id'])) {
    header("Location: login.php");
    exit;
}

// Dados do cliente
$cliente_nome = $_SESSION['cliente_nome'] ?? 'Cliente';

// Conexão PDO
require 'includes/conexao.php';

// Buscar dados da loja do cliente
$loja_id = $_SESSION['loja_id'];
$stmt = $pdo->prepare("SELECT nome, logo FROM lojas WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $loja_id]);
$loja = $stmt->fetch(PDO::FETCH_ASSOC);

// Caminho da logo
$logo_loja = !empty($loja['logo']) ? $loja['logo'] : 'img/logo-placeholder.png';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-danger mb-4">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="<?= htmlspecialchars($logo_loja) ?>" alt="<?= htmlspecialchars($loja['nome'] ?? 'Loja') ?>" 
           style="height:40px; width:auto; margin-right:10px;">
      <?= htmlspecialchars($loja['nome'] ?? 'Delivery Lanches') ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCliente" aria-controls="navbarCliente" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarCliente">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
        <li class="nav-item"><a class="nav-link" href="perfil.php">Perfil (<?= htmlspecialchars($cliente_nome) ?>)</a></li>
        <li class="nav-item"><a class="nav-link" href="finalizar_pedido.php">Finalizar Pedido</a></li>
        <li class="nav-item"><a class="nav-link fw-bold text-light" href="logout_cliente.php">Sair</a></li>
      </ul>
    </div>
  </div>
</nav>
