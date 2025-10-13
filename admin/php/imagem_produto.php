<?php
require __DIR__ . '/../../includes/conexao.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT foto_id FROM produtos WHERE id = :id");
$stmt->execute([':id' => $id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($produto && $produto['foto_id']) {
    header("Content-Type: image/jpeg"); // ou alterar dinamicamente conforme tipo real
    echo $produto['foto_id'];
    exit;
}

header("HTTP/1.0 404 Not Found");
