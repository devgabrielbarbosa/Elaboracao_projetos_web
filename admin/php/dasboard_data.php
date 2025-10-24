<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';
session_start();

function resposta($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Sessão
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    http_response_code(401);
    resposta(['erro' => 'Sessão expirada. Faça login novamente.']);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];

function respostaJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 1) Ler resumo da VIEW dashboard_loja (se existir)
    $sqlView = "SELECT faturamento_total, entregues, andamento, cancelados, total_clientes, total_produtos
                FROM dashboard_loja
                WHERE loja_id = :loja_id
                LIMIT 1";
    $stmt = $pdo->prepare($sqlView);
    $stmt->execute([':loja_id' => $loja_id]);
    $resumo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se a view não existir ou não trouxer resultado, inicializa zeros
    if (!$resumo) {
        $resumo = [
            'faturamento_total' => 0,
            'entregues' => 0,
            'andamento' => 0,
            'cancelados' => 0,
            'total_clientes' => 0,
            'total_produtos' => 0
        ];
    }

    // 2) Últimos pedidos (limit 5)
    $sqlPedidos = "SELECT id, total, taxa_entrega, status, metodo_pagamento, data_criacao
                   FROM pedidos
                   WHERE loja_id = :loja_id
                   ORDER BY data_criacao DESC
                   LIMIT 5";
    $stmt = $pdo->prepare($sqlPedidos);
    $stmt->execute([':loja_id' => $loja_id]);
    $ultimosPedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normaliza campos numéricos/formatáveis para o JSON
    foreach ($ultimosPedidos as &$p) {
        $p['total'] = (float) $p['total'];
        $p['taxa_entrega'] = (float) $p['taxa_entrega'];
        // data_criacao já vem em formato 'YYYY-MM-DD HH:MM:SS' do MySQL — JS tratará para exibir
    }
    unset($p);

    // 3) Gráfico: faturamento últimos 7 dias (somente pedidos entregues)
    $sqlGrafico = "SELECT DATE(data_criacao) AS dia, COALESCE(SUM(total + taxa_entrega),0) AS faturamento
                   FROM pedidos
                   WHERE loja_id = :loja_id
                     AND status = 'entregue'
                     AND data_criacao >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                   GROUP BY dia
                   ORDER BY dia ASC";
    $stmt = $pdo->prepare($sqlGrafico);
    $stmt->execute([':loja_id' => $loja_id]);
    $dadosGrafico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monta arrays com labels (d/m) e valores (float) para os últimos 7 dias (hoje -6 ... hoje)
    $labels = [];
    $valores = [];
    $mapGraf = [];
    foreach ($dadosGrafico as $g) {
        $mapGraf[$g['dia']] = (float) $g['faturamento'];
    }
    for ($i = 6; $i >= 0; $i--) {
        $dia = date('Y-m-d', strtotime("-$i day"));
        $labels[] = date('d/m', strtotime($dia));
        $valores[] = isset($mapGraf[$dia]) ? $mapGraf[$dia] : 0.0;
    }

    // Monta resposta final
    $response = [
        'loja_id' => $loja_id,
        'totais' => [
            'faturamento' => (float) $resumo['faturamento_total'],
            'entregues' => (int) $resumo['entregues'],
            'andamento' => (int) $resumo['andamento'],
            'cancelados' => (int) $resumo['cancelados'],
            'clientes' => (int) $resumo['total_clientes'],
            'produtos' => (int) $resumo['total_produtos']
        ],
        'ultimosPedidos' => $ultimosPedidos,
        'labelsGrafico' => $labels,
        'valoresGrafico' => $valores
    ];

    respostaJSON($response);

} catch (PDOException $e) {
    // Em produção, não enviar $e->getMessage() bruto — aqui pode-se logar e retornar mensagem genérica
    respostaJSON(['erro' => 'Erro no servidor: ' . $e->getMessage()], 500);
}
