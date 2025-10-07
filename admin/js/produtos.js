async function carregarProdutos(){
    const res = await fetch('../php/produtos.php');
    const data = await res.json();

    document.getElementById('mensagem').innerHTML = data.mensagem || '';
    const container = document.getElementById('produtos-lista');
    container.innerHTML = '';

    if(data.produtos.length === 0){
        container.innerHTML = `<p class="text-center text-muted">Nenhum produto cadastrado.</p>`;
        return;
    }

    data.produtos.forEach(p => {
        const ativoClass = p.ativo ? 'bg-success' : 'bg-secondary';
        const ativoText = p.ativo ? 'Ativo' : 'Pausado';
        container.innerHTML += `
        <div class="col-md-4">
            <div class="card card-produto shadow-sm">
                <img src="../php/imagem_produto.php?id=${p.id}" class="produto-img" alt="${p.nome}">
                <div class="card-body">
                    <h5 class="card-title">${p.nome}</h5>
                    <p class="card-text">${p.descricao}</p>
                    <p class="card-text text-danger fw-bold">R$ ${parseFloat(p.preco).toFixed(2).replace('.', ',')}</p>
                    <span class="badge ${ativoClass} badge-ativo">${ativoText}</span>
                    <div class="d-flex justify-content-between mt-2">
                        <button onclick="alterarStatus(${p.id}, '${p.ativo ? 'pausar':'ativar'}')" class="btn btn-sm ${p.ativo?'btn-warning':'btn-success'}">${p.ativo?'Pausar':'Ativar'}</button>
                        <a href="produtos_editar.html?id=${p.id}" class="btn btn-sm btn-primary">Editar</a>
                        <button onclick="deletarProduto(${p.id})" class="btn btn-sm btn-danger">Deletar</button>
                    </div>
                </div>
            </div>
        </div>`;
    });
}

async function adicionarProduto(form){
    const formData = new FormData(form);
    await fetch('../php/produtos.php', {
        method:'POST',
        body: formData
    });
    await carregarProdutos();
    form.reset();
}

document.getElementById('form-adicionar-produto').addEventListener('submit', e=>{
    e.preventDefault();
    adicionarProduto(e.target);
});

async function alterarStatus(id, acao){
    await fetch(`../php/produtos.php?acao=${acao}&id=${id}`);
    await carregarProdutos();
}

async function deletarProduto(id){
    if(confirm('Deseja realmente deletar?')){
        await fetch(`../php/produtos.php?acao=deletar&id=${id}`);
        await carregarProdutos();
    }
}

// Inicializa
carregarProdutos();
