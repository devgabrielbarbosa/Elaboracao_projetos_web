<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require '../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Método inválido");
}

$loja_id = $_SESSION['admin_id'] ?? null; // você usa admin_id para identificar a loja?
if (!$loja_id) exit("Erro: administrador não logado");

// ===== Dados do POST =====
$dados = [
    'nome'       => trim($_POST['nome'] ?? ''),
    'endereco'   => trim($_POST['endereco'] ?? ''),
    'bairro'     => trim($_POST['bairro'] ?? ''),
    'rua'        => trim($_POST['rua'] ?? ''),
    'logradouro' => trim($_POST['logradouro'] ?? ''),
    'cep'        => trim($_POST['cep'] ?? ''),
    'cidade'     => trim($_POST['cidade'] ?? ''),
    'estado'     => trim($_POST['estado'] ?? ''),
    'telefone'   => trim($_POST['telefone'] ?? ''),
    'horarios'   => trim($_POST['horarios'] ?? ''),
    'status'     => trim($_POST['status'] ?? 'aberto'),
    'mensagem'   => trim($_POST['mensagem'] ?? '')
];

// ===== Logo =====
$logo_sql = "";
$logo_data = null;

if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg','image/png','image/gif'];
    if (!in_array($_FILES['logo']['type'], $allowed_types)) {
        exit("Erro: arquivo de logo inválido!");
    }

    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($_FILES['logo']['size'] > $maxSize) {
        exit("Erro: arquivo de logo muito grande. Máx 2MB.");
    }

    $logo_data = file_get_contents($_FILES['logo']['tmp_name']);
    $logo_sql = ", logo = :logo";
}

// ===== Montar SQL =====
$sql = "UPDATE lojas SET 
            nome = :nome,
            endereco = :endereco,
            bairro = :bairro,
            rua = :rua,
            logradouro = :logradouro,
            cep = :cep,
            cidade = :cidade,
            estado = :estado,
            telefone = :telefone,
            horarios = :horarios,
            status = :status,
            mensagem = :mensagem
            $logo_sql
        WHERE id = :id";

$stmt = $pdo->prepare($sql);

// Bind dos valores
foreach ($dados as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(":id", $loja_id, PDO::PARAM_INT);
if ($logo_data) {
    $stmt->bindValue(":logo", $logo_data, PDO::PARAM_LOB);
}

// ===== Executar =====
if ($stmt->execute()) {
    echo "Perfil atualizado com sucesso!";
} else {
    echo "Erro ao atualizar perfil.";
}

