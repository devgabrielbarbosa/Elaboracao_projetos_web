async function logout() {
    if (!confirm('Deseja realmente sair da conta?')) return;

    try {
        const res = await fetch('../php/logout.php', {
            method: 'POST',
            credentials: 'include'
        });

        const text = await res.text(); // Captura resposta crua
        console.log('Resposta crua do logout:', text);

        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            console.error('Resposta não é JSON válida:', err, text);
            alert('Erro ao processar resposta do servidor.');
            return;
        }

        if (data.sucesso) {
            alert(data.mensagem || 'Logout realizado com sucesso.');
            window.location.href = '../paginas/login.html';
        } else {
            alert(data.erro || 'Erro ao sair. Tente novamente.');
        }

    } catch (err) {
        console.error('Erro ao fazer logout:', err);
        alert('Erro de conexão com o servidor.');
    }
}

// Exemplo: vincular a um botão
document.addEventListener('DOMContentLoaded', () => {
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) btnLogout.addEventListener('click', logout);
});
