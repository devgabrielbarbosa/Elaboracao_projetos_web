<?php
// admin/dashboard.php
session_start();
require '../includes/conexao.php';

// === Segurança básica ===
if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    die("Acesso negado: sessão de admin ou loja não encontrada.");
}

$admin_id  = $_SESSION['admin_id'];
$nome_admin = $_SESSION['admin_nome'] ?? 'Administrador';
$loja_id   = (int)$_SESSION['loja_id'];
$admin = ['foto' => null];

// opcional: debug rápido (?debug=1)
$show_debug = (isset($_GET['debug']) && $_GET['debug'] === '1');

// buscar dados da loja
$loja = ['nome'=>"Loja #{$loja_id}", 'logo'=>null];
try {
    $stmt = $pdo->prepare("SELECT id, nome, logo FROM lojas WHERE id = :id LIMIT 1");
    $stmt->execute([':id'=>$loja_id]);
    $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
    if($tmp){
        $loja = $tmp;
    }
} catch (PDOException $e) {}

// === Resumos por loja ===
function fetchColumnSafe($pdo, $sql, $params, $default=0){
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return $default;
    }
}

// Totais de pedidos
$totalFaturamento = fetchColumnSafe(
    $pdo,
    "SELECT IFNULL(SUM(total + taxa_entrega),0) FROM pedidos WHERE status='entregue' AND loja_id=:loja_id",
    [':loja_id'=>$loja_id]
);

$pedidosEntregues = (int)fetchColumnSafe(
    $pdo,
    "SELECT COUNT(*) FROM pedidos WHERE status='entregue' AND loja_id=:loja_id",
    [':loja_id'=>$loja_id]
);

$pedidosAndamento = (int)fetchColumnSafe(
    $pdo,
    "SELECT COUNT(*) FROM pedidos WHERE status IN ('pendente','aceito','em_entrega') AND loja_id=:loja_id",
    [':loja_id'=>$loja_id]
);

$pedidosCancelados = (int)fetchColumnSafe(
    $pdo,
    "SELECT COUNT(*) FROM pedidos WHERE status='cancelado' AND loja_id=:loja_id",
    [':loja_id'=>$loja_id]
);

// Totais adicionais
$totalClientes = (int)fetchColumnSafe(
    $pdo,
    "SELECT COUNT(*) FROM clientes WHERE loja_id=:loja_id",
    [':loja_id'=>$loja_id]
);

$totalProdutos = (int)fetchColumnSafe(
    $pdo,
    "SELECT COUNT(*) FROM produtos WHERE loja_id=:loja_id",
    [':loja_id'=>$loja_id]
);

// Últimos pedidos
try {
    $stmt = $pdo->prepare("
        SELECT id, total, taxa_entrega, status, metodo_pagamento, data_criacao 
        FROM pedidos
        WHERE loja_id = :loja_id
        ORDER BY data_criacao DESC
        LIMIT 6
    ");
    $stmt->execute([':loja_id'=>$loja_id]);
    $ultimosPedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ultimosPedidos = [];
}

// Faturamento semanal
try {
    $stmt = $pdo->prepare("
        SELECT DAYNAME(data_criacao) as dia, SUM(total + taxa_entrega) as valor
        FROM pedidos
        WHERE YEARWEEK(data_criacao, 1) = YEARWEEK(CURDATE(), 1)
          AND status='entregue'
          AND loja_id = :loja_id
        GROUP BY dia
    ");
    $stmt->execute([':loja_id'=>$loja_id]);
    $dadosSemana = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dadosSemana = [];
}

// montar labels/valores do gráfico
$dias = ['Monday'=>'Seg','Tuesday'=>'Ter','Wednesday'=>'Qua','Thursday'=>'Qui','Friday'=>'Sex','Saturday'=>'Sáb','Sunday'=>'Dom'];
$labels = [];
$valores = [];
foreach($dias as $en=>$pt){
    $labels[] = $pt;
    $valor = 0;
    foreach($dadosSemana as $linha){
        if($linha['dia'] === $en){
            $valor = (float)$linha['valor'];
            break;
        }
    }
    $valores[] = $valor;
}

// foto do admin
if($admin_id){
    $stmt = $pdo->prepare("SELECT foto FROM administradores WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $admin_id]);
    $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
    if($tmp){
        $admin = $tmp;
    }
}
@include 'navbar.php';
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard - (<?= htmlspecialchars($nome_admin) ?>)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.card-valor { font-size: 1.5rem; font-weight:700; }
.small-muted { color: #6c757d; }
.store-logo { height:36px; object-fit:cover; border-radius:6px; margin-right:8px; }
</style>
</head>
<body class="bg-light">

<div class="container my-4">

<?php if($show_debug): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6>DEBUG (sessão)</h6>
        <pre><?= htmlspecialchars(print_r($_SESSION,true)) ?></pre>
        <h6>DEBUG (dadosSemana)</h6>
        <pre><?= htmlspecialchars(print_r($dadosSemana,true)) ?></pre>
    </div>
</div>
<?php endif; ?>

<!-- Cards resumo -->
<div class="row g-3">

  <!-- Faturamento, pedidos, andamento, cancelados -->
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="small-muted">Faturamento (entregues)</div>
        <div class="card-valor text-success">R$ <?= number_format($totalFaturamento,2,',','.') ?></div>
        <small class="text-muted">Total recebido</small>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="small-muted">Pedidos entregues</div>
        <div class="card-valor text-primary"><?= $pedidosEntregues ?></div>
        <small class="text-muted">Últimos finalizados</small>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="small-muted">Em andamento</div>
        <div class="card-valor text-warning"><?= $pedidosAndamento ?></div>
        <small class="text-muted">Pendentes / Em rota</small>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="small-muted">Cancelados</div>
        <div class="card-valor text-danger"><?= $pedidosCancelados ?></div>
        <small class="text-muted">Total cancelados</small>
      </div>
    </div>
  </div>

<?php
$linkCardapio = "/delivery_lanches/login.php?loja_id={$loja_id}";

?>

<div class="col-md-3">
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="small text-muted">Link do Cardápio</div>
      <div class="card-valor text-info mb-2" style="font-size:1rem; word-break:break-all;">
        <a href="<?= $linkCardapio ?>" target="_blank"><?= $linkCardapio ?></a>
      </div>
      <button class="btn btn-sm btn-outline-primary" onclick="copiarLink()">Copiar Link</button>
      <small class="text-muted d-block mt-2">Copie ou compartilhe com clientes</small>
    </div>
  </div>
</div>


<!-- Segunda linha: clientes/produtos + gráfico + últimos pedidos -->
<div class="row mt-4 g-4">
  <div class="col-lg-4">
    <div class="card shadow-sm p-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Clientes & Produtos</h5>
      </div>
      <div class="row text-center">
        <div class="col-6 border-end">
          <div class="small-muted">Clientes</div>
          <div class="h4"><?= $totalClientes ?></div>
        </div>
        <div class="col-6">
          <div class="small-muted">Produtos</div>
          <div class="h4"><?= $totalProdutos ?></div>
        </div>
      </div>
      <hr>
      <h6 class="mb-2">Últimos pedidos</h6>
      <ul class="list-group list-group-flush">
        <?php if(empty($ultimosPedidos)): ?>
          <li class="list-group-item">Nenhum pedido recente.</li>
        <?php else: ?>
          <?php foreach($ultimosPedidos as $p): ?>
          <li class="list-group-item d-flex justify-content-between align-items-start">
            <div>
              <strong>#<?= htmlspecialchars($p['id']) ?></strong> — R$ <?= number_format($p['total']+$p['taxa_entrega'],2,',','.') ?>
              <div class="small text-muted"><?= htmlspecialchars($p['metodo_pagamento']) ?> — <?= date('d/m H:i', strtotime($p['data_criacao'])) ?></div>
            </div>
            <span class="badge <?= $p['status']=='entregue' ? 'bg-success' : (in_array($p['status'], ['pendente','aceito','em_entrega']) ? 'bg-warning text-dark' : 'bg-danger') ?>">
              <?= htmlspecialchars($p['status']) ?>
            </span>
          </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm p-3">
      <h5>Faturamento semanal</h5>
      <canvas id="graficoFaturamento" height="120"></canvas>
    </div>
  </div>
</div>

</div>

<script>
const ctx = document.getElementById('graficoFaturamento').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Faturamento (R$)',
            data: <?= json_encode($valores) ?>,
            borderColor: 'rgb(220,53,69)',
            backgroundColor: 'rgba(220,53,69,0.2)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
function copiarLink() {
    // Construindo URL completa
    const link = window.location.origin + "<?= $linkCardapio ?>";
    navigator.clipboard.writeText(link)
        .then(() => alert('Link copiado para a área de transferência!'))
        .catch(err => alert('Erro ao copiar link: ' + err));
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
