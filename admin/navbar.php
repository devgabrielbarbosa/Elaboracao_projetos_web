<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/conexao.php';

// Pegar o ID do admin logado
$admin_id = $_SESSION['admin_id'] ?? null;

$nome_admin = 'Administrador';
$foto_admin = 'https://via.placeholder.com/150?text=Sem+Foto';

if ($admin_id) {
    $stmt = $pdo->prepare("SELECT nome, foto FROM administradores WHERE id = :id");
    $stmt->execute([':id' => $admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $nome_admin = $admin['nome'] ?? 'Administrador';
        if (!empty($admin['foto'])) {
            // Caminho salvo no banco (ex: "uploads/foto.jpg")
            $foto_admin = '../' . $admin['foto'];
        }
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-danger mb-4">
  <div class="container-fluid">
    <img src="<?= htmlspecialchars($foto_admin) ?>" 
         class="profile-img me-3" 
         alt="Foto de perfil" 
         style="width:50px; height:50px; object-fit:cover; border-radius:50%;">

    <a class="navbar-brand fw-bold" href="dashboard.php"><?= htmlspecialchars($nome_admin) ?></a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLinks" aria-controls="navbarLinks" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarLinks">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 text-light">
        <li class="nav-item"><a class="nav-link text-light" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="pedidos.php">Pedidos</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="produtos.php">Produtos</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="promocoes.php">Promoções</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="taxa_entrega.php">Taxa de Entrega</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="forma_pagamento.php">Formas de Pagamento</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="perfil_admin.php">Perfil (<?= htmlspecialchars($nome_admin) ?>)</a></li>
        <li class="nav-item"><a class="nav-link text-light text-light fw-bold" href="logout.php">Sair</a></li>
      </ul>
    </div>
  </div>
</nav>
