document.addEventListener('DOMContentLoaded', () => {
  const formLogin = document.getElementById('formLogin');
  const formCadastro = document.getElementById('formCadastro');

  formLogin.addEventListener('submit', async e => {
    e.preventDefault();
    const data = new FormData(formLogin);
    const res = await fetch('../php/login.php', { method: 'POST', body: data });
    const json = await res.json();
    if(json.sucesso) window.location.href = 'index.html';
    else alert(json.erro);
  });

  formCadastro.addEventListener('submit', async e => {
    e.preventDefault();
    const data = new FormData(formCadastro);
    const res = await fetch('php/cadastro.php', { method: 'POST', body: data });
    const json = await res.json();
    if(json.sucesso) window.location.href = 'index.html';
    else alert(json.erro);
  });
});
