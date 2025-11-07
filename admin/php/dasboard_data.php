<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

function respostaJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    respostaJSON(['erro' => 'Admin ou loja não logado.'], 401);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];

try {
    function fetchColumnSafe($pdo, $sql, $params = [], $default = 0){
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() ?? $default;
        } catch (PDOException $e) {
            return $default;
        }
    }

    // ======== TOTAIS ========
    $totalFaturamento   = (float) fetchColumnSafe($pdo, "SELECT IFNULL(SUM(total + taxa_entrega),0) FROM pedidos WHERE status='entregue' AND loja_id=:loja_id", [':loja_id'=>$loja_id]);
    $pedidosEntregues   = (int) fetchColumnSafe($pdo, "SELECT COUNT(*) FROM pedidos WHERE status='entregue' AND loja_id=:loja_id", [':loja_id'=>$loja_id]);
    $pedidosAndamento   = (int) fetchColumnSafe($pdo, "SELECT COUNT(*) FROM pedidos WHERE status IN ('pendente','aceito','em_entrega') AND loja_id=:loja_id", [':loja_id'=>$loja_id]);
    $pedidosCancelados  = (int) fetchColumnSafe($pdo, "SELECT COUNT(*) FROM pedidos WHERE status='cancelado' AND loja_id=:loja_id", [':loja_id'=>$loja_id]);
    $totalClientes      = (int) fetchColumnSafe($pdo, "SELECT COUNT(*) FROM clientes WHERE loja_id=:loja_id", [':loja_id'=>$loja_id]);
    $totalProdutos      = (int) fetchColumnSafe($pdo, "SELECT COUNT(*) FROM produtos WHERE loja_id=:loja_id AND ativo=1", [':loja_id'=>$loja_id]);

    // ======== FATURAMENTO DIÁRIO (7 DIAS) ========
    $stmt = $pdo->prepare("
        SELECT DATE(data_criacao) AS dia, COALESCE(SUM(total + taxa_entrega),0) AS faturamento
        FROM pedidos
        WHERE loja_id = :loja_id AND status='entregue' AND data_criacao >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY dia
        ORDER BY dia ASC
    ");
    $stmt->execute([':loja_id'=>$loja_id]);
    $dadosGrafico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $mapGraf = [];
    foreach($dadosGrafico as $g){
        $mapGraf[$g['dia']] = (float)$g['faturamento'];
    }

    $labels = [];
    $valores = [];
    for($i=6; $i>=0; $i--){
        $dia = date('Y-m-d', strtotime("-$i day"));
        $labels[] = date('d/m', strtotime($dia));
        $valores[] = $mapGraf[$dia] ?? 0.0;
    }

    // ======== LINK PÚBLICO DA LOJA ========
    $stmt = $pdo->prepare("SELECT slug FROM lojas WHERE id = ?");
    $stmt->execute([$loja_id]);
    $slug = $stmt->fetchColumn();

    if (!$slug) {
        respostaJSON(['erro' => 'Slug da loja não encontrado.'], 500);
    }

    // Verifica se já existe link na tabela links_lojas
    $stmt = $pdo->prepare("SELECT url_completa FROM links_lojas WHERE loja_id = ? AND nome_link = 'Página inicial'");
    $stmt->execute([$loja_id]);
    $url = $stmt->fetchColumn();

    // Se não existir, cria automaticamente
    if (!$url) {
        $stmt = $pdo->prepare("INSERT INTO links_lojas (loja_id, nome_link, slug, tipo, ativo)
                               VALUES (?, 'Página inicial', ?, 'publico', 1)");
        $stmt->execute([$loja_id, $slug]);

        $url = '/loja/' . $slug;
    }

    // Últimos 5 pedidos
    $stmt = $pdo->prepare("SELECT id, total, taxa_entrega, status, metodo_pagamento, data_criacao 
                           FROM pedidos 
                           WHERE loja_id=:loja_id 
                           ORDER BY data_criacao DESC LIMIT 5");
    $stmt->execute([':loja_id'=>$loja_id]);
    $ultimosPedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ======== RESPOSTA FINAL ========
    $response = [
        'loja_id' => $loja_id,
        'link_publico' => $url,
        'totais' => [
            'faturamento' => $totalFaturamento,
            'entregues'   => $pedidosEntregues,
            'andamento'   => $pedidosAndamento,
            'cancelados'  => $pedidosCancelados,
            'clientes'    => $totalClientes,
            'produtos'    => $totalProdutos
        ],
        'ultimosPedidos' => $ultimosPedidos,
        'labelsGrafico'  => $labels,
        'valoresGrafico' => $valores
    ];

    respostaJSON($response);

} catch (PDOException $e){
    respostaJSON(['erro'=>'Erro: ' . $e->getMessage()], 500);
}
