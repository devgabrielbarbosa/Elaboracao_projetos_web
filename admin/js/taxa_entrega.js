const urlPHP = '../php/taxa_entrega.php';
const tbody = document.getElementById('lista-faixas');
const form = document.getElementById('form-faixa');
const mensagemDiv = document.getElementById('mensagem');

function mostrarMensagem(msg, tipo='success') {
    mensagemDiv.innerHTML = `<div class="alert alert-${tipo}">${msg}</div>`;
    setTimeout(()=>mensagemDiv.innerHTML='',3000);
}

function carregarFaixas() {
    fetch(urlPHP+'listar.php')
    .then(res=>res.json())
    .then(data=>{
        tbody.innerHTML='';
        data.forEach(f=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${f.nome_faixa}</td>
                <td>${parseFloat(f.valor).toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-warning btn-editar" data-id="${f.id}" data-nome="${f.nome_faixa}" data-valor="${f.valor}">Editar</button>
                    <button class="btn btn-sm btn-danger btn-excluir" data-id="${f.id}">Excluir</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    });
}

form.addEventListener('submit', e=>{
    e.preventDefault();
    const id = document.getElementById('faixa-id').value;
    const nome = document.getElementById('nome_faixa').value;
    const valor = document.getElementById('valor').value;

    const formData = new FormData();
    formData.append('nome_faixa', nome);
    formData.append('valor', valor);
    if(id){
        formData.append('id', id);
        fetch(urlPHP+'editar.php',{method:'POST', body:formData})
            .then(res=>res.text())
            .then(msg=>{ mostrarMensagem(msg); form.reset(); document.getElementById('faixa-id').value=''; carregarFaixas(); });
    }else{
        fetch(urlPHP+'adicionar.php',{method:'POST', body:formData})
            .then(res=>res.text())
            .then(msg=>{ mostrarMensagem(msg); form.reset(); carregarFaixas(); });
    }
});

tbody.addEventListener('click', e=>{
    if(e.target.classList.contains('btn-editar')){
        const btn = e.target;
        document.getElementById('faixa-id').value = btn.dataset.id;
        document.getElementById('nome_faixa').value = btn.dataset.nome;
        document.getElementById('valor').value = btn.dataset.valor;
    }
    if(e.target.classList.contains('btn-excluir')){
        if(!confirm('Deseja excluir esta faixa?')) return;
        const formData = new FormData();
        formData.append('id', e.target.dataset.id);
        fetch(urlPHP+'excluir.php',{method:'POST', body:formData})
            .then(res=>res.text())
            .then(msg=>{ mostrarMensagem(msg,'danger'); carregarFaixas(); });
    }
});

document.getElementById('cancelar').addEventListener('click', ()=>{
    form.reset();
    document.getElementById('faixa-id').value='';
});

carregarFaixas();
