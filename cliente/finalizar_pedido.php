<?php
session_start();
require 'includes/conexao.php'; // PDO

if(!isset($_SESSION['cliente_id']) || empty($_SESSION['carrinho'])){
    exit("Carrinho vazio ou cliente não logado.");
}

// Recebendo dados do formulário
$cliente_id = $_SESSION['cliente_id'];
$nome_cliente = $_POST['nome'];
$telefone = $_POST['telefone'];
$endereco = $_POST['endereco'];
$localizacao = $_POST['localizacao'] ?? '';
$taxa_entrega = floatval($_POST['taxa_entrega']);
$total = floatval($_POST['total']) + $taxa_entrega;

// Inserir pedido na tabela pedidos
$stmt = $pdo->prepare("
    INSERT INTO pedidos 
    (cliente_id, total, taxa_entrega, endereco, telefone, localizacao) 
    VALUES (:cliente_id, :total, :taxa_entrega, :endereco, :telefone, :localizacao)
");
$stmt->execute([
    ':cliente_id' => $cliente_id,
    ':total' => $total,
    ':taxa_entrega' => $taxa_entrega,
    ':endereco' => $endereco,
    ':telefone' => $telefone,
    ':localizacao' => $localizacao
]);

$pedido_id = $pdo->lastInsertId();

// Inserir itens do pedido
foreach($_SESSION['carrinho'] as $produto_id => $item){
    $stmt = $pdo->prepare("
        INSERT INTO itens_pedido 
        (pedido_id, produto_id, quantidade, preco) 
        VALUES (:pedido_id, :produto_id, :quantidade, :preco)
    ");
    $stmt->execute([
        ':pedido_id' => $pedido_id,
        ':produto_id' => $produto_id,
        ':quantidade' => $item['quantidade'],
        ':preco' => $item['preco']
    ]);
}

// Gerar mensagem para WhatsApp
$mensagem = "🛒 *Novo Pedido* 🛒\n\n";
$mensagem .= "*Cliente:* $nome_cliente\n";
$mensagem .= "*Telefone:* $telefone\n";
$mensagem .= "*Endereço:* $endereco\n";
if($localizacao) $mensagem .= "*Localização:* $localizacao\n";
$mensagem .= "\n*Produtos:*\n";

foreach($_SESSION['carrinho'] as $item){
    $mensagem .= $item['nome'] . " x" . $item['quantidade'] . " - R$ " . number_format($item['preco'],2,",",".") . "\n";
}

$mensagem .= "\n*Taxa de entrega:* R$ " . number_format($taxa_entrega,2,",",".") . "\n";
$mensagem .= "*Total:* R$ " . number_format($total,2,",",".") . "\n";

// Número da empresa
$telefone_empresa = '5511999999999'; // Coloque o número correto
$whatsapp = "https://wa.me/$telefone_empresa?text=" . urlencode($mensagem);

// Limpar carrinho
unset($_SESSION['carrinho']);

// Redirecionar para WhatsApp
header("Location: $whatsapp");
exit;
