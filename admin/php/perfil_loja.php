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

// ===== Sessão =====
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    respostaJSON(['erro' => 'Admin ou loja não logado.'], 401);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];


try {
    // ===== GET: retorna dados da loja =====
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("SELECT id, nome, telefone, email, endereco, cidade, estado, cep, status, taxa_entrega_padrao, logo FROM lojas WHERE id = ?");
        $stmt->execute([$loja_id]);
        $loja = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$loja) {
            respostaJSON(['erro' => 'Loja não encontrada'], 404);
        }

        // Buscar horários existentes
        $stmt = $pdo->prepare("SELECT dia_semana, hora_abertura, hora_fechamento, status FROM horarios_loja WHERE loja_id = ?");
        $stmt->execute([$loja_id]);
        $horariosDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Inicializa todos os dias da semana
        $dias_semana = ['Segunda','Terca','Quarta','Quinta','Sexta','Sabado','Domingo'];
        $horarios = [];
        foreach ($dias_semana as $dia) {
            $horarios[$dia] = ['hora_abertura'=>'', 'hora_fechamento'=>'', 'status'=>'aberto'];
        }

        // Sobrescreve com horários existentes
        foreach ($horariosDB as $h) {
            $horarios[$h['dia_semana']] = [
                'hora_abertura' => $h['hora_abertura'],
                'hora_fechamento' => $h['hora_fechamento'],
                'status' => $h['status']
            ];
        }

        // Converter logo em base64 se existir
        if ($loja['logo']) {
            $loja['logo'] = base64_encode($loja['logo']);
        }

        $loja['horarios'] = $horarios;

        // Retorna pelo menos o nome da loja e alguns dados básicos se estiverem vazios
        respostaJSON(['loja' => $loja]);
    }

    // ===== POST: atualiza dados da loja =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $email = $_POST['email'] ?? '';
        $endereco = $_POST['endereco'] ?? '';
        $cidade = $_POST['cidade'] ?? '';
        $estado = $_POST['estado'] ?? '';
        $cep = $_POST['cep'] ?? '';
        $status = $_POST['status'] ?? 'ativa';
        $taxa_entrega_padrao = $_POST['taxa_entrega_padrao'] ?? 0;

        $logoData = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['tmp_name']) {
            $logoData = file_get_contents($_FILES['logo']['tmp_name']);
        }

        if ($logoData) {
            $stmt = $pdo->prepare("UPDATE lojas SET nome=?, telefone=?, email=?, endereco=?, cidade=?, estado=?, cep=?, status=?, taxa_entrega_padrao=?, logo=? WHERE id=?");
            $stmt->execute([$nome, $telefone, $email, $endereco, $cidade, $estado, $cep, $status, $taxa_entrega_padrao, $logoData, $loja_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE lojas SET nome=?, telefone=?, email=?, endereco=?, cidade=?, estado=?, cep=?, status=?, taxa_entrega_padrao=? WHERE id=?");
            $stmt->execute([$nome, $telefone, $email, $endereco, $cidade, $estado, $cep, $status, $taxa_entrega_padrao, $loja_id]);
        }

        // Atualiza horários
        $horarios_post = $_POST['horarios'] ?? [];
        foreach ($dias_semana as $dia) {
            if (!isset($horarios_post[$dia])) continue;

            $hora_abertura = $horarios_post[$dia]['hora_abertura'] ?? '';
            $hora_fechamento = $horarios_post[$dia]['hora_fechamento'] ?? '';
            $statusDia = $horarios_post[$dia]['status'] ?? 'aberto';

            $stmt = $pdo->prepare("SELECT id FROM horarios_loja WHERE loja_id=? AND dia_semana=?");
            $stmt->execute([$loja_id, $dia]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($registro) {
                $stmt = $pdo->prepare("UPDATE horarios_loja SET hora_abertura=?, hora_fechamento=?, status=? WHERE id=?");
                $stmt->execute([$hora_abertura, $hora_fechamento, $statusDia, $registro['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO horarios_loja (loja_id, dia_semana, hora_abertura, hora_fechamento, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$loja_id, $dia, $hora_abertura, $hora_fechamento, $statusDia]);
            }
        }

        respostaJSON(['sucesso' => 'Perfil atualizado com sucesso!']);
    }

    respostaJSON(['erro'=>'Método não permitido'], 405);

} catch(PDOException $e) {
    respostaJSON(['erro'=>'Erro no banco: '.$e->getMessage()], 500);
}
