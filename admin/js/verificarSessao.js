async function carregarAdmin() {
    const fotoAdmin = document.getElementById('fotoAdmin');
    const nomeAdmin = document.getElementById('nomeAdmin');

    // ===== Ajuste de caminho automático =====
    let url = '../php/verificarSessao.php';
    if (window.location.pathname.includes('/admin/paginas/')) {
        url = '../../php/verificarSessao.php';
    } else if (window.location.pathname.includes('/paginas/')) {
        url = '../php/verificarSessao.php';
    } else if (window.location.pathname.includes('/admin/')) {
        url = './php/verificarSessao.php';
    }

    try {
        const res = await fetch(url, { credentials: 'include' });

        if (!res.ok) {
            throw new Error(`Erro HTTP ${res.status} - ${res.statusText}`);
        }

        const text = await res.text();
        console.log('Resposta crua do PHP:', text);

        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            console.error('Resposta do PHP não é JSON válido:', err, text);
            if (fotoAdmin) fotoAdmin.src = 'https://placehold.co/200x150?text=Sem+Imagem';
            if (nomeAdmin) nomeAdmin.textContent = 'Administrador';
            return;
        }

        if (data.erro) {
            alert(data.erro);
            window.location.href = '../paginas/login.html';
            return;
        }

        if (fotoAdmin) {
            fotoAdmin.src = data.foto && data.foto.trim() !== ''
                ? data.foto
                : 'https://placehold.co/200x150?text=Sem+Imagem';
        }

        if (nomeAdmin) nomeAdmin.textContent = data.nome || 'Administrador';

    } catch (err) {
        console.error('Erro ao carregar admin:', err);
        if (fotoAdmin) fotoAdmin.src = 'https://placehold.co/200x150?text=Sem+Imagem';
        if (nomeAdmin) nomeAdmin.textContent = 'Administrador';
    }
}

document.addEventListener('DOMContentLoaded', carregarAdmin);
