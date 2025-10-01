fetch('navbar_data.json')
.then(res => res.json())
.then(data => {
    document.getElementById('fotoAdmin').src = data.foto_admin;
    document.getElementById('nomeAdmin').textContent = data.nome_admin;
    document.getElementById('perfilAdminLink').textContent = `Perfil (${data.nome_admin})`;
});
