<?php
session_start();
require '../includes/conexao.php';

header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'], $_SESSION['loja_id'])){
    echo json_encode(['erro'=>'Sessão expirada']);
    exit;
}

$loja_id = $_SESSION['loja_id'];
$acao    = $_REQUEST['acao'] ?? 'listar'; // padrão listar

try {

    // ================= LISTAR =================
    if($acao === 'listar'){
        $stmt = $pdo->prepare("SELECT * FROM faixas_entrega WHERE loja_id=:loja_id ORDER BY id ASC");
        $stmt->execute([':loja_id'=>$loja_id]);
        $faixas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['faixas'=>$faixas]);
        exit;
    }

    // ================= ADICIONAR =================
    if($acao === 'adicionar'){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            throw new Exception('Método inválido');
        }

        $nome_faixa = trim($_POST['nome_faixa'] ?? '');
        $valor      = floatval($_POST['valor'] ?? 0);

        if($nome_faixa === '' || $valor <= 0){
            throw new Exception('Nome da faixa e valor devem ser preenchidos corretamente');
        }

        $stmt = $pdo->prepare("INSERT INTO faixas_entrega (nome_faixa, valor, loja_id) VALUES (:nome_faixa, :valor, :loja_id)");
        $stmt->execute([
            ':nome_faixa'=>$nome_faixa,
            ':valor'=>$valor,
            ':loja_id'=>$loja_id
        ]);

        echo json_encode(['sucesso'=>'Faixa de entrega cadastrada', 'id'=>$pdo->lastInsertId()]);
        exit;
    }

    // ================= EDITAR =================
    if($acao === 'editar'){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            throw new Exception('Método inválido');
        }

        $id         = intval($_POST['id'] ?? 0);
        $nome_faixa = trim($_POST['nome_faixa'] ?? '');
        $valor      = floatval($_POST['valor'] ?? 0);

        if($id <= 0 || $nome_faixa === '' || $valor <= 0){
            throw new Exception('Dados inválidos para edição');
        }

        $stmt = $pdo->prepare("UPDATE faixas_entrega SET nome_faixa=:nome_faixa, valor=:valor WHERE id=:id AND loja_id=:loja_id");
        $stmt->execute([
            ':nome_faixa'=>$nome_faixa,
            ':valor'=>$valor,
            ':id'=>$id,
            ':loja_id'=>$loja_id
        ]);

        echo json_encode(['sucesso'=>'Faixa de entrega atualizada']);
        exit;
    }

    // ================= EXCLUIR =================
    if($acao === 'excluir'){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            throw new Exception('Método inválido');
        }

        $id = intval($_POST['id'] ?? 0);
        if($id <= 0){
            throw new Exception('ID inválido');
        }

        $stmt = $pdo->prepare("DELETE FROM faixas_entrega WHERE id=:id AND loja_id=:loja_id");
        $stmt->execute([
            ':id'=>$id,
            ':loja_id'=>$loja_id
        ]);

        echo json_encode(['sucesso'=>'Faixa de entrega excluída']);
        exit;
    }

    throw new Exception('Ação inválida');

} catch (Exception $e){
    echo json_encode(['erro'=>$e->getMessage()]);
}
?>