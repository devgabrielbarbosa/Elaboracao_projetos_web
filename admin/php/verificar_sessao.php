<?php
session_start();

// Desativar qualquer output que possa quebrar o JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../../includes/conexao.php';

// Função para enviar JSON e encerrar o script
function respostaJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Verifica sessão
if (!isset($_SESSION['admin_id'], $_SESSION['loja_id'])) {
    respostaJSON(['erro' => 'Sessão expirada. Faça login novamente.'], 401);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id  = (int) $_SESSION['loja_id'];

try {
    // Busca dados do admin
    $stmt = $pdo->prepare("SELECT nome, foto FROM administradores WHERE id = :admin_id AND loja_id = :loja_id LIMIT 1");
    $stmt->execute([':admin_id' => $admin_id, ':loja_id' => $loja_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        respostaJSON(['erro' => 'Administrador não encontrado.'], 404);
    }

    // Se não houver foto, usar placeholder
    $admin['foto'] = $admin['foto'] ?? 'https://via.placeholder.com/200x150?text=Sem+Imagem';

    respostaJSON([
        'nome' => $admin['nome'],
        'foto' => $admin['foto']
    ]);

} catch (PDOException $e) {
    respostaJSON(['erro' => 'Erro no banco de dados.'], 500);
}
