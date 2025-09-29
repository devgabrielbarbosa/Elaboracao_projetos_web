<?php
session_start();
require 'includes/conexao.php'; // Conexão PDO

// ===============================
// 1️⃣ Verifica login do cliente
// ===============================
if(!isset($_SESSION['cliente_id'], $_SESSION['loja_id'])){
    header("Location: login.php");
    exit;
}

$cliente_id = $_SESSION['cliente_id'];
$loja_id    = $_SESSION['loja_id'];
$nome_cliente = $_SESSION['cliente_nome'] ?? 'Cliente';

// ===============================
// Caminho base das imagens
// ===============================
$uploadDir = '/delivery_lanches/uploads/';

// ===============================
// 2️⃣ Buscar dados do cliente
// ===============================
$stmt = $pdo->prepare("SELECT foto_perfil, telefone, nome FROM clientes WHERE id=:id AND loja_id=:loja_id");
$stmt->execute([':id'=>$cliente_id, ':loja_id'=>$loja_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

$cliente_foto = !empty($cliente['foto_perfil']) && file_exists(__DIR__ . $uploadDir . $cliente['foto_perfil'])
    ? $uploadDir . $cliente['foto_perfil']
    : 'img/default-avatar.png';

$cliente_telefone = $cliente['telefone'] ?? '';
$cliente_nome = $cliente['nome'] ?? $nome_cliente;

// ===============================
// 3️⃣ Dados da loja/empresa
// ===============================
$stmt = $pdo->prepare("SELECT * FROM lojas WHERE id=:loja_id LIMIT 1");
$stmt->execute([':loja_id'=>$loja_id]);
$loja = $stmt->fetch(PDO::FETCH_ASSOC);

$empresa_telefone = $loja['telefone'] ?? '5511999999999';
$empresa_whatsapp = $empresa_telefone;
$empresa_email = $loja['email'] ?? 'contato@delivery.com';
$loja_nome = $loja['nome'] ?? 'Minha Loja';

// ===============================
// 4️⃣ Inicializa carrinho
// ===============================
if(!isset($_SESSION['carrinho']) || !is_array($_SESSION['carrinho'])){
    $_SESSION['carrinho'] = [];
}

// ===============================
// 5️⃣ Adicionar produto ao carrinho
// ===============================
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar'], $_POST['produto_id'], $_POST['nome'], $_POST['preco'])){
    $id = (int)$_POST['produto_id'];
    $nome = trim($_POST['nome']);
    $preco = (float)$_POST['preco'];

    if(isset($_SESSION['carrinho'][$id])){
        $_SESSION['carrinho'][$id]['quantidade'] += 1;
    } else {
        $_SESSION['carrinho'][$id] = [
            'nome' => $nome,
            'preco' => $preco,
            'quantidade' => 1
        ];
    }

    header('Location: '.$_SERVER['REQUEST_URI']);
    exit;
}

// ===============================
// 6️⃣ Buscar produtos da loja
// ===============================
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE disponivel=1 AND loja_id=:loja_id ORDER BY nome ASC");
$stmt->execute([':loja_id'=>$loja_id]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajustar imagens dos produtos
foreach ($produtos as &$p) {
    $p['imagem'] = !empty($p['imagem']) && file_exists(__DIR__ . $uploadDir . $p['imagem'])
        ? $uploadDir . $p['imagem']
        : 'img/product-placeholder.png';
}
unset($p);

// ===============================
// 7️⃣ Buscar promoções da loja
// ===============================
$hoje = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT * FROM promocoes 
    WHERE ativo = 1 
      AND data_inicio <= :hoje_inicio 
      AND data_fim >= :hoje_fim
      AND loja_id = :loja_id
    ORDER BY data_criacao DESC
");

$stmt->execute([
    ':hoje_inicio' => $hoje,
    ':hoje_fim'    => $hoje,
    ':loja_id'     => $loja_id
]);

$promocoes_ativas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajustar imagens das promoções
foreach ($promocoes_ativas as &$promo) {
    $promo['imagem'] = !empty($promo['imagem']) && file_exists(__DIR__ . $uploadDir . $promo['imagem'])
        ? $uploadDir . $promo['imagem']
        : 'img/banner-placeholder.png';
}
unset($promo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($loja_nome) ?> - Delivery</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.btn-brand { background-color: #7B1E3D; color: #fff; transition:0.2s; }
.btn-brand:hover { background-color: #a22b52; }
.product-img { height:180px; object-fit:cover; border-radius:8px; }
.profile-img { width:40px; height:40px; object-fit:cover; border-radius:50%; border:2px solid #fff; }
.carousel-banner { height:200px; object-fit:cover; border-radius:8px; }
.card-product { transition: transform 0.2s; border-radius:8px; }
.card-product:hover { transform: scale(1.02); box-shadow:0 5px 15px rgba(0,0,0,0.2); }
.carrinho-fixo { position: fixed; top: 80px; right: 20px; width: 280px; max-height: 70vh; overflow-y: auto; z-index: 999; background-color: #fff; padding: 15px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-dark" href="#">
      <img src="img/logo-small.png" alt="logo" style="height:34px; margin-right:8px;">
      <?= htmlspecialchars($loja_nome) ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link d-flex align-items-center text-dark p-2" href="tel:<?= $empresa_telefone ?>"><i class="fas fa-phone fa-lg me-2"></i><span class="d-none d-md-inline"><?= substr($empresa_telefone,-9) ?></span></a></li>
        <li class="nav-item"><a class="nav-link p-2" href="https://wa.me/<?= $empresa_whatsapp ?>" target="_blank"><i class="fab fa-whatsapp fa-lg text-success"></i></a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center p-0" href="#" data-bs-toggle="dropdown">
            <img src="<?= $cliente_foto ?>" class="profile-img" alt="perfil">
            <span class="ms-2 d-none d-md-inline fw-medium"><?= htmlspecialchars($nome_cliente) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i>Meu Perfil</a></li>
            <li><a class="dropdown-item" href="meus_pedidos.php"><i class="fas fa-list-check me-2"></i>Meus Pedidos</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-right-from-bracket me-2"></i>Sair</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- CARROSSEL DE PROMOÇÕES -->
<div class="container my-4">
<?php if(!empty($promocoes_ativas)): ?>
  <div id="carouselPromocoes" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      <?php foreach($promocoes_ativas as $idx=>$promo): ?>
        <div class="carousel-item <?= $idx===0?'active':'' ?>">
          <img src="<?= htmlspecialchars($promo['imagem']) ?>" class="d-block w-100 carousel-banner" alt="<?= htmlspecialchars($promo['descricao']) ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <?php if(count($promocoes_ativas)>1): ?>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselPromocoes" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselPromocoes" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="alert alert-info text-center">Nenhuma promoção ativa no momento.</div>
<?php endif; ?>
</div>

<!-- PRODUTOS -->
<div class="container my-4">
  <div class="row g-3">
    <?php if(empty($produtos)): ?>
      <div class="col-12"><div class="alert alert-info">Nenhum produto disponível nesta loja.</div></div>
    <?php endif; ?>
    <?php foreach($produtos as $p): ?>
      <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100 shadow-sm card-product">
       <img src="<?= htmlspecialchars($uploadDir . $p['imagem']) ?>" class="product-img" alt="<?= htmlspecialchars($p['nome']) ?>">

          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1"><?= htmlspecialchars($p['nome']) ?></h5>
            <p class="card-text text-muted small mb-2" style="flex:1"><?= htmlspecialchars($p['descricao']) ?></p>
            <div class="d-flex align-items-center justify-content-between mt-2">
              <strong class="fs-6">R$ <?= number_format($p['preco'],2,",",".") ?></strong>
              <form method="POST" class="ms-2">
                <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="nome" value="<?= htmlspecialchars($p['nome']) ?>">
                <input type="hidden" name="preco" value="<?= (float)$p['preco'] ?>">
                <button type="submit" name="adicionar" class="btn btn-brand btn-sm"><i class="fas fa-cart-plus"></i> Adicionar</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- CARRINHO FIXO -->
<div class="carrinho-fixo">
  <h5><i class="fas fa-shopping-cart"></i> Carrinho</h5>
  <?php if(empty($_SESSION['carrinho'])): ?>
    <p class="text-muted">Seu carrinho está vazio.</p>
  <?php else: ?>
    <ul class="list-group mb-2">
      <?php $total=0; foreach($_SESSION['carrinho'] as $id=>$item): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <?= htmlspecialchars($item['nome']) ?> x <?= $item['quantidade'] ?>
          <span>R$ <?= number_format($item['preco']*$item['quantidade'],2,",",".") ?></span>
        </li>
        <?php $total += $item['preco']*$item['quantidade']; endforeach; ?>
    </ul>
    <div class="d-flex justify-content-between fw-bold mb-2">
      Total: <span>R$ <?= number_format($total,2,",",".") ?></span>
    </div>
    <a href="checkout.php" class="btn btn-success w-100">Finalizar Compra</a>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
