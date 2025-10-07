<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';

if(!isset($_GET['id'])) exit;

$stmt = $pdo->prepare("SELECT imagem FROM promocoes WHERE id=:id");
$stmt->execute([':id' => $_GET['id']]);
$imagem = $stmt->fetchColumn();

if($imagem){
    // Detecta tipo pelo header do blob ou assume png
    header('Content-Type: image/png');
    echo $imagem;
    exit;
}
