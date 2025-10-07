<?php
require '../includes/conexao.php';
if(!isset($_GET['id'])) exit;

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT url FROM fotos_produto WHERE produto_id=:id LIMIT 1");
$stmt->execute([':id'=>$id]);
$img = $stmt->fetch(PDO::FETCH_ASSOC);

if($img && $img['url']){
    header("Content-Type: image/jpeg"); // ajuste se precisar PNG
    echo $img['url'];
} else {
    readfile('../img/product-placeholder.png'); // placeholder
}
?>
