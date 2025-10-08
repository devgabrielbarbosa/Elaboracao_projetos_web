
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('promocoesContainer');
  const form = document.getElementById('formPromocao');

  // ======== Adicionar promoção ========
  if (form) {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const formData = new FormData(form);
      formData.append('acao', 'adicionar');

      try {
        const res = await fetch('../php/promocoes_api.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });
        const data = await res.json();

        if (data.erro) {
          alert(data.erro);
          return;
        }
        alert(data.mensagem || 'Promoção cadastrada!');
        form.reset();
        carregarPromocoes();
      } catch (err) {
        console.error('Erro ao enviar promoção:', err);
        alert('Erro ao cadastrar promoção.');
      }
    });
  }

  // ======== Carregar promoções ========
  async function carregarPromocoes() {
    container.innerHTML = '<p class="text-center text-muted">Carregando promoções...</p>';
    try {
      const res = await fetch('../php/promocoes_api.php', { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Resposta do servidor: ' + res.status);
      const data = await res.json();

      if (data.erro) {
        container.innerHTML = `<p class="text-center text-danger">${data.erro}</p>`;
        return;
      }
      if (!data.promocoes || data.promocoes.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">Nenhuma promoção cadastrada.</p>';
        return;
      }

      container.innerHTML = '';
      data.promocoes.forEach(p => {
        const ativo = p.ativo == 1;
        const ativoClass = ativo ? 'bg-success' : 'bg-secondary';
        const ativoText = ativo ? 'Ativa' : 'Inativa';

        const card = document.createElement('div');
        card.className = 'col-md-4 mb-4';
        card.innerHTML = `
          <div class="card h-100 shadow-sm border-0 card-promocao">
            <img src="${p.imagem}" class="banner-img" alt="${p.codigo}">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-primary fw-bold">
                ${p.codigo} <span class="badge ${ativoClass} ms-2">-${p.desconto}%</span>
              </h5>
              <p class="card-text text-muted">${p.descricao || ''}</p>
              <p class="mb-2"><small>${p.data_inicio || ''} até ${p.data_fim || ''}</small></p>
              <div class="mt-auto d-flex justify-content-between align-items-center">
                <button class="btn btn-sm btn-outline-${ativo ? 'warning' : 'success'} btn-toggle">
                  ${ativo ? 'Desativar' : 'Ativar'}
                </button>
                <button class="btn btn-sm btn-outline-danger btn-delete">Excluir</button>
              </div>
            </div>
          </div>`;

        // ======== Botão ativar/desativar ========
        const btnToggle = card.querySelector('.btn-toggle');
        btnToggle.addEventListener('click', async () => {
          const acao = ativo ? 'desativar' : 'ativar';
          try {
            const r = await fetch(`../php/promocoes_api.php?acao=${acao}&id=${p.id}`);
            const resp = await r.json();
            alert(resp.mensagem || resp.erro || 'OK');
            carregarPromocoes();
          } catch (e) {
            alert('Erro ao atualizar status da promoção.');
          }
        });

        // ======== Botão excluir ========
        const btnDelete = card.querySelector('.btn-delete');
        btnDelete.addEventListener('click', async () => {
          if (!confirm(`Deseja realmente excluir a promoção "${p.codigo}"?`)) return;
          try {
            const r = await fetch(`../php/promocoes_api.php?acao=deletar&id=${p.id}`);
            const resp = await r.json();
            alert(resp.mensagem || resp.erro || 'OK');
            carregarPromocoes();
          } catch (e) {
            alert('Erro ao excluir promoção.');
          }
        });

        container.appendChild(card);
      });
    } catch (err) {
      console.error('Erro ao carregar promoções:', err);
      container.innerHTML = '<p class="text-center text-danger">Erro ao carregar promoções. Veja console.</p>';
    }
  }

  // inicializar ao abrir a página
  if (container) carregarPromocoes();
});

