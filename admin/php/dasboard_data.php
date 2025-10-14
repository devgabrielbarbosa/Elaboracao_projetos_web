// ajax/dashboard_data.php
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require '../includes/conexao.php';

if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Administrador não logado']);
    exit;
}

$admin_id = (int) $_SESSION['admin_id'];

try {
    // Totais
    $stmt = $pdo->prepare("SELECT SUM(total + taxa_entrega) AS faturamento FROM pedidos WHERE admin_id = :admin_id");
    $stmt->execute([':admin_id' => $admin_id]);
    $totais['faturamento'] = (float) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT status, COUNT(*) AS qtd FROM pedidos WHERE admin_id = :admin_id GROUP BY status");
    $stmt->execute([':admin_id' => $admin_id]);
    $status_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $totais['entregues']  = (int)($status_counts['entregue'] ?? 0);
    $totais['andamento']  = (int)(($status_counts['pendente'] ?? 0) + ($status_counts['aceito'] ?? 0) + ($status_counts['em_entrega'] ?? 0));
    $totais['cancelados'] = (int)($status_counts['cancelado'] ?? 0);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE admin_id = :admin_id");
    $stmt->execute([':admin_id' => $admin_id]);
    $totais['clientes'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE admin_id = :admin_id");
    $stmt->execute([':admin_id' => $admin_id]);
    $totais['produtos'] = (int) $stmt->fetchColumn();

    // Últimos pedidos
    $stmt = $pdo->prepare("SELECT id, total, taxa_entrega, status, metodo_pagamento, data_criacao FROM pedidos WHERE admin_id = :admin_id ORDER BY data_criacao DESC LIMIT 5");
    $stmt->execute([':admin_id' => $admin_id]);
    $ultimosPedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gráfico faturamento últimos 7 dias
    $stmt = $pdo->prepare("SELECT DATE(data_criacao) AS dia, SUM(total + taxa_entrega) AS total FROM pedidos WHERE admin_id = :admin_id AND data_criacao >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(data_criacao) ORDER BY dia ASC");
    $stmt->execute([':admin_id' => $admin_id]);
    $grafico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labelsGrafico = [];
    $valoresGrafico = [];
    for ($i = 6; $i >= 0; $i--) {
        $dia = date('Y-m-d', strtotime("-$i days"));
        $labelsGrafico[] = date('d/m', strtotime($dia));
        $valoresGrafico[] = 0;
    }
    foreach ($grafico as $g) {
        $idx = array_search(date('d/m', strtotime($g['dia'])), $labelsGrafico);
        if ($idx !== false) $valoresGrafico[$idx] = (float) $g['total'];
    }

    echo json_encode([
        'totais' => $totais,
        'ultimosPedidos' => $ultimosPedidos,
        'labelsGrafico' => $labelsGrafico,
        'valoresGrafico' => $valoresGrafico
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no banco: ' . $e->getMessage()]);
}
