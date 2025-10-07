<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require '../includes/conexao.php';

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    echo json_encode(['erro' => 'Administrador não logado']);
    exit;
}

$sql = "SELECT * FROM lojas WHERE id = (SELECT loja_id FROM administradores WHERE id = :id LIMIT 1) LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    if ($admin['logo']) $admin['logo_base64'] = base64_encode($admin['logo']);
    echo json_encode($admin);
} else {
    echo json_encode(['erro' => 'Administrador não encontrado']);
}
?>
