// promocoes.js — versão BLOB com excluir/ativar funcionando e mensagens amigáveis
document.addEventListener('DOMContentLoaded', () => {
  const formPromocao = document.getElementById('form-promocao');
  const container = document.getElementById('promocoes-container');
  const msgContainer = document.getElementById('mensagem-container');

  if (!container) return console.error('Elemento #promocoes-container NÃO encontrado.');
  if (!formPromocao) console.warn('Elemento #form-promocao NÃO encontrado.');

  // ===== Função para mostrar mensagens =====
  function mostrarMensagem(texto, tipo = 'info') {
    if (msgContainer) {
      msgContainer.innerHTML = `<div class="alert alert-${tipo}">${texto}</div>`;
    }
  }

  // ===== Função para carregar promoções =====
  async function carregarPromocoes() {
    container.innerHTML = '<p class="text-center text-muted">Carregando promoções...</p>';
    try {
      const res = await fetch('../php/promocoes_api.php', { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Resposta do servidor: ' + res.status);
      const data = await res.json();

      if (data.erro) return container.innerHTML = `<p class="text-center text-danger">${data.erro}</p>`;
      if (!data.promocoes || data.promocoes.length === 0) return container.innerHTML = '<p class="text-center text-muted">Nenhuma promoção cadastrada.</p>';

      container.innerHTML = '';
      data.promocoes.forEach(p => {
        const ativoClass = p.ativo == 1 ? 'bg-success' : 'bg-secondary';
        const ativoText = p.ativo == 1 ? 'Ativa' : 'Inativa';
        const imgSrc = p.imagem_blob 
          ? `data:${p.imagem_tipo};base64,${p.imagem_blob}` 
          : 'https://via.placeholder.com/350x180?text=Sem+Imagem';

        const card = document.createElement('div');
        card.className = 'col-md-4 mb-4';
        card.innerHTML = `
          <div class="card h-100 shadow-sm border-0 card-promocao">
            <img src="${imgSrc}" class="banner-img" alt="${p.codigo}">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-primary fw-bold">
                ${p.codigo} <span class="badge ${ativoClass} ms-2">-${p.desconto}%</span>
              </h5>
              <p class="card-text text-muted">${p.descricao}</p>
              <p class="mb-2"><small>${p.data_inicio} até ${p.data_fim}</small></p>
              <div class="mt-auto d-flex justify-content-between align-items-center">
                <a href="#" class="badge ${ativoClass} text-decoration-none p-2">${ativoText}</a>
                <div>
                  <button type="button" class="btn btn-sm btn-warning me-2 btn-toggle" data-id="${p.id}">
                    ${p.ativo == 1 ? 'Desativar' : 'Ativar'}
                  </button>
                  <button type="button" class="btn btn-sm btn-danger btn-excluir" data-id="${p.id}">
                    Excluir
                  </button>
                </div>
              </div>
            </div>
          </div>`;
        container.appendChild(card);
      });

    } catch (err) {
      console.error('Erro ao carregar promoções:', err);
      container.innerHTML = '<p class="text-center text-danger">Erro ao carregar promoções. Veja console.</p>';
    }
  }

  // ===== Submit do formulário =====
  if (formPromocao) {
    formPromocao.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(formPromocao);
      formData.append('acao', 'adicionar');

      try {
        const res = await fetch('../php/promocoes_api.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } 
        catch { return mostrarMensagem('Resposta inválida do servidor.', 'danger'); }

        if (data.erro) mostrarMensagem(data.erro, 'danger');
        else if (data.mensagem) {
          mostrarMensagem(data.mensagem, 'success');
          formPromocao.reset();
          carregarPromocoes();
        } else mostrarMensagem('Resposta inesperada do servidor.', 'info');

      } catch (err) {
        console.error('Erro no fetch do cadastro:', err);
        mostrarMensagem('Erro ao cadastrar promoção. Veja console.', 'danger');
      }
    });
  }

  // ===== Delegation: toggle/excluir =====
  container.addEventListener('click', async (e) => {
    const toggleBtn = e.target.closest('.btn-toggle');
    const excluirBtn = e.target.closest('.btn-excluir');

    if (toggleBtn) {
      e.preventDefault();
      const id = toggleBtn.dataset.id;
      try {
        const res = await fetch('../php/promocoes_api.php', {
          method: 'POST',
          body: new URLSearchParams({ acao: 'toggle', id }),
          credentials: 'same-origin'
        });
        const data = await res.json();
        data.erro ? mostrarMensagem(data.erro, 'danger') : mostrarMensagem(data.mensagem, 'success');
        carregarPromocoes();
      } catch (err) {
        console.error(err);
        mostrarMensagem('Erro ao atualizar status.', 'danger');
      }
    }

    if (excluirBtn) {
      e.preventDefault();
      const id = excluirBtn.dataset.id;
      if (!confirm('Deseja realmente excluir esta promoção?')) return;
      try {
        const res = await fetch('../php/promocoes_api.php', {
          method: 'POST',
          body: new URLSearchParams({ acao: 'excluir', id }),
          credentials: 'same-origin'
        });
        const data = await res.json();
        data.erro ? mostrarMensagem(data.erro, 'danger') : mostrarMensagem(data.mensagem, 'success');
        carregarPromocoes();
      } catch (err) {
        console.error(err);
        mostrarMensagem('Erro ao excluir promoção.', 'danger');
      }
    }
  });

  // Carregar promoções ao abrir
  carregarPromocoes();
});
