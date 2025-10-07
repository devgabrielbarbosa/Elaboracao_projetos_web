async function verificarSessao() {
    try {
        const res = await fetch('../php/verificar_sessao.php', { credentials: 'include' });
        const data = await res.json();

        if (!data.logado) {
            window.location.href = '../login.html?erro=Faça+login+novamente';
            return;
        }

        const nome = document.getElementById('nomeAdmin');
        if (nome) nome.textContent = data.admin_nome || 'Administrador';

    } catch (e) {
        console.error('Erro ao verificar sessão:', e);
        window.location.href = '../login.html?erro=Erro+na+verificação+da+sessão';
    }
}

window.addEventListener('DOMContentLoaded', verificarSessao);
