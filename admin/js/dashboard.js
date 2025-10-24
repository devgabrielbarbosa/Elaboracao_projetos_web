
document.addEventListener('DOMContentLoaded', () => {
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

  // util: formatar moeda em pt-BR
  function formatBRL(value) {
    return Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  }

  // util: safe text (pequeno)
  function safeText(v) {
    return v === null || v === undefined ? '' : String(v);
  }

  async function fetchJSON(url, options = {}) {
    try {
      const res = await fetch(url, { credentials: 'same-origin', cache: 'no-store', ...options });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.json();
    } catch (err) {
      console.error('fetchJSON erro:', err);
      throw err;
    }
  }

  // Preenche cards, lista e gráfico
  async function carregarDashboard() {
    try {
      const data = await fetchJSON('../php/dashboard_data.php');

      if (data.erro) {
        alert(data.erro);
        return;
      }

      // Link do cardápio (gera a partir do loja_id vindo do PHP)
      if (data.loja_id) {
        const lojaId = data.loja_id;
        const linkLoja = `${window.location.origin}/cliente/index.html?loja_id=${encodeURIComponent(lojaId)}`;
        if (linkInput) linkInput.value = linkLoja;

        if (btnCopiar) {
          btnCopiar.onclick = async () => {
            try {
              await navigator.clipboard.writeText(linkLoja);
              // feedback simples
              alert('Link do cardápio copiado!');
            } catch (err) {
              console.error('Erro ao copiar:', err);
              alert('Não foi possível copiar o link.');
            }
          };
        }
      }

      // Totais
      const tot = data.totais || {};
      if (faturamentoEl) faturamentoEl.textContent = formatBRL(tot.faturamento ?? 0);
      if (entreguesEl) entreguesEl.textContent = Number(tot.entregues ?? 0);
      if (andamentoEl) andamentoEl.textContent = Number(tot.andamento ?? 0);
      if (canceladosEl) canceladosEl.textContent = Number(tot.cancelados ?? 0);
      if (clientesEl) clientesEl.textContent = Number(tot.clientes ?? 0);
      if (produtosEl) produtosEl.textContent = Number(tot.produtos ?? 0);

      // Últimos pedidos
      if (ultimosEl) {
        ultimosEl.innerHTML = '';
        const pedidos = Array.isArray(data.ultimosPedidos) ? data.ultimosPedidos : [];
        if (pedidos.length === 0) {
          const li = document.createElement('li');
          li.className = 'list-group-item text-muted small';
          li.textContent = 'Nenhum pedido recente';
          ultimosEl.appendChild(li);
        } else {
          pedidos.forEach(p => {
            const total = Number(p.total || 0) + Number(p.taxa_entrega || 0);
            const date = p.data_criacao ? new Date(p.data_criacao) : null;

            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-start';

            const statusClass =
              p.status === 'entregue' ? 'bg-success' :
              ['pendente', 'aceito', 'em_entrega'].includes(p.status) ? 'bg-warning text-dark' :
              'bg-danger';

            const left = document.createElement('div');
            left.innerHTML = `<strong>#${safeText(p.id)}</strong> — ${formatBRL(total)}
              <div class="small text-muted">${safeText(p.metodo_pagamento)}${date ? ' — ' + date.toLocaleString('pt-BR',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'}) : ''}</div>`;

            const badge = document.createElement('span');
            badge.className = `badge ${statusClass}`;
            badge.textContent = safeText(p.status);

            li.appendChild(left);
            li.appendChild(badge);
            ultimosEl.appendChild(li);
          });
        }
      }

      // Gráfico de faturamento
      const labels = Array.isArray(data.labelsGrafico) ? data.labelsGrafico : [];
      const valores = Array.isArray(data.valoresGrafico) ? data.valoresGrafico.map(v => Number(v || 0)) : [];

      if (graficoCanvas) {
        if (chartInstance) {
          chartInstance.destroy();
          chartInstance = null;
        }
        chartInstance = new Chart(graficoCanvas.getContext('2d'), {
          type: 'line',
          data: {
            labels: labels,
            datasets: [{
              label: 'Faturamento (R$)',
              data: valores,
              borderColor: 'rgb(220,53,69)',
              backgroundColor: 'rgba(220,53,69,0.15)',
              tension: 0.25,
              fill: true,
              pointRadius: 3
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) { return Number(value).toLocaleString('pt-BR', { style:'currency', currency:'BRL' }); }
                }
              }
            }
          }
        });
      }

    } catch (err) {
      console.error(err);
      alert('Erro ao carregar dados do dashboard. Veja o console para mais detalhes.');
    }
  }

  carregarDashboard();
});
