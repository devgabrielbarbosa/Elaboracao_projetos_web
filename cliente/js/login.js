document.addEventListener('DOMContentLoaded', async () => {
  const params = new URLSearchParams(window.location.search);
  const lojaParam = params.get('loja');
  const nomeLojaEl = document.getElementById('nomeLoja');
  const logoLojaEl = document.getElementById('logoLoja');

  if (!lojaParam) {
    nomeLojaEl.textContent = 'Loja não especificada';
    return;
  }

  try {
    const res = await fetch(`../php/get_loja.php?loja=${encodeURIComponent(lojaParam)}`);
    const data = await res.json();

    if (data.erro) {
      nomeLojaEl.textContent = data.erro;
      return;
    }

    if (data.sucesso && data.loja) {
      const loja = data.loja;
      nomeLojaEl.textContent = `Bem-vindo à ${loja.nome}`;
      if (loja.logo) {
        logoLojaEl.src = `data:image/png;base64,${loja.logo}`;
      } else {
        logoLojaEl.src = '../imagens/default_logo.png';
      }
    } else {
      nomeLojaEl.textContent = 'Erro: dados da loja inválidos.';
    }

  } catch (err) {
    console.error('Erro ao carregar loja:', err);
    nomeLojaEl.textContent = 'Erro ao carregar loja.';
  }
});
