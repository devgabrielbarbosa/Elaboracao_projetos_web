document.addEventListener("DOMContentLoaded", () => {
  // ---------------- MENU HAMBURGER ----------------
  const toggleBtn = document.getElementById("menu-toggle");
  const menuItems = document.getElementById("menu-items");

  toggleBtn?.addEventListener("click", () => {
    menuItems.classList.toggle("show");
  });

  // ---------------- SLIDER ----------------
  const slides = [
    { img: "IMG/banner-brigadeiro.png", alt: "banner de brigadeiro", link: "#brigadeiros", text: "Ver Brigadeiros" },
    { img: "IMG/banner-barra-chocolate.png", alt: "banner de barras de chocolate", link: "#chocolates", text: "Ver Chocolates" },
    { img: "IMG/banner-trufas.png", alt: "banner de trufas", link: "#trufas", text: "Ver Trufas" }
  ];

  const slideImg = document.getElementById("slide-img");
  const slideBtn = document.getElementById("slide-btn");
  const prevBtn = document.getElementById("prev");
  const nextBtn = document.getElementById("next");
  const dotsContainer = document.getElementById("dots-container");

  let currentIndex = 0;

if (dotsContainer) { // só roda se existir
  slides.forEach((_, index) => {
    const dot = document.createElement("span");
    dot.classList.add("dot");
    if (index === 0) dot.classList.add("active");
    dot.addEventListener("click", () => showSlide(index));
    dotsContainer.appendChild(dot);
  });
}
  const dots = document.querySelectorAll(".dot");

  function showSlide(index) {
    if (index >= slides.length) index = 0;
    if (index < 0) index = slides.length - 1;
    currentIndex = index;

    const slide = slides[index];
    if (slideImg && slideBtn) {
  slideImg.src = slide.img;
  slideImg.alt = slide.alt;
  slideBtn.href = slide.link;
  slideBtn.textContent = slide.text;

  dots.forEach((dot, i) => dot.classList.toggle("active", i === index));
}

  }

  prevBtn?.addEventListener("click", () => showSlide(currentIndex - 1));
  nextBtn?.addEventListener("click", () => showSlide(currentIndex + 1));

  showSlide(currentIndex);
  setInterval(() => showSlide(currentIndex + 1), 4000);

  // ---------------- FUNÇÃO PARA URL ----------------
  function siteBasePath() {
    if (location.hostname.includes("github.io")) {
      const parts = location.pathname.split("/").filter(Boolean);
      if (parts.length > 0) return `/${parts[0]}`;
    }
    return "";
  }

  function buildUrl(relativePath) {
    if (location.protocol === "file:") {
      const path = location.pathname;
      const dir = path.substring(0, path.lastIndexOf("/"));
      return `file://${dir}/${relativePath}`;
    } else {
      const base = siteBasePath();
      return `${location.origin}${base}/${relativePath}`.replace(/([^:]\/)\/+/g, "$1");
    }
  }

  // ---------------- CARRINHO ----------------
  let carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];

  function atualizarQtdCarrinho() {
    const total = carrinho.reduce((acc, item) => acc + item.qtd, 0);
    const qtdCarrinho = document.getElementById("qtd-carrinho");
    if (qtdCarrinho) qtdCarrinho.textContent = total;
  }
  atualizarQtdCarrinho();

  document.querySelectorAll(".produto-card").forEach(card => {
    const addCarrinho = card.querySelector(".add-carrinho");

    addCarrinho?.addEventListener("click", () => {
      const produtoAtual = {
        nome: card.dataset.nome,
        preco: parseFloat(card.dataset.preco),
        img: buildUrl(card.dataset.img),
        qtd: 1 // sempre 1 por clique
      };

      const indexExistente = carrinho.findIndex(p => p.nome === produtoAtual.nome);
      if (indexExistente > -1) {
        carrinho[indexExistente].qtd += 1; // soma 1
      } else {
        carrinho.push({ ...produtoAtual });
      }

      localStorage.setItem("carrinho", JSON.stringify(carrinho));
      atualizarQtdCarrinho();

      const mensagem = document.createElement("div");
      mensagem.textContent = `${produtoAtual.nome} adicionado ao carrinho!`;
      mensagem.className = "mensagem-carrinho";
      mensagem.style.cssText = `
        position: fixed;
        top: 200px;
        right: 100px;
        background-color: #4CAF50;
        color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        z-index: 1000;
        font-weight: bold;
      `;
      document.body.appendChild(mensagem);
      setTimeout(() => mensagem.remove(), 2000);
    });
  });

  // ---------------- BOTÕES DE NAVEGAÇÃO ----------------
  const btnCarrinho = document.getElementById("btn-carrinho");
  const btnPerfil = document.getElementById("btn-perfil");

  btnCarrinho?.addEventListener("click", e => {
    e.preventDefault();
    window.location.href = buildUrl("PAGINAS/carrinho.html");
  });

  btnPerfil?.addEventListener("click", e => {
    e.preventDefault();
    window.location.href = buildUrl("PAGINAS/profile.html");
  });

  // ---------------- PESQUISA ----------------
  const input = document.getElementById("campo-pesquisa");
  const btnPesquisa = document.getElementById("pesquisa");
  const produtos = document.querySelectorAll(".produto-card");

  const msg = document.createElement("h1");
  msg.textContent = "Produto não encontrado.";
  msg.style.textAlign = "center";
  msg.style.color = "red";
  msg.style.display = "none";
  document.querySelector(".pesquisa-result").after(msg);

  function pesquisar() {
    const termo = input.value.toLowerCase().trim();
    let encontrou = false;

    produtos.forEach(produto => {
      const nome = produto.dataset.nome.toLowerCase();
      if (nome.includes(termo) || termo === "") {
        produto.style.display = "block";
        if (nome.includes(termo)) encontrou = true;
      } else {
        produto.style.display = "none";
      }
    });

    msg.style.display = (termo !== "" && !encontrou) ? "block" : "none";
  }

  btnPesquisa?.addEventListener("click", pesquisar);
  input?.addEventListener("keyup", e => {
    if (e.key === "Enter" || input.value.trim() === "") pesquisar();
  });
// notificações
const btn = document.getElementById("btn-notifications");
const sidebar = document.getElementById("sidebar");

btn.addEventListener("click", () => {
  sidebar.classList.toggle("active");
});

// Fechar ao clicar fora
document.addEventListener("click", (e) => {
  if (!sidebar.contains(e.target) && !btn.contains(e.target)) {
    sidebar.classList.remove("active");
  }
});

});
