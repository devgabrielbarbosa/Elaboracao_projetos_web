async function carregarAdmin() {
    try {
        const res = await fetch('../php/admin_info.php');
        const data = await res.json();

        if(data.erro){
            window.location.href = 'login.html'; // redireciona se não logado
            return;
        }

        document.getElementById('nomeAdmin').textContent = data.nome;
        if(data.foto){
            document.getElementById('fotoAdmin').src = data.foto;
        } else {
            document.getElementById('fotoAdmin').src = '../uploads/placeholder.png';
        }
    } catch(e){
        console.error(e);
    }
}

window.addEventListener('DOMContentLoaded', carregarAdmin);