<?php
session_start();
require '../includes/conexao.php';

header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    echo json_encode(['erro'=>'Admin não logado']);
    exit;
}

$loja_id = (int)$_SESSION['loja_id'];

$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');
$horarios = $_POST['horarios'] ?? [];

// Converter horários para JSON
$horarios_json = json_encode($horarios, JSON_UNESCAPED_UNICODE);

try {
    $stmt = $pdo->prepare("UPDATE lojas SET nome=:nome, telefone=:telefone, mensagem=:mensagem, horarios=:horarios WHERE id=:id");
    $stmt->execute([
        ':nome'=>$nome,
        ':telefone'=>$telefone,
        ':mensagem'=>$mensagem,
        ':horarios'=>$horarios_json,
        ':id'=>$loja_id
    ]);

    echo json_encode(['sucesso'=>'Perfil atualizado com horários!']);
} catch(PDOException $ex){
    echo json_encode(['erro'=>'Erro ao atualizar perfil']);
}
?>
