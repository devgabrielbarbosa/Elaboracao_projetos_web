document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('formasContainer');
  const formAdd = document.getElementById('formAdicionar');
  const formEdit = document.getElementById('formEditar');
  const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));

  // ======== Função auxiliar de requisições ========
  async function fetchJSON(url, options = {}) {
    try {
      const res = await fetch(url, { credentials: 'same-origin', ...options });
      return await res.json();
    } catch (e) {
      console.error(e);
      alert('Erro de conexão com o servidor.');
      return null;
    }
  }

  // ======== Listar formas de pagamento ========
  async function listarFormas() {
    const dados = await fetchJSON('../php/formas_pagamento.php?acao=listar');
    if (!dados || !dados.formas) return;
    container.innerHTML = '';

    if (dados.formas.length === 0) {
      container.innerHTML = '<div class="alert alert-secondary text-center mt-3">Nenhuma forma cadastrada.</div>';
      return;
    }

    dados.formas.forEach(f => {
      const ativoBadge = f.ativo == 1
        ? '<span class="badge bg-success">Ativo</span>'
        : '<span class="badge bg-secondary">Inativo</span>';

      const tipoIcone = {
        dinheiro: '💵',
        cartao: '💳',
        pix: '⚡'
      }[f.tipo] || '💰';

      const div = document.createElement('div');
      div.className = 'card mb-2';
      div.innerHTML = `
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <strong>${tipoIcone} ${f.nome}</strong> - ${f.tipo.toUpperCase()} ${ativoBadge}<br>
            ${f.tipo === 'pix' && f.chave_pix ? `<small class="text-muted">Chave Pix: ${f.chave_pix}</small><br>` : ''}
            ${f.responsavel_nome ? `<small>Resp: ${f.responsavel_nome}</small><br>` : ''}
            ${f.responsavel_conta ? `<small>Conta: ${f.responsavel_conta}</small><br>` : ''}
            ${f.responsavel_doc ? `<small>Doc: ${f.responsavel_doc}</small>` : ''}
          </div>
          <div>
            <button class="btn btn-sm btn-outline-primary me-1" data-id="${f.id}" data-acao="editar">✏️</button>
            <button class="btn btn-sm btn-outline-warning me-1" data-id="${f.id}" data-acao="toggle">${f.ativo == 1 ? 'Desativar' : 'Ativar'}</button>
            <button class="btn btn-sm btn-outline-danger" data-id="${f.id}" data-acao="excluir">🗑️</button>
          </div>
        </div>
      `;
      container.appendChild(div);
    });
  }

  // ======== Adicionar forma ========
  formAdd.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(formAdd);
    formData.append('acao', 'adicionar');

    const dados = await fetchJSON('../php/formas_pagamento.php', {
      method: 'POST',
      body: formData
    });

    if (dados?.sucesso) {
      formAdd.reset();
      listarFormas();
    } else if (dados?.erro) {
      alert(dados.erro);
    }
  });

  // ======== Abrir modal de edição ========
  container.addEventListener('click', async e => {
    const btn = e.target.closest('button');
    if (!btn) return;
    const id = btn.dataset.id;
    const acao = btn.dataset.acao;

    if (acao === 'editar') {
// Ativar/Desativar
     const dados = await fetchJSON(`../php/formas_pagamento.php?acao=toggle&id=${id}`);
      const forma = dados.formas.find(f => f.id == id);
      if (!forma) return alert('Forma não encontrada.');

      formEdit.id.value = forma.id;
      formEdit.editar_nome.value = forma.nome;
      formEdit.tipo.value = forma.tipo;
      formEdit.chave_pix.value = forma.chave_pix || '';
      formEdit.responsavel_nome.value = forma.responsavel_nome || '';
      formEdit.responsavel_conta.value = forma.responsavel_conta || '';
      formEdit.responsavel_doc.value = forma.responsavel_doc || '';

      modalEditar.show();
    }

    // ======== Ativar/Desativar ========
    if (acao === 'toggle') {
      const confirma = confirm('Deseja alterar o status desta forma de pagamento?');
      if (!confirma) return;
      const dados = await fetchJSON(`../php/formas_pagamento.php?acao=toggle&id=${id}`);
      if (dados?.sucesso) listarFormas();
    }

    // ======== Excluir ========
    if (acao === 'excluir') {
      const confirma = confirm('Tem certeza que deseja excluir esta forma?');
      if (!confirma) return;
    
// Excluir
const dados = await fetchJSON(`../php/formas_pagamento.php?acao=excluir&id=${id}`);
      if (dados?.sucesso) listarFormas();
    }
  });

  // ======== Editar forma ========
  formEdit.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(formEdit);
    formData.append('acao', 'editar');

    const dados = await fetchJSON('../php/formas_pagamento.php', {
      method: 'POST',
      body: formData
    });

    if (dados?.sucesso) {
      modalEditar.hide();
      listarFormas();
    } else if (dados?.erro) {
      alert(dados.erro);
    }
  });

  // ======== Carrega lista ao abrir ========
  listarFormas();
});
