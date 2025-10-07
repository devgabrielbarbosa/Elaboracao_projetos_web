<?php
session_start();
require '../includes/conexao.php';

// Segurança básica
if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    die("Acesso negado: sessão de admin ou loja não encontrada.");
}

$admin_id   = $_SESSION['admin_id'];
$nome_admin = $_SESSION['admin_nome'] ?? 'Administrador';
$loja_id    = (int)$_SESSION['loja_id'];

// Dados da loja
$loja = ['nome'=>"Loja #{$loja_id}", 'logo'=>null];
$stmt = $pdo->prepare("SELECT id, nome, logo FROM lojas WHERE id = :id LIMIT 1");
$stmt->execute([':id'=>$loja_id]);
$tmp = $stmt->fetch(PDO::FETCH_ASSOC);
if($tmp) $loja = $tmp;

// Função para evitar erros em queries
function fetchColumnSafe($pdo, $sql, $params, $default=0){
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return $default;
    }
}

// Totais
$totalFaturamento   = fetchColumnSafe($pdo, "SELECT IFNULL(SUM(total + taxa_entrega),0) FROM pedidos WHERE status='entregue' AND loja_id=:loja_id", [':loja_id'=>$loja_id]);
$pedidosEntregues   = (int)fetchColumnSafe($pdo, "SELECT COUNT(*) FROM pedidos WHERE status='entregue' AND loja_id=:loja_id", [':loja_id'=>$loja_id]);
$pedidosAndamento   = (int)fetchColumnSafe($pdo, "SELECT COUNT(*) FROM pedidos WHERE status IN ('pendente','aceito','em_entrega') AND loja_id=:loja_id", [':loja_id'=>$loja_id]);
$pedidosCancelados  = (int)fetchColumnSafe($pdo, "SELECT COUNT(*) FROM pedidos WHERE status='cancelado' AND loja_id=:loja_id", [':loja_id'=>$loja_id]);
$totalClientes      = (int)fetchColumnSafe($pdo, "SELECT COUNT(*) FROM clientes WHERE loja_id=:loja_id", [':loja_id'=>$loja_id]);
$totalProdutos      = (int)fetchColumnSafe($pdo, "SELECT COUNT(*) FROM produtos WHERE loja_id=:loja_id", [':loja_id'=>$loja_id]);

// Últimos pedidos
$stmt = $pdo->prepare("SELECT id, total, taxa_entrega, status, metodo_pagamento, data_criacao FROM pedidos WHERE loja_id=:loja_id ORDER BY data_criacao DESC LIMIT 6");
$stmt->execute([':loja_id'=>$loja_id]);
$ultimosPedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Faturamento semanal
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

// Preparar labels e valores para JS
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

// Link do cardápio
$linkCardapio = "/delivery_lanches/login.php?loja_id={$loja_id}";

// Preparar dados para passar ao HTML
$data = [
    'nome_admin'       => $nome_admin,
    'loja'             => $loja,
    'totais'           => [
        'faturamento'      => $totalFaturamento,
        'entregues'        => $pedidosEntregues,
        'andamento'        => $pedidosAndamento,
        'cancelados'       => $pedidosCancelados,
        'clientes'         => $totalClientes,
        'produtos'         => $totalProdutos
    ],
    'ultimosPedidos'   => $ultimosPedidos,
    'labelsGrafico'    => $labels,
    'valoresGrafico'   => $valores,
    'linkCardapio'     => $linkCardapio
];

// Passa os dados como JSON para JS via query string ou arquivo intermediário
file_put_contents('dashboard_data.json', json_encode($data));

// Redireciona para o HTML puro
header("Location: dashboard.html");
exit;
