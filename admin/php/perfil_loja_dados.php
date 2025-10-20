<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// Função para responder em JSON
function respostaJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// ===== Sessão =====
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    respostaJSON(['erro' => 'Admin ou loja não logado.'], 401);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM lojas WHERE id = ?");
    $stmt->execute([$loja_id]);
    $loja = $stmt->fetch(PDO::FETCH_ASSOC);

    if($loja){
        // Horarios devem vir como JSON decodificado
        $loja['horarios'] = json_decode($loja['horarios'] ?? '{}', true);
        echo json_encode(['loja'=>$loja]);
    } else {
        echo json_encode(['erro'=>'Loja não encontrada']);
    }
} catch(PDOException $e){
    echo json_encode(['erro'=>'Erro ao buscar loja: '.$e->getMessage()]);
}
?>
