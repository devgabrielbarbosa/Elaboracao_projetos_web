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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? '';

        // ===== Adicionar promoção =====
        if ($acao === 'adicionar') {
            $codigo      = trim($_POST['codigo'] ?? '');
            $descricao   = trim($_POST['descricao'] ?? '');
            $desconto    = floatval($_POST['desconto'] ?? 0);
            $data_inicio = $_POST['data_inicio'] ?? null;
            $data_fim    = $_POST['data_fim'] ?? null;
            $produtos    = $_POST['produtos'] ?? []; // array de produto_id enviados do JS

            if (!$codigo) respostaJSON(['erro' => 'Campo "codigo" obrigatório.']);
            if ($desconto <= 0) respostaJSON(['erro' => 'Desconto deve ser maior que zero.']);
            if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
                respostaJSON(['erro' => 'Imagem inválida ou não enviada.']);
            }

            $maxSize = 2 * 1024 * 1024; // 2MB
            if ($_FILES['imagem']['size'] > $maxSize) {
                respostaJSON(['erro' => 'A imagem é muito grande. O limite é 2MB.']);
            }

            $imagemConteudo = file_get_contents($_FILES['imagem']['tmp_name']);
            $imagemTipo     = $_FILES['imagem']['type'];

            // Evitar duplicidade de código na mesma loja
            $check = $pdo->prepare("SELECT COUNT(*) FROM promocoes WHERE codigo = :codigo AND loja_id = :loja_id");
            $check->execute([':codigo' => $codigo, ':loja_id' => $loja_id]);
            if ($check->fetchColumn() > 0) {
                respostaJSON(['erro' => 'Já existe promoção com esse código nesta loja.']);
            }

            // Inserir promoção
            $stmt = $pdo->prepare("
                INSERT INTO promocoes
                (admin_id, loja_id, codigo, descricao, desconto, ativo, data_inicio, data_fim, imagem_blob, imagem_tipo, data_criacao)
                VALUES (:admin_id, :loja_id, :codigo, :descricao, :desconto, 1, :data_inicio, :data_fim, :imagem_blob, :imagem_tipo, NOW())
            ");
            $stmt->execute([
                ':admin_id'   => $admin_id,
                ':loja_id'    => $loja_id,
                ':codigo'     => $codigo,
                ':descricao'  => $descricao,
                ':desconto'   => $desconto,
                ':data_inicio'=> $data_inicio,
                ':data_fim'   => $data_fim,
                ':imagem_blob'=> $imagemConteudo,
                ':imagem_tipo'=> $imagemTipo
            ]);

            $promocao_id = $pdo->lastInsertId();

            // ===== Associar produtos à promoção =====
            foreach ($produtos as $produto) {
                $produto_id = (int)$produto['id'];
                $preco_original = floatval($produto['preco_original']);
                $preco_desconto = round($preco_original * (1 - $desconto / 100), 2);

                $stmt = $pdo->prepare("
                    INSERT INTO promocao_produtos
                    (promocao_id, produto_id, preco_original, preco_desconto)
                    VALUES (:promocao_id, :produto_id, :preco_original, :preco_desconto)
                ");
                $stmt->execute([
                    ':promocao_id'   => $promocao_id,
                    ':produto_id'    => $produto_id,
                    ':preco_original'=> $preco_original,
                    ':preco_desconto'=> $preco_desconto
                ]);
            }

            respostaJSON(['sucesso' => true, 'mensagem' => 'Promoção cadastrada com sucesso!']);
        }

        // ===== Toggle ativo/desativo =====
        if ($acao === 'toggle' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare("UPDATE promocoes SET ativo = 1 - ativo WHERE id = :id AND loja_id = :loja_id");
            $stmt->execute([':id' => $id, ':loja_id' => $loja_id]);
            respostaJSON(['sucesso' => true, 'mensagem' => 'Status atualizado.']);
        }

        // ===== Excluir promoção =====
        if ($acao === 'excluir' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM promocoes WHERE id = :id AND loja_id = :loja_id");
            $stmt->execute([':id' => $id, ':loja_id' => $loja_id]);
            respostaJSON(['sucesso' => true, 'mensagem' => 'Promoção excluída com sucesso.']);
        }

        respostaJSON(['erro' => 'Ação POST inválida.'], 400);
    }

    // ===== GET: Listar promoções =====
    $stmt = $pdo->prepare("
        SELECT id, codigo, descricao, desconto, ativo, data_inicio, data_fim, imagem_blob, imagem_tipo 
        FROM promocoes 
        WHERE loja_id = :loja_id 
        ORDER BY id DESC
    ");
    $stmt->execute([':loja_id' => $loja_id]);
    $promocoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($promocoes as &$p) {
        if (!empty($p['imagem_blob'])) {
            $tipo = $p['imagem_tipo'] ?? 'image/png';
            $p['imagem'] = 'data:' . $tipo . ';base64,' . base64_encode($p['imagem_blob']);
        } else {
            $p['imagem'] = null;
        }
        unset($p['imagem_blob'], $p['imagem_tipo']);
    }

    // ===== GET: Listar produtos da loja =====
    $stmt = $pdo->prepare("SELECT id, nome, preco FROM produtos WHERE loja_id = :loja_id ORDER BY nome ASC");
    $stmt->execute([':loja_id' => $loja_id]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    respostaJSON([
        'promocoes' => $promocoes,
        'produtos'  => $produtos
    ]);

} catch (PDOException $e) {
    respostaJSON(['erro' => 'Erro no banco: ' . $e->getMessage()], 500);
}
