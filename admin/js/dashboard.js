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

  function formatBRL(value) {
    return Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  }

  async function fetchJSON(url, options={}) {
    const res = await fetch(url, { credentials:'include', ...options });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
  }

  async function carregarDashboard() {
    try {
      const data = await fetchJSON('../php/dashboard_data.php'); // caminho pro seu PHP

      if (data.erro) { alert(data.erro); return; }

      // Atualiza cards
      faturamentoEl.textContent = formatBRL(data.totais.faturamento);
      entreguesEl.textContent = data.totais.entregues;
      andamentoEl.textContent = data.totais.andamento;
      canceladosEl.textContent = data.totais.cancelados;
      clientesEl.textContent = data.totais.clientes;
      produtosEl.textContent = data.totais.produtos;

      // Atualiza últimos pedidos
      ultimosEl.innerHTML = '';
      if (!data.ultimosPedidos.length) {
        const li = document.createElement('li');
        li.className = 'list-group-item text-muted small';
        li.textContent = 'Nenhum pedido recente';
        ultimosEl.appendChild(li);
      } else {
        data.ultimosPedidos.forEach(p => {
          const li = document.createElement('li');
          li.className = 'list-group-item d-flex justify-content-between align-items-start';
          const total = Number(p.total) + Number(p.taxa_entrega);
          const date = new Date(p.data_criacao);
          li.innerHTML = `<div><strong>#${p.id}</strong> — ${formatBRL(total)}
            <div class="small text-muted">${p.metodo_pagamento} — ${date.toLocaleString('pt-BR',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'})}</div></div>
            <span class="badge ${p.status==='entregue'?'bg-success':p.status==='cancelado'?'bg-danger':'bg-warning text-dark'}">${p.status}</span>`;
          ultimosEl.appendChild(li);
        });
      }

      // Atualiza gráfico
      const ctx = graficoCanvas.getContext('2d');
      if (chartInstance) chartInstance.destroy();
      chartInstance = new Chart(ctx, {
        type: 'line',
        data: { labels: data.labelsGrafico, datasets:[{
          label:'Faturamento', 
          data: data.valoresGrafico, 
          borderColor:'rgb(220,53,69)', 
          backgroundColor:'rgba(220,53,69,0.15)', 
          fill:true, 
          tension:0.25
        }]},
        options: { 
          responsive:true, 
          plugins:{legend:{display:false}}, 
          scales:{y:{beginAtZero:true, ticks:{callback:v=>formatBRL(v)}}}
        }
      });

      // Link do cardápio
      const linkLoja = `${window.location.origin}/projeto_web/cliente/index.html?loja_id=${encodeURIComponent(data.loja_id)}`;
      linkInput.value = linkLoja;
      btnCopiar.onclick = async () => { await navigator.clipboard.writeText(linkLoja); alert('Link do cardápio copiado!'); };

    } catch (err) {
      console.error('Erro ao carregar dados do dashboard:', err);
      alert('Erro ao carregar dados do dashboard.');
    }
  }

  carregarDashboard();
});
