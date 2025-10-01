<?php
session_start();
require '../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && $senha === $admin['senha']) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];
        if(isset($admin['loja_id']) && $admin['loja_id'] > 0){
            $_SESSION['loja_id'] = (int)$admin['loja_id'];
            header("Location: dashboard.php");
            exit;
        } else {
            header("Location: login.html?erro=Loja+não+associada");
            exit;
        }
    } else {
        header("Location: login.html?erro=E-mail+ou+senha+incorretos");
        exit;
    }
} else {
    header("Location: cadastro_admin.html");
    exit;
}
