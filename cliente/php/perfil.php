<?php
session_start();
require __DIR__ . '/../../includes/conexao.php';
header('Content-Type: application/json; charset=utf-8');

function respostaJSON($data,$code=200){
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if(!isset($_SESSION['cliente_id'],$_SESSION['loja_id'])){
    respostaJSON(['erro'=>'Cliente não logado'],401);
}

$cliente_id = (int)$_SESSION['cliente_id'];
$loja_id = (int)$_SESSION['loja_id'];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome = trim($_POST['nome']??'');
    $telefone = trim($_POST['telefone']??'');
    $data_nascimento = $_POST['data_nascimento']??null;

    $foto = null;
    if(isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error']===0){
        $foto = file_get_contents($_FILES['foto_perfil']['tmp_name']);
    }

    $sql = "UPDATE clientes SET nome=:nome, telefone=:telefone, data_nascimento=:data_nascimento";
    $params = [':nome'=>$nome, ':telefone'=>$telefone, ':data_nascimento'=>$data_nascimento, ':id'=>$cliente_id];
    if($foto){
        $sql.=", foto_perfil=:foto";
        $params[':foto']=$foto;
    }
    $sql.=" WHERE id=:id AND loja_id=:loja_id";
    $params[':loja_id']=$loja_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    respostaJSON(['sucesso'=>true,'mensagem'=>'Perfil atualizado']);
}

// GET -> retorna dados do cliente
$stmt = $pdo->prepare("SELECT nome,telefone,data_nascimento,foto_perfil FROM clientes WHERE id=:id AND loja_id=:loja_id");
$stmt->execute([':id'=>$cliente_id,':loja_id'=>$loja_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
if($cliente['foto_perfil']) $cliente['foto_perfil']='data:image/jpeg;base64,'.base64_encode($cliente['foto_perfil']);
respostaJSON($cliente);
