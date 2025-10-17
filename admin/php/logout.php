<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

// Elimina todos os dados da sessão atual
$_SESSION = [];

// Destroi o cookie da sessão (caso exista)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroi a sessão em si
session_destroy();

// Retorna resposta JSON para o front-end
echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Logout realizado com sucesso.'
]);
exit;
