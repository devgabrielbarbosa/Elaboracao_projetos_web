document.addEventListener('DOMContentLoaded', async () => {
  const form = document.getElementById('formPerfil');

  async function carregarPerfil(){
    const res = await fetch('php/perfil.php');
    const cliente = await res.json();
    if(cliente.erro){ alert(cliente.erro); return; }
    form.nome.value = cliente.nome||'';
    form.telefone.value = cliente.telefone||'';
    form.data_nascimento.value = cliente.data_nascimento||'';
  }

  form.addEventListener('submit', async e=>{
    e.preventDefault();
    const data = new FormData(form);
    const res = await fetch('php/perfil.php',{method:'POST',body:data});
    const json = await res.json();
    if(json.sucesso) alert(json.mensagem);
    else alert(json.erro);
  });

  carregarPerfil();
});
