document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-faixa');
  const container = document.getElementById('faixas-container');
  const mensagem = document.getElementById('mensagem-container');
  const idField = document.getElementById('id');
  const nomeField = document.getElementById('nome_faixa');
  const valorField = document.getElementById('valor');
  const setorField = document.getElementById('setor');
  const ativoField = document.getElementById('ativo');

  // Função para exibir mensagens
  function exibirMensagem(tipo, texto) {
    mensagem.innerHTML = `
      <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
        ${texto}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>`;
    setTimeout(() => mensagem.innerHTML = '', 4000);
  }

  // Carregar faixas de entrega
  async function carregarFaixas() {
    try {
      const res = await fetch('../php/taxa_entrega.php?acao=listar', { credentials: 'include' });
      if (!res.ok) throw new Error('Erro ao buscar faixas');
      const data = await res.json();

      container.innerHTML = '';
      if (!data.faixas || data.faixas.length === 0) {
        container.innerHTML = '<div class="text-muted text-center">Nenhuma faixa cadastrada.</div>';
        return;
      }

      data.faixas.forEach(f => {
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex justify-content-between align-items-center';
        item.innerHTML = `
          <div>
            <strong>${f.nome_faixa}</strong><br>
            <small>Valor: R$ ${parseFloat(f.valor).toFixed(2)}</small><br>
            <small>Setor: ${f.setor || '-'}</small><br>
            <small>Status: ${f.ativo == 1 ? 'Ativo' : 'Inativo'}</small>
          </div>
          <div>
            <button class="btn btn-sm btn-warning me-2"
              data-id="${f.id}"
              data-nome="${f.nome_faixa}"
              data-valor="${f.valor}"
              data-setor="${f.setor}"
              data-ativo="${f.ativo}">Editar</button>
            <button class="btn btn-sm btn-danger" data-id="${f.id}">Excluir</button>
          </div>
        `;
        container.appendChild(item);
      });
    } catch (err) {
      console.error(err);
      container.innerHTML = '<div class="text-danger text-center">Erro ao carregar faixas.</div>';
    }
  }

  // Cadastrar ou editar faixa
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.append('acao', 'salvar');

    try {
      const res = await fetch('../php/taxa_entrega.php', { method: 'POST', body: formData, credentials: 'include' });
      if (!res.ok) throw new Error('Erro ao salvar faixa');
      const data = await res.json();

      if (data.erro) exibirMensagem('danger', data.erro);
      else {
        exibirMensagem('success', data.mensagem);
        form.reset();
        idField.value = '';
        carregarFaixas();
      }
    } catch (err) {
      console.error(err);
      exibirMensagem('danger', 'Erro ao salvar faixa.');
    }
  });

  // Delegação de eventos para editar/excluir
  container.addEventListener('click', (e) => {
    const btn = e.target;

    // Editar
    if (btn.classList.contains('btn-warning')) {
      idField.value = btn.dataset.id;
      nomeField.value = btn.dataset.nome;
      valorField.value = btn.dataset.valor;
      setorField.value = btn.dataset.setor;
      ativoField.checked = btn.dataset.ativo == 1;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Excluir
    if (btn.classList.contains('btn-danger')) {
      if (!confirm('Deseja realmente excluir esta faixa?')) return;

      const formData = new FormData();
      formData.append('acao', 'excluir');
      formData.append('id', btn.dataset.id);

      fetch('../php/taxa_entrega.php', { method: 'POST', body: formData, credentials: 'include' })
        .then(res => res.json())
        .then(data => {
          if (data.erro) exibirMensagem('danger', data.erro);
          else {
            exibirMensagem('success', data.mensagem);
            carregarFaixas();
          }
        })
        .catch(err => {
          console.error(err);
          exibirMensagem('danger', 'Erro ao excluir faixa.');
        });
    }
  });

  // Inicializa carregamento
  carregarFaixas();
});
