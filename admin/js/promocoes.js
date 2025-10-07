window.addEventListener('DOMContentLoaded', () => {

    const formPromocao = document.getElementById('form-promocao');
    const container = document.getElementById('promocoes-container');
    const msgContainer = document.getElementById('mensagem-container');

    // Carregar promoções
    async function carregarPromocoes() {
        container.innerHTML = '<p class="text-center text-muted">Carregando promoções...</p>';

        try {
            const res = await fetch('../php/promocoes_api.php');
            if (!res.ok) throw new Error('Erro na resposta do servidor');
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
                const ativoClass = p.ativo == 1 ? 'bg-success' : 'bg-secondary';
                const ativoText = p.ativo == 1 ? 'Ativa' : 'Inativa';

                const card = document.createElement('div');
                card.className = 'col-md-4 mb-4';
                card.innerHTML = `
                    <div class="card h-100 shadow-sm border-0 card-promocao">
                        <img src="${p.imagem}" class="banner-img" alt="${p.codigo}">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-primary fw-bold">
                                ${p.codigo} <span class="badge ${ativoClass} ms-2">-${p.desconto}%</span>
                            </h5>
                            <p class="card-text text-muted">${p.descricao}</p>
                            <p class="mb-2"><small>${p.data_inicio} até ${p.data_fim}</small></p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <a href="#" class="badge ${ativoClass} text-decoration-none p-2">${ativoText}</a>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });

        } catch (e) {
            console.error('Erro no fetch:', e);
            container.innerHTML = '<p class="text-center text-danger">Erro ao carregar promoções.</p>';
        }
    }

    // Enviar nova promoção
    formPromocao.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formPromocao);
        formData.append('acao', 'adicionar');

        try {
            const res = await fetch('../php/promocoes_api.php', {
                method: 'POST',
                body: formData
            });

            if (!res.ok) throw new Error('Erro na resposta do servidor');
            const data = await res.json();

            if (data.erro) {
                msgContainer.innerHTML = `<div class="alert alert-danger">${data.erro}</div>`;
            } else if (data.mensagem) {
                msgContainer.innerHTML = `<div class="alert alert-success">${data.mensagem}</div>`;
                formPromocao.reset();
                carregarPromocoes();
            }

        } catch (e) {
            console.error('Erro no cadastro:', e);
            msgContainer.innerHTML = `<div class="alert alert-danger">Erro ao cadastrar promoção.</div>`;
        }
    });

    // Carrega promoções ao abrir a página
    carregarPromocoes();
});
