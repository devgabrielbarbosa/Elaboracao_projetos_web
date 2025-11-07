document.addEventListener("DOMContentLoaded", async () => {
  const logoEl = document.getElementById("logoLoja");
  const nomeEl = document.getElementById("nomeLoja");
  const lojaIdInput = document.getElementById("loja_id");
  const formCadastro = document.getElementById("formCadastro");
  const linkLogin = document.getElementById("linkLogin");

  const params = new URLSearchParams(window.location.search);
  const slug = params.get("loja");

  if (!slug) {
    nomeEl.textContent = "Loja não especificada na URL.";
    return;
  }

  // ===== Carrega dados da loja =====
  try {
    const res = await fetch(
      `php/get_loja.php?loja=${encodeURIComponent(slug)}`
    );

    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const data = await res.json(); // já converte direto para JSON

    if (data.erro) {
      nomeEl.textContent = data.erro;
      return;
    }

    if (!data.loja) {
      nomeEl.textContent = "Loja não encontrada.";
      return;
    }

    const loja = data.loja;
    nomeEl.textContent = loja.nome;
    lojaIdInput.value = loja.id;

    logoEl.src = loja.logo
      ? `data:image/png;base64,${loja.logo}`
      : "../imagens/default_logo.png";
  } catch (err) {
    console.error("Erro ao carregar loja:", err);
    nomeEl.textContent = "Erro ao carregar informações da loja.";
  }

  // ===== Submissão do formulário =====
  formCadastro.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(formCadastro);

    try {
      const res = await fetch("php/cadastro_cliente.php", {
        method: "POST",
        body: formData,
      });

      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const result = await res.json(); // converte direto

      if (result.sucesso) {
        alert(result.mensagem || "Cadastro realizado com sucesso!");
        window.location.href = `login.html?loja=${slug}`;
      } else {
        alert(result.erro || "Erro ao cadastrar. Tente novamente.");
      }
    } catch (err) {
      console.error("Erro no cadastro:", err);
      alert("Falha na comunicação com o servidor.");
    }
  });

  // ===== Link voltar ao login =====
  linkLogin.addEventListener("click", (e) => {
    e.preventDefault();
    window.location.href = `login.html?loja=${slug}`;
  });
});
