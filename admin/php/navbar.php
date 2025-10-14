<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/conexao.php';

// Verifica se o admin está logado
$admin_id = $_SESSION['admin_id'] ?? null;

$nome_admin = 'Administrador';
$foto_admin = 'https://via.placeholder.com/150?text=Sem+Foto';

if ($admin_id) {
    $stmt = $pdo->prepare("SELECT nome, foto FROM administradores WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $nome_admin = $admin['nome'] ?? 'Administrador';
        if (!empty($admin['foto'])) {
            // Garante que o caminho não tenha vulnerabilidades
            $foto_admin = '../' . ltrim($admin['foto'], '/');
        }
    }
}

// Preparar JSON para HTML/JS
$data = [
    'nome_admin' => $nome_admin,
    'foto_admin' => $foto_admin
];

// Salva o JSON em arquivo
file_put_contents('navbar_data.json', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

// Redireciona para HTML separado
header("Location: navbar.html");
exit;
