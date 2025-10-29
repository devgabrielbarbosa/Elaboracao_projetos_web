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

$acao     = $_REQUEST['acao'] ?? null;

try {
    if (!$acao || $acao === 'listar') {
        $stmt = $pdo->prepare("
            SELECT p.id, p.nome, p.descricao, p.preco, p.ativo, p.categoria_id,
                   c.nome_categoria AS categoria_nome, p.imagem
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

        foreach ($produtos as &$p) {
            if (!empty($p['imagem'])) {
                $p['imagem'] = 'data:image/png;base64,' . base64_encode($p['imagem']);
            } else {
                $p['imagem'] = null;
            }
        }
        unset($p);

        echo json_encode(['produtos' => $produtos]);
        exit;
    }

    if ($acao === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome         = trim($_POST['nome'] ?? '');
        $descricao    = trim($_POST['descricao'] ?? '');
        $preco        = is_numeric($_POST['preco'] ?? '') ? (float)$_POST['preco'] : null;
        $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;

        if ($nome === '' || $preco === null) {
            echo json_encode(['erro' => 'Nome e preço obrigatórios.']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO produtos (nome, descricao, preco, categoria_id, loja_id, admin_id, ativo, data_criacao)
            VALUES (:nome, :descricao, :preco, :categoria_id, :loja_id, :admin_id, 1, NOW())
        ");
        $stmt->execute([
            ':nome'        => $nome,
            ':descricao'   => $descricao,
            ':preco'       => $preco,
            ':categoria_id'=> $categoria_id,
            ':loja_id'     => $loja_id,
            ':admin_id'    => $admin_id
        ]);

        $produtoId = $pdo->lastInsertId();

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
            $tmp = $_FILES['imagem']['tmp_name'];
            $imageData = @file_get_contents($tmp);
            if ($imageData === false) {
                echo json_encode(['erro' => 'Arquivo inválido.']);
                exit;
            }

            $stmt2 = $pdo->prepare("UPDATE produtos SET imagem = :imagem WHERE id = :id AND loja_id = :loja_id");
            $stmt2->bindParam(':imagem', $imageData, PDO::PARAM_LOB);
            $stmt2->bindParam(':id', $produtoId, PDO::PARAM_INT);
            $stmt2->bindParam(':loja_id', $loja_id, PDO::PARAM_INT);
            $stmt2->execute();
        }

        echo json_encode(['sucesso' => 'Produto adicionado.', 'id' => $produtoId]);
        exit;
    }

    if (($acao === 'pausar' || $acao === 'ativar') && isset($_REQUEST['id'])) {
        $id = (int)$_REQUEST['id'];
        $ativo = ($acao === 'ativar') ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE produtos SET ativo = :ativo WHERE id = :id AND loja_id = :loja_id");
        $stmt->execute([':ativo'=>$ativo, ':id'=>$id, ':loja_id'=>$loja_id]);
        echo json_encode(['sucesso' => 'Status atualizado.']);
        exit;
    }

    if ($acao === 'deletar' && isset($_REQUEST['id'])) {
        $id = (int)$_REQUEST['id'];
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = :id AND loja_id = :loja_id");
        $stmt->execute([':id'=>$id, ':loja_id'=>$loja_id]);
        echo json_encode(['sucesso' => 'Produto deletado.']);
        exit;
    }

    echo json_encode(['erro' => 'Ação inválida.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no banco: ' . $e->getMessage()]);
}
