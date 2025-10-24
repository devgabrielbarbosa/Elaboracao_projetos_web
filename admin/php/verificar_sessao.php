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

// Ambiente de debug (mude para false em produção)
define('DEBUG', false);
if (DEBUG) { ini_set('display_errors',1); error_reporting(E_ALL); }
else { ini_set('display_errors',0); error_reporting(0); }

header('Content-Type: application/json; charset=utf-8');

// Tenta localizar o arquivo de conexão em caminhos comuns
$paths = [
    __DIR__ . '/conexao.php',
    __DIR__ . '/../../includes/conexao.php',
    __DIR__ . '/../includes/conexao.php',
    __DIR__ . '/../../../includes/conexao.php',
    __DIR__ . '/../../includes/conexao.php'
];

$found = false;
foreach ($paths as $p) {
    if (file_exists($p)) {
        require_once $p;
        $found = true;
        break;
    }
}

if (!$found || !isset($pdo)) {
    echo json_encode(['erro' => 'Arquivo de conexão não encontrado. Verifique includes/conexao.php'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Função util
function resposta($arr, $code = 200) {
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica sessão básica
if (!isset($_SESSION['admin_id'])) {
    resposta(['erro' => 'Admin não logado.'], 401);
}

$admin_id = (int) $_SESSION['admin_id'];
$loja_id = isset($_SESSION['loja_id']) ? (int) $_SESSION['loja_id'] : null;

try {
    // Tenta buscar dados do administrador (ajusta o nome da tabela se for outro)
    $sql = "SELECT id, nome, foto FROM administradores WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        resposta(['erro' => 'Administrador não encontrado.'], 404);
    }

    $foto = $admin['foto'] ?? '/uploads/placeholder.png'; // ajuste o path do placeholder se precisar

    resposta([
        'admin_id' => (int) $admin['id'],
        'nome'     => $admin['nome'],
        'foto'     => $foto,
        'loja_id'  => $loja_id
    ]);
} catch (PDOException $e) {
    if (DEBUG) resposta(['erro' => 'DB error: ' . $e->getMessage()], 500);
    error_log('verificarSessao.php PDOException: ' . $e->getMessage());
    resposta(['erro' => 'Erro no servidor.'], 500);
}
