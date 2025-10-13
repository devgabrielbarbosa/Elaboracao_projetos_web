<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../includes/conexao.php';
session_start();

// Sessão
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];
$acao     = $_REQUEST['acao'] ?? null;

try {
    // ---------- LISTAR PRODUTOS ----------
    if (!$acao || $acao === 'listar') {
        $stmt = $pdo->prepare("
            SELECT p.id, p.nome, p.descricao, p.preco, p.ativo, p.categoria_id,
                   c.nome AS categoria_nome, p.imagem
            FROM produtos p
            LEFT JOIN categorias_produtos_lojas c 
                ON p.categoria_id = c.id AND c.loja_id = :loja_id_categoria
            WHERE p.loja_id = :loja_id_produto
            ORDER BY p.id DESC
        ");
        $stmt->execute([
            ':loja_id_categoria' => $loja_id,
            ':loja_id_produto'   => $loja_id
        ]);

        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Converte LONGBLOB em data URI (detecta mime)
        foreach ($produtos as &$p) {
            if (!empty($p['imagem'])) {
                $finfo = @getimagesizefromstring($p['imagem']);
                $mime = $finfo['mime'] ?? 'image/jpeg';
                $p['imagem'] = 'data:' . $mime . ';base64,' . base64_encode($p['imagem']);
            } else {
                $p['imagem'] = null;
            }
        }
        unset($p);

        echo json_encode(['produtos' => $produtos]);
        exit;
    }

    // ---------- ADICIONAR PRODUTO ----------
    if ($acao === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome         = trim($_POST['nome'] ?? '');
        $descricao    = trim($_POST['descricao'] ?? '');
        $preco_raw    = $_POST['preco'] ?? '';
        $preco        = is_numeric($preco_raw) ? (float)$preco_raw : null;
        $categoria_id = $_POST['categoria_id'] ?? null;

        if ($nome === '' || $preco === null) {
            echo json_encode(['erro' => 'Nome e preço são obrigatórios e devem ser válidos.']);
            exit;
        }

        // Inserir produto (imagem salva após insert)
        $stmt = $pdo->prepare("
            INSERT INTO produtos (nome, descricao, preco, categoria_id, loja_id, admin_id, ativo, data_criacao)
            VALUES (:nome, :descricao, :preco, :categoria_id, :loja_id, :admin_id, 1, NOW())
        ");
        $stmt->execute([
            ':nome'        => $nome,
            ':descricao'   => $descricao,
            ':preco'       => $preco,
            ':categoria_id'=> $categoria_id ?: null,
            ':loja_id'     => $loja_id,
            ':admin_id'    => $admin_id
        ]);

        $produtoId = $pdo->lastInsertId();

        // Se enviou imagem, valida e salva no LONGBLOB
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['erro' => 'Erro no upload da imagem. Código: ' . $_FILES['imagem']['error']]);
                exit;
            }

            // Limite de 4MB (ajusta conforme necessário)
            $maxBytes = 4 * 1024 * 1024;
            if ($_FILES['imagem']['size'] > $maxBytes) {
                // opcional: remover produto recém-criado se não quiser produto sem imagem
                echo json_encode(['erro' => 'A imagem é muito grande. Limite: 4MB.']);
                exit;
            }

            $tmp = $_FILES['imagem']['tmp_name'];
            $imageData = @file_get_contents($tmp);
            $imgInfo = @getimagesize($tmp);
            if ($imageData === false || $imgInfo === false) {
                echo json_encode(['erro' => 'Arquivo enviado não é uma imagem válida.']);
                exit;
            }

            // Salva LONGBLOB
            $stmt2 = $pdo->prepare("UPDATE produtos SET imagem = :imagem WHERE id = :id AND loja_id = :loja_id");
            $stmt2->bindParam(':imagem', $imageData, PDO::PARAM_LOB);
            $stmt2->bindParam(':id', $produtoId, PDO::PARAM_INT);
            $stmt2->bindParam(':loja_id', $loja_id, PDO::PARAM_INT);
            $stmt2->execute();
        }

        echo json_encode(['sucesso' => 'Produto adicionado com sucesso.', 'id' => $produtoId]);
        exit;
    }

    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;

if ($categoria_id === null) {
    echo json_encode(['erro' => 'Selecione uma categoria válida.']);
    exit;
}
    // ---------- ATIVAR / PAUSAR ----------
    if (($acao === 'pausar' || $acao === 'ativar') && isset($_REQUEST['id'])) {
        $id = (int)$_REQUEST['id'];
        $ativo = ($acao === 'ativar') ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE produtos SET ativo = :ativo WHERE id = :id AND loja_id = :loja_id");
        $stmt->execute([':ativo'=>$ativo, ':id'=>$id, ':loja_id'=>$loja_id]);
        echo json_encode(['sucesso' => 'Status atualizado.']);
        exit;
    }

    // ---------- DELETAR ----------
    if ($acao === 'deletar' && isset($_REQUEST['id'])) {
        $id = (int)$_REQUEST['id'];

        // se houver FK, a query pode falhar; aqui assumimos cascatas ou checagem prévia
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = :id AND loja_id = :loja_id");
        $stmt->execute([':id'=>$id, ':loja_id'=>$loja_id]);

        echo json_encode(['sucesso' => 'Produto deletado com sucesso.']);
        exit;
    }

    echo json_encode(['erro' => 'Ação inválida.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit;
}
