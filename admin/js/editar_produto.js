document.addEventListener('DOMContentLoaded', () => {
  const formProduto = document.getElementById('formProduto');
  const inputImagem = document.getElementById('inputImagem');
  const previewImagem = document.getElementById('previewImagem');
  const selectCategorias = document.getElementById('categoria_id');
  const mensagem = document.getElementById('mensagem');

  function exibirMensagem(tipo, texto){
    mensagem.innerHTML = `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
      ${texto}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
  }

  async function fetchJSON(url, options={}){
    const res = await fetch(url, options);
    if(!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
  }

  // ===== Carregar categorias =====
  async function carregarCategorias(){
    try {
      const data = await fetchJSON('../php/categorias_lojas.php?acao=listar');
      selectCategorias.innerHTML = '<option value="">Selecione a categoria</option>';
      data.categorias.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat.id;
        opt.textContent = cat.nome_categoria || cat.nome;
        selectCategorias.appendChild(opt);
      });
    } catch(err){
      exibirMensagem('danger','Não foi possível carregar categorias');
      console.error(err);
    }
  }

  // ===== Preview da imagem =====
  inputImagem.addEventListener('change', () => {
    const file = inputImagem.files[0];
    if(file){
      const reader = new FileReader();
      reader.onload = e => {
        previewImagem.src = e.target.result;
        previewImagem.style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else {
      previewImagem.src = '';
      previewImagem.style.display = 'none';
    }
  });

  // ===== Pegar ID do produto da URL =====
  const params = new URLSearchParams(window.location.search);
  const produtoId = params.get('id');

  if(produtoId){
    (async ()=>{
      try {
        const produto = await fetchJSON(`../php/editar_produto.php?id=${produtoId}`);
        formProduto.dataset.editando = produtoId;
        formProduto.nome.value = produto.nome || '';
        formProduto.preco.value = produto.preco || '';
        formProduto.descricao.value = produto.descricao || '';
        formProduto.categoria_id.value = produto.categoria_id || '';
        if(produto.imagem){
          previewImagem.src = produto.imagem;
          previewImagem.style.display = 'block';
        }
      } catch(err){
        exibirMensagem('danger','Erro ao carregar produto');
        console.error(err);
      }
    })();
  } else {
    exibirMensagem('warning','ID do produto não informado');
  }

  // ===== Submit =====
  formProduto.addEventListener('submit', async e=>{
    e.preventDefault();
    const formData = new FormData(formProduto);
    if(formProduto.dataset.editando) formData.append('id', formProduto.dataset.editando);

    try {
      const res = await fetch('../php/editar_produto.php',{ method:'POST', body:formData });
      const data = await res.json();
      if(data.sucesso){
        exibirMensagem('success', data.mensagem);
      } else {
        exibirMensagem('danger', data.erro || 'Erro ao atualizar produto');
      }
    } catch(err){
      exibirMensagem('danger','Erro na requisição');
      console.error(err);
    }
  });

  carregarCategorias();
});
