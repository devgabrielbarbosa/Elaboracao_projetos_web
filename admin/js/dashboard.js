// js/dashboard.js
document.addEventListener('DOMContentLoaded', () => {
    fetch('ajax/dashboard_data.php')
    .then(res => res.json())
    .then(data => {
        if(data.erro){
            alert(data.erro);
            return;
        }

        const tot = data.totais;
        document.getElementById('faturamento').textContent = `R$ ${tot.faturamento.toFixed(2).replace('.', ',')}`;
        document.getElementById('entregues').textContent = tot.entregues;
        document.getElementById('andamento').textContent = tot.andamento;
        document.getElementById('cancelados').textContent = tot.cancelados;
        document.getElementById('clientes').textContent = tot.clientes;
        document.getElementById('produtos').textContent = tot.produtos;

        const ultimosEl = document.getElementById('ultimosPedidos');
        ultimosEl.innerHTML = '';
        if(data.ultimosPedidos.length === 0){
            ultimosEl.innerHTML = '<li class="list-group-item">Nenhum pedido recente.</li>';
        } else {
            data.ultimosPedidos.forEach(p => {
                const li = document.createElement('li');
                li.className = "list-group-item d-flex justify-content-between align-items-start";
                const statusClass = p.status === 'entregue' ? 'bg-success' : (['pendente','aceito','em_entrega'].includes(p.status) ? 'bg-warning text-dark' : 'bg-danger');
                li.innerHTML = `<div>
                    <strong>#${p.id}</strong> — R$ ${(p.total + p.taxa_entrega).toFixed(2).replace('.', ',')}
                    <div class="small text-muted">${p.metodo_pagamento} — ${new Date(p.data_criacao).toLocaleDateString('pt-BR',{day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit'})}</div>
                </div>
                <span class="badge ${statusClass}">${p.status}</span>`;
                ultimosEl.appendChild(li);
            });
        }

        const ctx = document.getElementById('graficoFaturamento').getContext('2d');
        new Chart(ctx, {
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
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });
    });
});
