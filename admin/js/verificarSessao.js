async function carregarAdmin() {
    try {
        const res = await fetch('../php/admin_info.php', { credentials: 'include' });
        const data = await res.json();

        if(data.erro){
            window.location.href = '../login.html'; // redireciona se não logado
            return;
        }

        const nomeEl = document.getElementById('nomeAdmin');
        const fotoEl = document.getElementById('fotoAdmin');

        if(nomeEl) nomeEl.textContent = data.nome;
        if(fotoEl){
            if(data.foto){
                fotoEl.src = data.foto; // data:image/jpeg;base64,...
            } else {
                fotoEl.src = '../uploads/placeholder.png';
            }
        }
    } catch(e){
        console.error('Erro ao carregar admin:', e);
    }
}

window.addEventListener('DOMContentLoaded', carregarAdmin);
