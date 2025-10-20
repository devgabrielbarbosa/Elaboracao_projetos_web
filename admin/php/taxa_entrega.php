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

// Pega a ação enviada por POST ou GET
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

try {
    switch ($acao) {
        // CADASTRAR / EDITAR
        case 'salvar':
            $id = $_POST['id'] ?? '';
            $nome = trim($_POST['nome_faixa'] ?? '');
            $valor = $_POST['valor'] ?? '';
            $setor = trim($_POST['setor'] ?? '');
            $ativo = isset($_POST['ativo']) ? 1 : 0;

            if (empty($nome) || $valor === '') {
                respostaJSON(['erro' => 'Preencha todos os campos obrigatórios.']);
            }

            if ($id) {
                $stmt = $pdo->prepare("
                    UPDATE faixas_entrega 
                    SET nome_faixa = ?, valor = ?, setor = ?, ativo = ? 
                    WHERE id = ? AND loja_id = ?
                ");
                $stmt->execute([$nome, $valor, $setor, $ativo, $id, $loja_id]);
                respostaJSON(['mensagem' => 'Faixa atualizada com sucesso!']);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO faixas_entrega (loja_id, nome_faixa, valor, setor, ativo) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$loja_id, $nome, $valor, $setor, $ativo]);
                respostaJSON(['mensagem' => 'Faixa cadastrada com sucesso!']);
            }
            break;

        // LISTAR
        case 'listar':
            $stmt = $pdo->prepare("SELECT * FROM faixas_entrega WHERE loja_id = ? ORDER BY id DESC");
            $stmt->execute([$loja_id]);
            $faixas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            respostaJSON(['faixas' => $faixas]);
            break;

        // EXCLUIR
        case 'excluir':
            $id = $_POST['id'] ?? '';
            if (!$id) {
                respostaJSON(['erro' => 'ID inválido.']);
            }
            $stmt = $pdo->prepare("DELETE FROM faixas_entrega WHERE id = ? AND loja_id = ?");
            $stmt->execute([$id, $loja_id]);
            respostaJSON(['mensagem' => 'Faixa excluída com sucesso!']);
            break;

        default:
            respostaJSON(['erro' => 'Ação inválida.']);
    }
} catch (Exception $e) {
    respostaJSON(['erro' => 'Erro: ' . $e->getMessage()]);
}
