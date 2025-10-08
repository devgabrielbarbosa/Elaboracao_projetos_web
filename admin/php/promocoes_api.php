<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

function respostaJSON($data) {
    echo json_encode($data);
    exit;
}

// ===== Sessão =====
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    respostaJSON(['erro' => 'Admin ou loja não logado.']);
}
$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];

// ===== POST: Ações =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';

    // ===== Adicionar promoção =====
    if ($acao === 'adicionar') {
        $codigo      = trim($_POST['codigo'] ?? '');
        $descricao   = trim($_POST['descricao'] ?? '');
        $desconto    = $_POST['desconto'] ?? null;
        $data_inicio = $_POST['data_inicio'] ?? null;
        $data_fim    = $_POST['data_fim'] ?? null;

        if (!$codigo) respostaJSON(['erro' => 'Campo "codigo" obrigatório.']);
        if (!$desconto) respostaJSON(['erro' => 'Campo "desconto" obrigatório.']);
        if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            respostaJSON(['erro' => 'Imagem inválida ou não enviada.']);
        }

        // Limite de tamanho
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($_FILES['imagem']['size'] > $maxSize) {
            respostaJSON(['erro' => 'A imagem é muito grande. O limite é 2MB.']);
        }

        $imagemConteudo = file_get_contents($_FILES['imagem']['tmp_name']);
        $imagemTipo     = $_FILES['imagem']['type'];

        // Checar duplicidade por loja
        try {
            $check = $pdo->prepare("SELECT COUNT(*) FROM promocoes WHERE codigo = :codigo AND loja_id = :loja_id");
            $check->execute([':codigo' => $codigo, ':loja_id' => $loja_id]);
            if ($check->fetchColumn() > 0) {
                respostaJSON(['erro' => 'Já existe promoção com esse código nesta loja.']);
            }
        } catch (PDOException $ex) {
            respostaJSON(['erro' => 'Erro ao verificar duplicidade.']);
        }

        // Inserir
        try {
            $stmt = $pdo->prepare("INSERT INTO promocoes
                (admin_id, loja_id, codigo, descricao, desconto, ativo, data_inicio, data_fim, imagem_blob, imagem_tipo, data_criacao)
                VALUES (:admin_id, :loja_id, :codigo, :descricao, :desconto, 1, :data_inicio, :data_fim, :imagem_blob, :imagem_tipo, NOW())");

            $stmt->bindParam(':admin_id', $admin_id);
            $stmt->bindParam(':loja_id', $loja_id);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':desconto', $desconto);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->bindParam(':imagem_blob', $imagemConteudo, PDO::PARAM_LOB);
            $stmt->bindParam(':imagem_tipo', $imagemTipo);
            $stmt->execute();

            respostaJSON(['mensagem' => 'Promoção cadastrada com sucesso!']);
        } catch (PDOException $ex) {
            if (strpos($ex->getMessage(), 'Got a packet bigger than') !== false) {
                respostaJSON(['erro' => 'A imagem é muito grande. Tente enviar um arquivo menor.']);
            } else {
                respostaJSON(['erro' => 'Erro ao salvar no banco.']);
            }
        }
    }

    // ===== Toggle ativo/desativo =====
    if ($acao === 'toggle' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        try {
            $stmt = $pdo->prepare("UPDATE promocoes SET ativo = 1 - ativo WHERE id = :id AND loja_id = :loja_id");
            $stmt->execute([':id' => $id, ':loja_id' => $loja_id]);
            respostaJSON(['mensagem' => 'Status atualizado.']);
        } catch (PDOException $ex) {
            respostaJSON(['erro' => 'Erro ao atualizar status.']);
        }
    }

    // ===== Excluir promoção =====
    if ($acao === 'excluir' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM promocoes WHERE id = :id AND loja_id = :loja_id");
            $stmt->execute([':id' => $id, ':loja_id' => $loja_id]);
            respostaJSON(['mensagem' => 'Promoção excluída com sucesso.']);
        } catch (PDOException $ex) {
            respostaJSON(['erro' => 'Erro ao excluir promoção.']);
        }
    }
}

// ===== GET: Listar promoções =====
try {
    $stmt = $pdo->prepare("SELECT id, codigo, descricao, desconto, ativo, data_inicio, data_fim, imagem_blob, imagem_tipo 
                           FROM promocoes 
                           WHERE loja_id = :loja_id 
                           ORDER BY id DESC");
    $stmt->execute([':loja_id' => $loja_id]);
    $promocoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Converter BLOB para base64
    foreach ($promocoes as &$p) {
        $p['imagem_blob'] = $p['imagem_blob'] ? base64_encode($p['imagem_blob']) : null;
    }
    unset($p);

    respostaJSON(['promocoes' => $promocoes]);
} catch (PDOException $ex) {
    respostaJSON(['erro' => 'Erro ao buscar promoções.']);
}
?>
