<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// ===== Função padrão de resposta =====
function respostaJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== Verifica sessão =====
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    respostaJSON(['erro' => 'Admin ou loja não logado.'], 401);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];

try {
    // === Busca dados do admin ===
    $stmt = $pdo->prepare("
        SELECT id, nome, foto 
        FROM administradores 
        WHERE id = :id 
        LIMIT 1
    ");
    $stmt->execute([':id' => $admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        respostaJSON(['erro' => 'Administrador não encontrado.'], 404);
    }

    $foto = $admin['foto'] ?: 'https://placehold.co/200x150?text=Sem+Imagem';

// === Busca dados da loja ===
$stmtLoja = $pdo->prepare("
    SELECT id, nome, slug 
    FROM lojas 
    WHERE id = :id 
    LIMIT 1
");
$stmtLoja->execute([':id' => $loja_id]);
$loja = $stmtLoja->fetch(PDO::FETCH_ASSOC);

if (!$loja) {
    respostaJSON(['erro' => 'Loja não encontrada.'], 404);
}

// === Gera slug limpo ===
$slug = strtolower(trim($loja['slug'] ?: $loja['nome']));
$slug = preg_replace('/[^a-z0-9]+/i', '-', $slug); // substitui espaço e caracteres especiais por '-'
$slug = trim($slug, '-'); // remove '-' no começo/fim

// Atualiza slug no banco se necessário
if (empty($loja['slug']) || $loja['slug'] !== $slug) {
    $updateSlug = $pdo->prepare("UPDATE lojas SET slug = :slug WHERE id = :id");
    $updateSlug->execute([':slug' => $slug, ':id' => $loja['id']]);
}

// === Retorna dados corretos para o front ===
respostaJSON([
    'admin_id'  => (int) $admin['id'],
    'nome'      => $admin['nome'],
    'foto'      => $foto,
    'loja_id'   => (int)  $loja['nome'],
    'loja_nome' => $loja['nome'],
    'slug'      => $slug
]);

} catch (PDOException $e) {
    respostaJSON(['erro' => 'Erro no servidor: ' . $e->getMessage()], 500);
}
