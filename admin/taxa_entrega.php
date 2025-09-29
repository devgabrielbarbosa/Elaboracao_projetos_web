<?php
session_start();
require '../includes/conexao.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

// ID da loja atual do admin logado
$loja_id = $_SESSION['loja_id'] ?? 2;

$mensagem = '';

// Adicionar ou editar faixa de entrega
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])){
    $acao = $_POST['acao'];

    if($acao === 'faixa'){ // Adicionar faixa
        $nome_faixa = trim($_POST['nome_faixa']);
        $valor = floatval($_POST['valor']);

        $stmt = $pdo->prepare("INSERT INTO faixas_entrega (nome_faixa, valor, loja_id) VALUES (:nome_faixa, :valor, :loja_id)");
        $stmt->execute([
            ':nome_faixa' => $nome_faixa,
            ':valor' => $valor,
            ':loja_id' => $loja_id
        ]);

        $mensagem = "<div class='alert alert-success'>Faixa de entrega cadastrada!</div>";
    } elseif($acao === 'editar'){ // Editar faixa
        $id = (int)$_POST['id'];
        $nome_faixa = trim($_POST['nome_faixa']);
        $valor = floatval($_POST['valor']);

        $stmt = $pdo->prepare("UPDATE faixas_entrega SET nome_faixa=:nome_faixa, valor=:valor WHERE id=:id AND loja_id=:loja_id");
        $stmt->execute([
            ':nome_faixa' => $nome_faixa,
            ':valor' => $valor,
            ':id' => $id,
            ':loja_id' => $loja_id
        ]);

        $mensagem = "<div class='alert alert-success'>Faixa de entrega atualizada!</div>";
    }
}

// Excluir faixa
if(isset($_GET['acao'], $_GET['id']) && $_GET['acao'] === 'excluir'){
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM faixas_entrega WHERE id=:id AND loja_id=:loja_id");
    $stmt->execute([':id'=>$id, ':loja_id'=>$loja_id]);

    $mensagem = "<div class='alert alert-success'>Faixa de entrega excluída!</div>";
}

// Pegar todas as faixas cadastradas da loja atual
$faixas = $pdo->prepare("SELECT * FROM faixas_entrega WHERE loja_id=:loja_id ORDER BY id ASC");
$faixas->execute([':loja_id'=>$loja_id]);
$faixas = $faixas->fetchAll(PDO::FETCH_ASSOC);

// Simulação de taxa para endereço do cliente
$taxa_simulada = null;
if(isset($_POST['endereco_cliente'])){
    $endereco_cliente = trim($_POST['endereco_cliente']);
    $taxa_simulada = 0;

    foreach($faixas as $faixa){
        if(stripos($endereco_cliente, $faixa['nome_faixa']) !== false){
            $taxa_simulada = $faixa['valor'];
            break;
        }
    }

    if($taxa_simulada === 0){
        $taxa_simulada = "Endereço fora da área de entrega!";
    }
}

include 'navbar.php';
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Taxa de Entrega (<?= htmlspecialchars($nome_admin) ?>)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: #f7f9fc;
    }
    .card {
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .btn-custom {
        border-radius: 0.5rem;
    }
    .table thead {
        background: #0d6efd;
        color: #fff;
    }
    .modal-content {
        border-radius: 0.75rem;
    }
</style>
</head>
<body>

<div class="container my-5">
    <div class="card p-4 mb-4">
        <h3 class="mb-3">Gerenciar Taxa de Entrega <small class="text-muted">(<?= htmlspecialchars($nome_admin) ?>)</small></h3>
        <?php if($mensagem) echo $mensagem; ?>

        <!-- Formulário de cadastro de faixa -->
        <form method="POST" class="mb-3">
            <input type="hidden" name="acao" value="faixa">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="nome_faixa" class="form-control" placeholder="Nome da Rua ou Bairro" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="valor" class="form-control" placeholder="Valor da taxa" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success btn-custom w-100">+ Adicionar Faixa</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Lista de faixas -->
    <div class="card p-4 mb-4">
        <h5 class="mb-3">Faixas de Entrega da Loja <span class="text-primary">#<?= $loja_id ?></span></h5>
        <div class="table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome da Faixa</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($faixas as $f): ?>
                    <tr>
                        <td><?= $f['id'] ?></td>
                        <td><?= htmlspecialchars($f['nome_faixa']) ?></td>
                        <td><span class="badge bg-primary fs-6">R$ <?= number_format($f['valor'],2,",",".") ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#editarModal<?= $f['id'] ?>">Editar</button>
                            <a href="taxa_entrega.php?acao=excluir&id=<?= $f['id'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Deseja realmente excluir esta faixa?')">Excluir</a>
                        </td>
                    </tr>

                    <!-- Modal de edição -->
                    <div class="modal fade" id="editarModal<?= $f['id'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <form method="POST">
                            <input type="hidden" name="acao" value="editar">
                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                            <div class="modal-header bg-primary text-white">
                              <h5 class="modal-title">Editar Faixa</h5>
                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <div class="mb-3">
                                <label class="form-label">Nome da Faixa</label>
                                <input type="text" name="nome_faixa" class="form-control" value="<?= htmlspecialchars($f['nome_faixa']) ?>" required>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Valor</label>
                                <input type="number" step="0.01" name="valor" class="form-control" value="<?= $f['valor'] ?>" required>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                              <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Simulação de taxa -->
    <div class="card p-4">
        <h5 class="mb-3">Simular Taxa de Entrega</h5>
        <form method="POST" class="mb-3">
            <div class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="endereco_cliente" class="form-control" placeholder="Digite o endereço do cliente" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-custom w-100">Calcular</button>
                </div>
            </div>
        </form>

        <?php if($taxa_simulada !== null): ?>
            <div class="alert alert-info">
                <strong>Endereço:</strong> <?= htmlspecialchars($endereco_cliente) ?><br>
                <strong>Taxa de entrega:</strong> 
                <?= is_numeric($taxa_simulada) ? "R$ ".number_format($taxa_simulada,2,",",".") : $taxa_simulada ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

