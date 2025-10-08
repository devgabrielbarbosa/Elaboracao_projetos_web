<?php
session_start();
require __DIR__ . '/../../includes/conexao.php'; // ajuste se necessário

// DEBUG ON (para ambiente de desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// === Log básico ===
$logfile = __DIR__ . '/debug_promocoes.log';
function append_log($file, $text) {
    file_put_contents($file, $text, FILE_APPEND | LOCK_EX);
}

$logEntry  = "[" . date('c') . "] URL: " . ($_SERVER['REQUEST_URI'] ?? '') . PHP_EOL;
$logEntry .= "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? '') . PHP_EOL;
$logEntry .= "SESSION: " . print_r($_SESSION, true) . PHP_EOL;
$logEntry .= "POST: " . print_r($_POST, true) . PHP_EOL;
$logEntry .= "FILES: " . print_r($_FILES, true) . PHP_EOL;
$logEntry .= "---------------------" . PHP_EOL;
append_log($logfile, $logEntry);

// === Verifica sessão ===
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    echo json_encode(['erro' => 'Admin ou loja não logado. Verifique sessão.']);
    exit;
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];

// === Função auxiliar de upload ===
function upload_error_message($code) {
    $map = [
        UPLOAD_ERR_INI_SIZE   => 'Arquivo maior que upload_max_filesize (server).',
        UPLOAD_ERR_FORM_SIZE  => 'Arquivo maior que MAX_FILE_SIZE no formulário.',
        UPLOAD_ERR_PARTIAL    => 'Upload parcial.',
        UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo enviado.',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente.',
        UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever no disco.',
        UPLOAD_ERR_EXTENSION  => 'Upload interrompido por extensão.'
    ];
    return $map[$code] ?? "Erro desconhecido no upload: " . $code;
}

// === POST - Adicionar promoção ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'adicionar') {
    $codigo      = trim($_POST['codigo'] ?? '');
    $descricao   = trim($_POST['descricao'] ?? '');
    $desconto    = $_POST['desconto'] ?? null;
    $data_inicio = $_POST['data_inicio'] ?? null;
    $data_fim    = $_POST['data_fim'] ?? null;

    if ($codigo === '') {
        echo json_encode(['erro' => 'Campo "codigo" é obrigatório.']);
        exit;
    }
    if ($desconto === null || $desconto === '') {
        echo json_encode(['erro' => 'Campo "desconto" é obrigatório.']);
        exit;
    }
    if (!isset($_FILES['imagem'])) {
        echo json_encode(['erro' => 'Arquivo de imagem não enviado.']);
        exit;
    }
    if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['erro' => upload_error_message($_FILES['imagem']['error'])]);
        exit;
    }

    $imagem_blob = file_get_contents($_FILES['imagem']['tmp_name']);
    if (!$imagem_blob) {
        echo json_encode(['erro' => 'Falha ao ler o arquivo enviado.']);
        exit;
    }

    // checar duplicidade
    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM promocoes WHERE codigo = :codigo AND loja_id = :loja_id");
        $check->execute([':codigo' => $codigo, ':loja_id' => $loja_id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['erro' => 'Já existe uma promoção com esse código nesta loja.']);
            exit;
        }
    } catch (PDOException $ex) {
        append_log($logfile, "[ERROR] Check duplicidade: " . $ex->getMessage() . PHP_EOL);
        echo json_encode(['erro' => 'Erro ao verificar duplicidade.']);
        exit;
    }

    // inserir promoção
    try {
        $stmt = $pdo->prepare("INSERT INTO promocoes
            (admin_id, loja_id, codigo, descricao, desconto, ativo, data_inicio, data_fim, imagem, data_criacao)
            VALUES (:admin_id, :loja_id, :codigo, :descricao, :desconto, 1, :data_inicio, :data_fim, :imagem, NOW())");
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $stmt->bindParam(':loja_id', $loja_id, PDO::PARAM_INT);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':desconto', $desconto);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->bindParam(':imagem', $imagem_blob, PDO::PARAM_LOB);
        $stmt->execute();

        echo json_encode(['mensagem' => 'Promoção cadastrada com sucesso!']);
        exit;
    } catch (PDOException $ex) {
        append_log($logfile, "[ERROR] Insert: " . $ex->getMessage() . PHP_EOL);
        echo json_encode(['erro' => 'Erro ao salvar no banco.']);
        exit;
    }
}

// === GET - ações diretas ===
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao'], $_GET['id'])) {
    $acao = $_GET['acao'];
    $id   = (int) $_GET['id'];
    try {
        if ($acao === 'ativar') {
            $pdo->prepare("UPDATE promocoes SET ativo=1 WHERE id=:id AND loja_id=:loja_id")
                ->execute([':id'=>$id, ':loja_id'=>$loja_id]);
            echo json_encode(['mensagem' => 'Promoção ativada.']); exit;
        }
        if ($acao === 'desativar') {
            $pdo->prepare("UPDATE promocoes SET ativo=0 WHERE id=:id AND loja_id=:loja_id")
                ->execute([':id'=>$id, ':loja_id'=>$loja_id]);
            echo json_encode(['mensagem' => 'Promoção desativada.']); exit;
        }
        if ($acao === 'deletar') {
            $pdo->prepare("DELETE FROM promocoes WHERE id=:id AND loja_id=:loja_id")
                ->execute([':id'=>$id, ':loja_id'=>$loja_id]);
            echo json_encode(['mensagem' => 'Promoção excluída.']); exit;
        }
        echo json_encode(['erro' => 'Ação inválida.']); exit;
    } catch (PDOException $ex) {
        append_log($logfile, "[ERROR] Ação ({$acao}): " . $ex->getMessage() . PHP_EOL);
        echo json_encode(['erro' => 'Erro ao executar ação: ' . $ex->getMessage()]);
        exit;
    }
}

// === GET padrão - listar promoções ===
try {
    $stmt = $pdo->prepare("SELECT * FROM promocoes WHERE loja_id = :loja_id ORDER BY id DESC");
    $stmt->execute([':loja_id' => $loja_id]);
    $promocoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($promocoes as &$p) {
        if (!empty($p['imagem'])) {
            $bin = $p['imagem'];
            $mime = 'image/jpeg';
            if (strpos($bin, "\x89PNG") === 0) $mime = 'image/png';
            elseif (strpos($bin, "GIF8") === 0) $mime = 'image/gif';
            $p['imagem'] = 'data:' . $mime . ';base64,' . base64_encode($bin);
        } else {
            $p['imagem'] = 'https://via.placeholder.com/350x180?text=Sem+Imagem';
        }
    }
    unset($p);

    echo json_encode(['promocoes' => $promocoes]);
    exit;
} catch (PDOException $ex) {
    append_log($logfile, "[ERROR] Select: " . $ex->getMessage() . PHP_EOL);
    echo json_encode(['erro' => 'Erro ao buscar promoções: ' . $ex->getMessage()]);
    exit;
}
?>
