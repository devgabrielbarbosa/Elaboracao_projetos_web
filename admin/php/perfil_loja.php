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

// Array de dias da semana esperados
$dias_semana = ['Segunda','Terca','Quarta','Quinta','Sexta','Sabado','Domingo'];

// Recebe os horários via POST
$horarios_post = $_POST['horarios'] ?? [];

// Valida os dados recebidos
foreach($dias_semana as $dia){
    if(!isset($horarios_post[$dia]['hora_abertura']) || !isset($horarios_post[$dia]['hora_fechamento']) || !isset($horarios_post[$dia]['status'])){
        echo json_encode(['erro'=>"Horário do dia $dia não informado corretamente."]);
        exit;
    }
}

try {
    // Loop por cada dia da semana
    foreach($dias_semana as $dia){
        $hora_abertura = $horarios_post[$dia]['hora_abertura'];
        $hora_fechamento = $horarios_post[$dia]['hora_fechamento'];
        $status = $horarios_post[$dia]['status']; // 'aberto' ou 'fechado'

        // Verifica se já existe registro para este dia
        $stmt = $pdo->prepare("SELECT id FROM horarios_loja WHERE loja_id=:loja_id AND dia_semana=:dia");
        $stmt->execute([':loja_id'=>$loja_id, ':dia'=>$dia]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if($registro){
            // Atualiza
            $stmt = $pdo->prepare("UPDATE horarios_loja SET hora_abertura=:hora_abertura, hora_fechamento=:hora_fechamento, status=:status WHERE id=:id");
            $stmt->execute([
                ':hora_abertura'=>$hora_abertura,
                ':hora_fechamento'=>$hora_fechamento,
                ':status'=>$status,
                ':id'=>$registro['id']
            ]);
        } else {
            // Insere
            $stmt = $pdo->prepare("INSERT INTO horarios_loja (loja_id, dia_semana, hora_abertura, hora_fechamento, status) VALUES (:loja_id, :dia, :hora_abertura, :hora_fechamento, :status)");
            $stmt->execute([
                ':loja_id'=>$loja_id,
                ':dia'=>$dia,
                ':hora_abertura'=>$hora_abertura,
                ':hora_fechamento'=>$hora_fechamento,
                ':status'=>$status
            ]);
        }
    }

    echo json_encode(['sucesso'=>'Horários atualizados com sucesso!']);

} catch(PDOException $ex){
    echo json_encode(['erro'=>'Erro ao atualizar horários: '.$ex->getMessage()]);
}
?>
