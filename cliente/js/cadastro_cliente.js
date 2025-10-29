document.addEventListener('DOMContentLoaded', async () => {
  const form = document.getElementById('formCadastro');
  const nomeLojaEl = document.getElementById('nomeLoja');

  // Pega o slug da loja da URL
  const urlParams = new URLSearchParams(window.location.search);
  const slugLoja = urlParams.get('loja');

  if (!slugLoja) {
    alert('Loja não especificada.');
    nomeLojaEl.textContent = 'Loja não encontrada';
    return;
  }

  // Busca informações da loja
  try {
    const resLoja = await fetch(`../php/loja_info.php?slug=${encodeURIComponent(slugLoja)}`);
    const dataLoja = await resLoja.json();

    if (dataLoja.erro) {
      nomeLojaEl.textContent = dataLoja.erro;
    } else {
      nomeLojaEl.textContent = dataLoja.nome;
    }

  } catch (err) {
    console.error(err);
    nomeLojaEl.textContent = 'Erro ao carregar a loja';
  }

  // Evento de cadastro
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = {
      nome: document.getElementById('nome').value,
      cpf: document.getElementById('cpf').value,
      telefone: document.getElementById('telefone').value,
      email: document.getElementById('email').value,
      senha: document.getElementById('senha').value,
      data_nascimento: document.getElementById('data_nascimento').value,
      slug: slugLoja
    };

    try {
      const res = await fetch('../php/cadastro_cliente.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });
      const data = await res.json();

      if (data.erro) {
        alert(data.erro);
      } else {
        alert('Cadastro realizado com sucesso!');
        window.location.href = `login.html?loja=${slugLoja}`;
      }
    } catch (err) {
      console.error(err);
      alert('Erro ao cadastrar. Tente novamente.');
    }
  });
});
