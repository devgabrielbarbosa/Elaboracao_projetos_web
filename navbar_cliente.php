<?php
// Verifica sessão do cliente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona se não estiver logado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}

// Informações do cliente
$cliente_nome = $_SESSION['cliente_nome'] ?? 'Cliente';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-danger mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Delivery Lanches</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCliente" aria-controls="navbarCliente" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarCliente">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
        <li class="nav-item"><a class="nav-link" href="perfil.php">Perfil (<?= htmlspecialchars($cliente_nome) ?>)</a></li>
        <li class="nav-item"><a class="nav-link" href="finalizar_pedido.php">Finalizar Pedido</a></li>
        <li class="nav-item"><a class="nav-link text-light fw-bold" href="logout_cliente.php">Sair</a></li>
      </ul>
    </div>
  </div>
</nav>
