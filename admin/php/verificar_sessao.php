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


try {
    $stmt = $pdo->prepare("SELECT id, nome, foto FROM administradores WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) resposta(['erro' => 'Administrador não encontrado.'], 404);

    $foto = $admin['foto'] ?: 'https://placehold.co/200x150?text=Sem+Imagem';

    resposta([
        'admin_id' => (int)$admin['id'],
        'nome'     => $admin['nome'],
        'foto'     => $foto,
        'loja_id'  => $loja_id
    ]);

} catch (PDOException $e) {
    resposta(['erro' => 'Erro no servidor.'], 500);
}
