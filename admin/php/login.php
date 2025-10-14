<?php
session_start();
require __DIR__ . '/../../includes/conexao.php'; // ajustando caminho

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        header("Location: ../login.html?erro=" . urlencode("Preencha todos os campos"));
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($senha, $admin['senha'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];

        if (isset($admin['loja_id']) && $admin['loja_id'] > 0) {
            $_SESSION['loja_id'] = (int)$admin['loja_id'];
            header("Location: ../paginas/dashboard.html"); // HTML separado
            exit;
        } else {
            header("Location: ../login.html?erro=" . urlencode("Loja não associada"));
            exit;
        }
    } else {
        header("Location: ../login.html?erro=" . urlencode("E-mail ou senha incorretos"));
        exit;
    }
} else {
    header("Location: ../login.html");
    exit;
}
?>