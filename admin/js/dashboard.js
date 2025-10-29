document.addEventListener('DOMContentLoaded', () => {
  // ===== ELEMENTOS DO DASHBOARD =====
  const faturamentoEl = document.getElementById('faturamento');
  const entreguesEl = document.getElementById('entregues');
  const andamentoEl = document.getElementById('andamento');
  const canceladosEl = document.getElementById('cancelados');
  const clientesEl = document.getElementById('clientes');
  const produtosEl = document.getElementById('produtos');
  const ultimosEl = document.getElementById('ultimosPedidos');
  const graficoCanvas = document.getElementById('graficoFaturamento');
  const linkInput = document.getElementById('linkCardapio');
  const btnCopiar = document.getElementById('btnCopiarLink');

  let chartInstance = null;

  // ===== FORMATADOR DE MOEDA =====
  const formatBRL = value => Number(value || 0).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  });

  // ===== FETCH SEGURO EM JSON =====
  async function fetchJSON(url, options = {}) {
    try {
      const res = await fetch(url, { credentials: 'include', ...options });
      const text = await res.text();

      if (!res.ok) throw new Error(`HTTP ${res.status} - ${text}`);
      return JSON.parse(text);
    } catch (err) {
      console.error('Erro no fetchJSON:', err);
      throw err;
    }
  }

  // ===== FUNÇÃO PRINCIPAL =====
  async function carregarDashboard() {
    try {
        const res = await fetch('../php/verificar_sessao.php', { credentials: 'include' });
    const data = await res.json();
   if (!data.slug) {
      linkInput.value = 'Slug da loja não encontrado.';
      return;
    }

      if (data.erro) {
        alert(data.erro);
        window.location.href = '../paginas/login.html';
        return;
      }

      // ===== ATUALIZA CARDS =====
      faturamentoEl.textContent = formatBRL(data.totais?.faturamento);
      entreguesEl.textContent = data.totais?.entregues || 0;
      andamentoEl.textContent = data.totais?.andamento || 0;
      canceladosEl.textContent = data.totais?.cancelados || 0;
      clientesEl.textContent = data.totais?.clientes || 0;
      produtosEl.textContent = data.totais?.produtos || 0;

      // ===== ÚLTIMOS PEDIDOS =====
      ultimosEl.innerHTML = '';
      const pedidos = data.ultimosPedidos || [];
      if (!pedidos.length) {
        ultimosEl.innerHTML = '<li class="list-group-item text-muted small">Nenhum pedido recente</li>';
      } else {
        pedidos.forEach(p => {
          const total = (Number(p.total) + Number(p.taxa_entrega || 0)).toFixed(2);
          const dataPedido = new Date(p.data_criacao);
          const li = document.createElement('li');
          li.className = 'list-group-item d-flex justify-content-between align-items-start';
          li.innerHTML = `
            <div>
              <strong>#${p.id}</strong> — ${formatBRL(total)}
              <div class="small text-muted">
                ${p.metodo_pagamento || 'Pagamento não informado'} — 
                ${dataPedido.toLocaleString('pt-BR', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit' })}
              </div>
            </div>
            <span class="badge ${
              p.status === 'entregue' ? 'bg-success' :
              p.status === 'cancelado' ? 'bg-danger' : 'bg-warning text-dark'
            }">${p.status}</span>
          `;
          ultimosEl.appendChild(li);
        });
      }

      // ===== GRÁFICO =====
      if (graficoCanvas) {
        const ctx = graficoCanvas.getContext('2d');
        if (chartInstance) chartInstance.destroy();
        chartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: data.labelsGrafico || [],
            datasets: [{
              label: 'Faturamento (R$)',
              data: data.valoresGrafico || [],
              borderColor: 'rgb(220,53,69)',
              backgroundColor: 'rgba(220,53,69,0.15)',
              fill: true,
              tension: 0.25
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => formatBRL(v) } } }
          }
        });
      }

      // ===== LINK DO CARDÁPIO =====
         // Monta link do cardápio com slug — garantidamente sem loja_id
    const linkLoja = `${window.location.origin}/projeto_web/cliente/login.html?loja=${encodeURIComponent(data.slug)}`;
    linkInput.value = linkLoja;

    // Copiar link
    btnCopiar.onclick = async () => {
      try {
        await navigator.clipboard.writeText(linkLoja);
        alert('Link do cardápio copiado!');
      } catch {
        alert('Não foi possível copiar o link.');
      }
    };

  } catch (err) {
    console.error('Erro ao gerar link do cardápio:', err);
    linkInput.value = 'Erro ao gerar link.';
  }

  }

  // ===== INICIALIZA DASHBOARD =====
  carregarDashboard();
});
