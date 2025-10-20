document.addEventListener('DOMContentLoaded', () => {
  const formProduto = document.getElementById('formProduto');
  const formCategoria = document.getElementById('formCategoria');
  const containerProdutos = document.getElementById('containerProdutos');
  const listaCategorias = document.getElementById('listaCategorias');
  const selectCategorias = document.getElementById('selectCategorias');
  const mensagem = document.getElementById('mensagem');
  const inputImagem = document.getElementById('inputImagem');
  const previewImagem = document.getElementById('previewImagem');

  if (!formProduto || !formCategoria || !containerProdutos) {
    console.error('Elementos HTML não encontrados.');
    return;
  }

  // ===== Função para exibir mensagens =====
  function exibirMensagem(tipo, texto) {
    mensagem.innerHTML = `
      <div class="alert alert-${tipo} alert-dismissible fade show shadow-sm" role="alert">
        ${texto}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>`;
    setTimeout(() => mensagem.innerHTML = '', 5000);
  }

  // ===== Função fetchJSON =====
  async function fetchJSON(url, options = {}) {
    try {
      const res = await fetch(url, options);
      const text = await res.text();
      try {
        return JSON.parse(text);
      } catch {
        console.error('Resposta não é JSON:', text);
        return { erro: 'Resposta inválida do servidor' };
      }
    } catch (err) {
      console.error('Erro ao conectar ao servidor:', err);
      return { erro: 'Falha na conexão' };
    }
  }

  // ===== Preview da Imagem =====
  inputImagem.addEventListener('change', () => {
    const file = inputImagem.files[0];
    if (file) {
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

  // ===== CATEGORIAS =====
  async function carregarCategorias() {
    const data = await fetchJSON('../php/categorias_lojas.php?acao=listar');
    if (data.erro) return exibirMensagem('danger', data.erro);

    selectCategorias.innerHTML = '<option value="">Selecione a categoria</option>';
    listaCategorias.innerHTML = '';

    data.categorias.forEach(cat => {
      // Select
      if (cat.ativo == 1) {
        const opt = document.createElement('option');
        opt.value = cat.id;
        opt.textContent = cat.nome_categoria || cat.nome;
        selectCategorias.appendChild(opt);
      }

      // Lista no modal
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center';
      li.innerHTML = `
        ${cat.nome_categoria || cat.nome}
        <div>
          <span class="badge ${cat.ativo == 1 ? 'bg-success' : 'bg-secondary'} me-2">
            ${cat.ativo == 1 ? 'Ativa' : 'Inativa'}
          </span>
          <button class="btn btn-sm btn-outline-danger btnExcluir" data-id="${cat.id}">
            Excluir
          </button>
        </div>`;
      listaCategorias.appendChild(li);
    });

    // Botões de exclusão
    listaCategorias.querySelectorAll('.btnExcluir').forEach(btn => {
      btn.addEventListener('click', async () => {
        if (!confirm('Deseja realmente excluir esta categoria?')) return;
        const id = btn.dataset.id;
        const res = await fetchJSON(`../php/categorias_lojas.php?acao=deletar&id=${id}`);
        if (res.sucesso) exibirMensagem('success', res.mensagem || res.sucesso);
        else exibirMensagem('danger', res.erro || 'Erro ao deletar categoria.');
        carregarCategorias();
      });
    });
  }

  // ===== Adicionar Categoria =====
  formCategoria.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(formCategoria);
    formData.append('acao', 'adicionar');

    const res = await fetchJSON('../php/categorias_lojas.php', { method: 'POST', body: formData });
    if (res.sucesso) exibirMensagem('success', res.mensagem || res.sucesso);
    else exibirMensagem('danger', res.erro || 'Erro ao adicionar categoria.');

    formCategoria.reset();
    carregarCategorias();
  });

  // ===== PRODUTOS =====
  async function carregarProdutos() {
    containerProdutos.innerHTML = '<p class="text-center text-muted">Carregando produtos...</p>';
    const data = await fetchJSON('../php/produtos_lojas.php?acao=listar');

    if (data.erro) {
      containerProdutos.innerHTML = `<p class="text-danger text-center">${data.erro}</p>`;
      return;
    }

    if (!data.produtos?.length) {
      containerProdutos.innerHTML = '<p class="text-center text-muted">Nenhum produto cadastrado.</p>';
      return;
    }

    containerProdutos.innerHTML = '';
    data.produtos.forEach(p => {
      const ativoClass = p.ativo == 1 ? 'bg-success' : 'bg-secondary';
      const ativoText = p.ativo == 1 ? 'Ativo' : 'Pausado';
      const imgSrc = p.imagem || 'https://via.placeholder.com/200x150?text=Sem+Imagem';

      const col = document.createElement('div');
      col.className = 'col-md-4 mb-4';
      col.innerHTML = `
        <div class="card card-produto card shadow-sm h-100">
          <img  src="${imgSrc}" class="card-img-top" alt="${p.nome}" style="height:180px; object-fit:cover;">
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <h5 class="card-title fw-semibold">${p.nome}</h5>
              <p class="text-muted small mb-1">${p.descricao || ''}</p>
              <p class="fw-bold text-danger mb-1">R$ ${parseFloat(p.preco || 0).toFixed(2).replace('.', ',')}</p>
              <p class="small text-secondary mb-2">Categoria: ${p.categoria_nome || 'Sem categoria'}</p>
              <span class="badge ${ativoClass}">${ativoText}</span>
            </div>
            <div class="d-flex justify-content-between mt-3">
              <button class="btn btn-sm ${p.ativo == 1 ? 'btn-warning' : 'btn-success'}"
                onclick="alterarStatus(${p.id}, '${p.ativo == 1 ? 'pausar' : 'ativar'}')">
                ${p.ativo == 1 ? 'Pausar' : 'Ativar'}
              </button>
              <a href="produtos_editar.html?id=${p.id}" class="btn btn-sm btn-primary">Editar</a>
              <button class="btn btn-sm btn-danger" onclick="deletarProduto(${p.id})">Excluir</button>
            </div>
          </div>
        </div>`;
      containerProdutos.appendChild(col);
    });
  }

  // ===== Adicionar Produto =====
  formProduto.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(formProduto);
    formData.append('acao', 'adicionar');

    if (!formData.get('categoria_id')) formData.set('categoria_id', '');

    const data = await fetchJSON('../php/produtos_lojas.php', { method: 'POST', body: formData });
    if (data.sucesso) exibirMensagem('success', data.sucesso);
    else exibirMensagem('danger', data.erro || 'Erro ao adicionar produto.');

    formProduto.reset();
    previewImagem.src = '';
    previewImagem.style.display = 'none';
    carregarProdutos();
  });

  // ===== Alterar Status =====
  window.alterarStatus = async (id, acao) => {
    const data = await fetchJSON(`../php/produtos_lojas.php?acao=${acao}&id=${id}`);
    if (data.sucesso) exibirMensagem('success', data.sucesso);
    else exibirMensagem('danger', data.erro || 'Erro ao alterar status.');
    carregarProdutos();
  };

  // ===== Deletar Produto =====
  window.deletarProduto = async id => {
    if (!confirm('Deseja realmente excluir este produto?')) return;
    const data = await fetchJSON(`../php/produtos_lojas.php?acao=deletar&id=${id}`);
    if (data.sucesso) exibirMensagem('success', data.sucesso);
    else exibirMensagem('danger', data.erro || 'Erro ao excluir produto.');
    carregarProdutos();
  };

  // ===== Inicialização =====
  carregarCategorias();
  carregarProdutos();
});
