document.addEventListener('DOMContentLoaded', async () => {
  const logoEl = document.getElementById('logoLoja');
  const nomeEl = document.getElementById('nomeLoja');
  const enderecoEl = document.getElementById('enderecoLoja');

  // Pega o slug da URL
  const urlParams = new URLSearchParams(window.location.search);
  const slugLoja = urlParams.get('loja');

  if (!slugLoja) {
    nomeEl.textContent = "Loja não encontrada";
    enderecoEl.textContent = "";
    logoEl.src = "https://placehold.co/200x150?text=Erro";
    return;
  }

  try {
    // Faz a requisição ao back-end passando o slug
    const res = await fetch(`../php/get_loja.php?slug=${encodeURIComponent(slugLoja)}`);
    const loja = await res.json();

    if (!loja || loja.erro) {
      nomeEl.textContent = "Loja não encontrada";
      enderecoEl.textContent = "";
      logoEl.src = "https://placehold.co/200x150?text=Erro";
      return;
    }

    // Preenche os campos da loja
    nomeEl.textContent = loja.nome;
    enderecoEl.textContent = loja.endereco || '';
    logoEl.src = loja.logo ? `data:image/png;base64,${loja.logo}` : "https://placehold.co/200x150?text=Sem+Logo";

  } catch (err) {
    console.error('Erro ao carregar dados da loja:', err);
    nomeEl.textContent = "Erro ao carregar loja";
    enderecoEl.textContent = "";
    logoEl.src = "https://placehold.co/200x150?text=Erro";
  }

  // Aqui você pode adicionar a lógica do login via fetch/ajax
  const formLogin = document.getElementById('formLogin');
  formLogin.addEventListener('submit', async (e) => {
    e.preventDefault();
    // Pega valores
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;

    // Exemplo de requisição de login
    try {
      const resp = await fetch('../php/login_cliente.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, senha, slug: slugLoja })
      });
      const data = await resp.json();

      if (data.erro) {
        alert(data.erro);
      } else {
        // Redireciona para página do cliente
        window.location.href = `./dashboard.html?loja=${slugLoja}`;
      }
    } catch (err) {
      console.error(err);
      alert('Erro ao efetuar login.');
    }
  });
});
