<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require '../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_SESSION['admin_id'] ?? null;
    if (!$id) { 
        echo "Erro: administrador não logado"; 
        exit; 
    }

    // Sanitiza e pega os dados do POST
    $dados = [
        'nome' => trim($_POST['nome'] ?? ''),
        'endereco' => trim($_POST['endereco'] ?? ''),
        'bairro' => trim($_POST['bairro'] ?? ''),
        'rua' => trim($_POST['rua'] ?? ''),
        'logradouro' => trim($_POST['logradouro'] ?? ''),
        'cep' => trim($_POST['cep'] ?? ''),
        'cidade' => trim($_POST['cidade'] ?? ''),
        'estado' => trim($_POST['estado'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'horarios' => trim($_POST['horarios'] ?? ''),
        'status' => ($_POST['status'] ?? 'aberto'),
        'mensagem' => trim($_POST['mensagem'] ?? '')
    ];

    // Tratamento do logo
    $logo_sql = "";
    $logo_data = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $allowed_types = ['image/jpeg','image/png','image/gif'];
        if (in_array($_FILES['logo']['type'], $allowed_types)) {
            $logo_data = file_get_contents($_FILES['logo']['tmp_name']);
            $logo_sql = ", logo = :logo";
        } else {
            echo "Erro: arquivo de logo inválido!";
            exit;
        }
    }

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
    foreach ($dados as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    if ($logo_data) {
        $stmt->bindValue(":logo", $logo_data, PDO::PARAM_LOB);
    }

    echo $stmt->execute() ? "Perfil atualizado com sucesso!" : "Erro ao atualizar perfil";

} else {
    echo "Método inválido";
}
?>
