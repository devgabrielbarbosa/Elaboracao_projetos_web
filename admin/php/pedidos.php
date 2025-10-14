<?php
session_start();
require '../includes/conexao.php';

if (!isset($_SESSION['admin_id'])) {
    echo '<div class="alert alert-danger">Sessão expirada. Faça login novamente.</div>';
    exit;
}

$admin_id = (int)$_SESSION['admin_id'];

$stmt = $pdo->prepare("
    SELECT p.id, p.cliente_id, p.total, p.taxa_entrega, p.status, 
           p.metodo_pagamento, p.data_criacao, c.nome AS cliente_nome
    FROM pedidos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.admin_id = :admin_id
    ORDER BY p.data_criacao DESC
");
$stmt->execute([':admin_id' => $admin_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($pedidos)) {
    echo '<div class="alert alert-warning">Nenhum pedido encontrado.</div>';
    exit;
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

foreach ($pedidos as $p) {
    $statusClass = match ($p['status']) {
        'entregue' => 'bg-success',
        'pendente', 'aceito', 'em_entrega' => 'bg-warning text-dark',
        default => 'bg-danger',
    };

    $total = number_format($p['total'] + $p['taxa_entrega'], 2, ',', '.');
    $cliente = h($p['cliente_nome'] ?? 'Cliente não cadastrado');
    $metodoPagamento = h($p['metodo_pagamento'] ?? '-');
    $dataPedido = date('d/m/Y H:i', strtotime($p['data_criacao']));

    echo "
    <div class='col-md-6 col-lg-4'>
        <div class='card pedido-card' data-id='{$p['id']}'>
            <div class='card-body'>
                <div class='d-flex justify-content-between align-items-center mb-2'>
                    <span class='pedido-header'>Pedido #{$p['id']}</span>
                    <span class='badge badge-status {$statusClass}'>" . ucfirst(h($p['status'])) . "</span>
                </div>
                <p class='mb-1'><strong>Cliente:</strong> {$cliente}</p>
                <p class='mb-1'><strong>Total:</strong> R$ {$total}</p>
                <p class='mb-1'><strong>Pagamento:</strong> {$metodoPagamento}</p>
                <p class='text-muted mb-3'><small>{$dataPedido}</small></p>

                <div class='d-flex gap-2'>
                    <button class='btn btn-sm btn-primary ver-detalhes' data-id='{$p['id']}'>Detalhes</button>
                    <button class='btn btn-sm btn-success acao-pedido' data-id='{$p['id']}' data-acao='aceitar'" . ($p['status'] != 'pendente' ? ' style="display:none;"' : '') . ">Aceitar</button>
                    <button class='btn btn-sm btn-danger acao-pedido' data-id='{$p['id']}' data-acao='cancelar'" . ($p['status'] != 'pendente' ? ' style="display:none;"' : '') . ">Cancelar</button>
                    <button class='btn btn-sm btn-primary acao-pedido' data-id='{$p['id']}' data-acao='enviar'" . ($p['status'] != 'aceito' ? ' style="display:none;"' : '') . ">Enviar</button>
                    <button class='btn btn-sm btn-success acao-pedido' data-id='{$p['id']}' data-acao='finalizar'" . ($p['status'] != 'em_entrega' ? ' style="display:none;"' : '') . ">Finalizar</button>
                </div>
                <div class='mt-2 detalhes-container'></div>
            </div>
        </div>
    </div>
    ";
}
