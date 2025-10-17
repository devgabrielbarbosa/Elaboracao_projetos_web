document.addEventListener('DOMContentLoaded', () => {
  const formPromocao = document.getElementById('form-promocao');
  const mensagemContainer = document.getElementById('mensagem-container');
  const listaProdutos = document.getElementById('lista-produtos');
  const containerPromocoes = document.getElementById('promocoes-container');

  let produtosSelecionados = [];

  // ===== Função para exibir mensagem =====
  function exibirMensagem(tipo, texto) {
    mensagemContainer.innerHTML = `
      <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
        ${texto}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>`;
    setTimeout(() => mensagemContainer.innerHTML = '', 5000);
  }

  // ===== Carregar promoções e produtos =====
  async function carregarDados() {
    listaProdutos.innerHTML = '<p class="text-center text-muted">Carregando produtos...</p>';
    containerPromocoes.innerHTML = '<p class="text-center text-muted">Carregando promoções...</p>';

    try {
      const res = await fetch('../php/promocoes_api.php', { credentials: 'include' });
      const data = await res.json();

      if (data.erro) {
        exibirMensagem('danger', data.erro);
        return;
      }

      // ===== Listar produtos da loja =====
      if (data.produtos?.length) {
        listaProdutos.innerHTML = '';
        data.produtos.forEach(prod => {
          const div = document.createElement('div');
          div.className = 'd-flex justify-content-between align-items-center mb-2';
          div.innerHTML = `
            <span>${prod.nome} - R$ ${parseFloat(prod.preco).toFixed(2)}</span>
            <button class="btn btn-sm btn-outline-success">+</button>
          `;
          const btn = div.querySelector('button');
          btn.addEventListener('click', () => adicionarProduto(prod));
          listaProdutos.appendChild(div);
        });
      } else {
        listaProdutos.innerHTML = '<p class="text-center text-muted">Nenhum produto cadastrado.</p>';
      }

      // ===== Listar promoções =====
      if (data.promocoes?.length) {
        containerPromocoes.innerHTML = '';
        data.promocoes.forEach(p => {
          const ativoClass = p.ativo == 1 ? 'bg-success' : 'bg-secondary';
          const ativoText = p.ativo == 1 ? 'Ativa' : 'Inativa';
          const imgSrc = p.imagem || 'https://via.placeholder.com/200x150?text=Sem+Imagem';

          const div = document.createElement('div');
          div.className = 'col-md-4 mb-4';
          div.innerHTML = `
            <div class="card shadow-sm h-100">
              <img src="${imgSrc}" class="card-img-top" alt="${p.codigo}" style="height:180px; object-fit:cover;">
              <div class="card-body d-flex flex-column justify-content-between">
                <div>
                  <h5 class="card-title fw-semibold">${p.codigo}</h5>
                  <p class="text-muted small mb-1">${p.descricao || ''}</p>
                  <p class="fw-bold text-danger mb-1">Desconto: ${parseFloat(p.desconto).toFixed(2)}%</p>
                  <span class="badge ${ativoClass}">${ativoText}</span>
                </div>
                <div class="d-flex justify-content-between mt-3">
                  <button class="btn btn-sm ${p.ativo == 1 ? 'btn-warning' : 'btn-success'}" 
                    onclick="togglePromocao(${p.id})">
                    ${p.ativo == 1 ? 'Desativar' : 'Ativar'}
                  </button>
                  <button class="btn btn-sm btn-danger" onclick="deletarPromocao(${p.id})">Excluir</button>
                </div>
              </div>
            </div>`;
          containerPromocoes.appendChild(div);
        });
      } else {
        containerPromocoes.innerHTML = '<p class="text-center text-muted">Nenhuma promoção cadastrada.</p>';
      }

    } catch (err) {
      console.error(err);
      exibirMensagem('danger', 'Erro ao carregar dados.');
    }
  }

  // ===== Adicionar produto à seleção =====
  function adicionarProduto(prod) {
    if (produtosSelecionados.find(p => p.id === prod.id)) return;
    produtosSelecionados.push({ id: prod.id, preco_original: prod.preco });
    renderProdutosSelecionados();
  }

  // ===== Renderizar produtos selecionados =====
  function renderProdutosSelecionados() {
    let html = '';
    if (produtosSelecionados.length) {
      html += '<h6 class="mt-3">Produtos Selecionados:</h6>';
      html += '<ul class="list-group mb-3">';
      produtosSelecionados.forEach((p, idx) => {
        html += `<li class="list-group-item d-flex justify-content-between align-items-center">
          ${p.id} - R$ ${parseFloat(p.preco_original).toFixed(2)}
          <button type="button" class="btn btn-sm btn-danger">x</button>
        </li>`;
      });
      html += '</ul>';
    }
    document.getElementById('produtos-selecionados')?.remove();
    const div = document.createElement('div');
    div.id = 'produtos-selecionados';
    div.innerHTML = html;
    formPromocao.insertBefore(div, formPromocao.querySelector('button[type="submit"]'));

    // Botões de remover
    div.querySelectorAll('button').forEach((btn, idx) => {
      btn.addEventListener('click', () => {
        produtosSelecionados.splice(idx, 1);
        renderProdutosSelecionados();
      });
    });
  }

  // ===== Enviar formulário =====
  formPromocao.addEventListener('submit', async e => {
    e.preventDefault();
    if (!produtosSelecionados.length) {
      exibirMensagem('danger', 'Selecione pelo menos um produto.');
      return;
    }

    const formData = new FormData(formPromocao);
    formData.append('acao', 'adicionar');
    formData.append('produtos', JSON.stringify(produtosSelecionados));

    try {
      const res = await fetch('../php/promocoes_api.php', { method: 'POST', body: formData, credentials: 'include' });
      const data = await res.json();

      if (data.erro) exibirMensagem('danger', data.erro);
      else {
        exibirMensagem('success', data.mensagem || 'Promoção cadastrada com sucesso.');
        formPromocao.reset();
        produtosSelecionados = [];
        renderProdutosSelecionados();
        carregarDados();
      }

    } catch (err) {
      console.error(err);
      exibirMensagem('danger', 'Erro ao cadastrar promoção.');
    }
  });

  // ===== Toggle promoção =====
  window.togglePromocao = async (id) => {
    const formData = new FormData();
    formData.append('acao', 'toggle');
    formData.append('id', id);

    try {
      const res = await fetch('../php/promocoes_api.php', { method: 'POST', body: formData, credentials: 'include' });
      const data = await res.json();
      if (data.erro) exibirMensagem('danger', data.erro);
      else exibirMensagem('success', data.mensagem || 'Status atualizado.');
      carregarDados();
    } catch (err) {
      console.error(err);
      exibirMensagem('danger', 'Erro ao atualizar status.');
    }
  };

  // ===== Deletar promoção =====
  window.deletarPromocao = async (id) => {
    if (!confirm('Deseja realmente excluir esta promoção?')) return;
    const formData = new FormData();
    formData.append('acao', 'excluir');
    formData.append('id', id);

    try {
      const res = await fetch('../php/promocoes_api.php', { method: 'POST', body: formData, credentials: 'include' });
      const data = await res.json();
      if (data.erro) exibirMensagem('danger', data.erro);
      else exibirMensagem('success', data.mensagem || 'Promoção excluída com sucesso.');
      carregarDados();
    } catch (err) {
      console.error(err);
      exibirMensagem('danger', 'Erro ao excluir promoção.');
    }
  };

  // ===== Inicialização =====
  carregarDados();
});
