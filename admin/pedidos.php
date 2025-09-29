<?php
session_start();
require '../includes/conexao.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$nome_admin = $_SESSION['admin_nome'] ?? 'Administrador';

// Buscar pedidos do admin logado
$pedidos = $pdo->prepare("
    SELECT p.id, p.cliente_id, p.total, p.taxa_entrega, p.status, p.metodo_pagamento, p.data_criacao, c.nome AS cliente_nome
    FROM pedidos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.admin_id=:admin_id
    ORDER BY p.data_criacao DESC
");
$pedidos->execute([':admin_id' => $admin_id]);
$pedidos = $pedidos->fetchAll(PDO::FETCH_ASSOC);

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pedidos - (<?= htmlspecialchars($nome_admin) ?>)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.pedido-card { border-radius: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
.pedido-card:hover { transform: scale(1.01); }
.pedido-header { font-weight: bold; font-size: 1.1rem; }
.badge-status { font-size: 0.9rem; }
</style>
</head>
<body class="bg-light">

<div class="container my-5">
    <h3 class="mb-4">📦 Pedidos Recentes</h3>

    <div id="pedidos-list" class="row g-4">
        <?php if(empty($pedidos)): ?>
            <div class="alert alert-warning">Nenhum pedido encontrado.</div>
        <?php else: ?>
            <?php foreach($pedidos as $p): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card pedido-card" data-id="<?= $p['id'] ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="pedido-header">Pedido #<?= $p['id'] ?></span>
                            <span class="badge badge-status 
                                <?= $p['status']=='entregue'?'bg-success':(
                                   in_array($p['status'], ['pendente','aceito','em_entrega'])?'bg-warning text-dark':'bg-danger'
                                )?>">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </div>
                        <p class="mb-1"><strong>Cliente:</strong> <?= htmlspecialchars($p['cliente_nome'] ?? 'Cliente não cadastrado') ?></p>
                        <p class="mb-1"><strong>Total:</strong> R$ <?= number_format($p['total'] + $p['taxa_entrega'], 2, ',', '.') ?></p>
                        <p class="mb-1"><strong>Pagamento:</strong> <?= htmlspecialchars($p['metodo_pagamento']) ?></p>
                        <p class="text-muted mb-3"><small><?= date('d/m/Y H:i', strtotime($p['data_criacao'])) ?></small></p>

                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary ver-detalhes" data-id="<?= $p['id'] ?>">Detalhes</button>
                            <button class="btn btn-sm btn-success acao-pedido" data-id="<?= $p['id'] ?>" data-acao="aceitar" <?= $p['status']!='pendente'?'style="display:none;"':'' ?>>Aceitar</button>
                            <button class="btn btn-sm btn-danger acao-pedido" data-id="<?= $p['id'] ?>" data-acao="cancelar" <?= $p['status']!='pendente'?'style="display:none;"':'' ?>>Cancelar</button>
                            <button class="btn btn-sm btn-primary acao-pedido" data-id="<?= $p['id'] ?>" data-acao="enviar" <?= $p['status']!='aceito'?'style="display:none;"':'' ?>>Enviar</button>
                            <button class="btn btn-sm btn-success acao-pedido" data-id="<?= $p['id'] ?>" data-acao="finalizar" <?= $p['status']!='em_entrega'?'style="display:none;"':'' ?>>Finalizar</button>
                        </div>

                        <div class="mt-2 detalhes-container"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Ajax para ações de pedidos
$(document).on('click', '.acao-pedido', function(){
    let btn = $(this);
    let id = btn.data('id');
    let acao = btn.data('acao');

    $.post('pedidoacoesajax.php', {id, acao}, function(res){
        location.reload(); // atualiza pedidos após ação
    });
});

// Ajax para ver detalhes
$(document).on('click', '.ver-detalhes', function(){
    let card = $(this).closest('.card');
    let id = $(this).data('id');
    let container = card.find('.detalhes-container');

    $.get('pedidodetalhesajax.php', {id}, function(res){
        container.html(res);
    });
});
</script>
</body>
</html>
