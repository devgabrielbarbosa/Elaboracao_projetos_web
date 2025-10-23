document.addEventListener('DOMContentLoaded', () => {
    const faturamentoEl = document.getElementById('faturamento');
    const entreguesEl = document.getElementById('entregues');
    const andamentoEl = document.getElementById('andamento');
    const canceladosEl = document.getElementById('cancelados');
    const clientesEl = document.getElementById('clientes');
    const produtosEl = document.getElementById('produtos');
    const ultimosEl = document.getElementById('ultimosPedidos');
    const graficoCtx = document.getElementById('graficoFaturamento').getContext('2d');

    const linkInput = document.getElementById('linkCardapio');
    const btnCopiar = document.getElementById('btnCopiarLink');

    // Função auxiliar fetch JSON
    async function fetchJSON(url, options = {}) {
        try {
            const res = await fetch(url, { credentials: 'same-origin', ...options });
            return await res.json();
        } catch (err) {
            console.error(err);
            alert('Erro de conexão com o servidor.');
            return null;
        }
    }

    // ===== Carregar loja_id e gerar link do cardápio =====
    async function gerarLinkCardapio() {
        const data = await fetchJSON('../php/loja_id.php');
        if (!data || data.erro) {
            console.error(data?.erro || 'Erro ao buscar loja_id');
            linkInput.value = '';
            return;
        }

        const lojaId = data.loja_id;
        const linkLoja = `${window.location.origin}/cliente_login.php?loja_id=${lojaId}`;
        linkInput.value = linkLoja;

        btnCopiar.addEventListener('click', () => {
            linkInput.select();
            linkInput.setSelectionRange(0, 99999); // mobile
            navigator.clipboard.writeText(linkInput.value)
                .then(() => alert('Link copiado!'))
                .catch(err => alert('Erro ao copiar link.'));
        });
    }

    // ===== Carregar dashboard =====
    async function carregarDashboard() {
        const data = await fetchJSON('../php/dasboard_data.php');
        if (!data || data.erro) {
            alert(data?.erro || 'Erro ao carregar dados do dashboard.');
            return;
        }

        const tot = data.totais;
        faturamentoEl.textContent = `R$ ${tot.faturamento.toFixed(2).replace('.', ',')}`;
        entreguesEl.textContent = tot.entregues;
        andamentoEl.textContent = tot.andamento;
        canceladosEl.textContent = tot.cancelados;
        clientesEl.textContent = tot.clientes;
        produtosEl.textContent = tot.produtos;

        // Últimos pedidos
        ultimosEl.innerHTML = '';
        if (!data.ultimosPedidos || data.ultimosPedidos.length === 0) {
            ultimosEl.innerHTML = '<li class="list-group-item text-muted small">Nenhum pedido recente</li>';
        } else {
            data.ultimosPedidos.forEach(p => {
                const li = document.createElement('li');
                li.className = "list-group-item d-flex justify-content-between align-items-start";
                const statusClass = p.status === 'entregue' ? 'bg-success' :
                                    ['pendente','aceito','em_entrega'].includes(p.status) ? 'bg-warning text-dark' : 'bg-danger';
                li.innerHTML = `
                    <div>
                        <strong>#${p.id}</strong> — R$ ${(p.total + p.taxa_entrega).toFixed(2).replace('.', ',')}
                        <div class="small text-muted">${p.metodo_pagamento} — ${new Date(p.data_criacao).toLocaleDateString('pt-BR', {
                            day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit'
                        })}</div>
                    </div>
                    <span class="badge ${statusClass}">${p.status}</span>
                `;
                ultimosEl.appendChild(li);
            });
        }

        // Gráfico de faturamento
        new Chart(graficoCtx, {
            type: 'line',
            data: {
                labels: data.labelsGrafico,
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: data.valoresGrafico,
                    borderColor: 'rgb(220,53,69)',
                    backgroundColor: 'rgba(220,53,69,0.2)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Executar funções
    gerarLinkCardapio();
    carregarDashboard();
});
