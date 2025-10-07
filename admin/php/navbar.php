<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/conexao.php';

$admin_id = $_SESSION['admin_id'] ?? null;

$nome_admin = 'Administrador';
$foto_admin = 'https://via.placeholder.com/150?text=Sem+Foto';

if ($admin_id) {
    $stmt = $pdo->prepare("SELECT nome, foto FROM administradores WHERE id = :id");
    $stmt->execute([':id' => $admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $nome_admin = $admin['nome'] ?? 'Administrador';
        if (!empty($admin['foto'])) {
            $foto_admin = '../' . $admin['foto'];
        }
    }
}

// Preparar JSON para o HTML/JS
$data = [
    'nome_admin' => $nome_admin,
    'foto_admin' => $foto_admin
];

file_put_contents('navbar_data.json', json_encode($data));
header("Location: navbar.html");
exit;
?>