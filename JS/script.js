// menu haburger

 const toggleBtn = document.getElementById("menu-toggle");
  const menuItems = document.getElementById("menu-items");

  toggleBtn.addEventListener("click", () => {
    menuItems.classList.toggle("show");
  });

// slider
 const slides = [
  {
    img: "../IMG/banner brigadeiro.png",
    alt: "banner de brigadeiro",
    link: "#brigadeiros",
    text: "Ver Brigadeiros"
  },
  {
    img: "../IMG/banner-barra-choclate.png",
    alt: "banner de barras de chocolate",
    link: "#chocolates",
    text: "Ver Chocolates"
  },
  {
    img: "../IMG/banner-trufas.png",
    alt: "banner de trufas",
    link: "#trufas",
    text: "Ver Trufas"
  }
];

const slideImg = document.getElementById("slide-img");
const slideBtn = document.getElementById("slide-btn");
const prevBtn = document.getElementById("prev");
const nextBtn = document.getElementById("next");
const dotsContainer = document.getElementById("dots-container");

let currentIndex = 0;

// Criar dots
slides.forEach((_, index) => {
  const dot = document.createElement("span");
  dot.classList.add("dot");
  if (index === 0) dot.classList.add("active");
  dot.addEventListener("click", () => showSlide(index));
  dotsContainer.appendChild(dot);
});
const dots = document.querySelectorAll(".dot");

function showSlide(index) {
  if (index >= slides.length) index = 0;
  if (index < 0) index = slides.length - 1;
  currentIndex = index;

  const slide = slides[index];
  slideImg.src = slide.img;
  slideImg.alt = slide.alt;
  slideBtn.href = slide.link;
  slideBtn.textContent = slide.text;

  dots.forEach((dot, i) => {
    dot.classList.toggle("active", i === index);
  });
}

// Botões
prevBtn.addEventListener("click", () => showSlide(currentIndex - 1));
nextBtn.addEventListener("click", () => showSlide(currentIndex + 1));

// Inicial
showSlide(currentIndex);

// Automático
setInterval(() => {
  showSlide(currentIndex + 1);
}, 6000);



/// Modal Produto

const btnDetalhes = document.querySelectorAll(".btn-detalhe");
const modal = document.getElementById("modal-produto");
const modalImg = document.getElementById("modal-img");
const modalNome = document.getElementById("modal-nome");
const modalPreco = document.getElementById("modal-preco");
const closeModal = document.querySelector(".modal .close");
const menos = document.getElementById("menos");
const mais = document.getElementById("mais");
const qtdSpan = document.getElementById("qtd");
const addCarrinho = document.getElementById("add-carrinho");

const btnCarrinho = document.getElementById("btn-carrinho");
const qtdCarrinho = document.getElementById("qtd-carrinho"); // span dentro do botão

let qtd = 0;
let produtoAtual = {};

// Função para atualizar quantidade total no botão do carrinho
function atualizarQtdCarrinho() {
  const carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
  const total = carrinho.reduce((acc, item) => acc + item.qtd, 0);
  qtdCarrinho.textContent = total;
}

// Inicializa quantidade do carrinho ao carregar
atualizarQtdCarrinho();

// Abrir modal ao clicar no botão detalhe
btnDetalhes.forEach(btn => {
  btn.addEventListener("click", (e) => {
    const card = e.target.closest(".produto-card");
    produtoAtual = {
      nome: card.dataset.nome,
      preco: parseFloat(card.dataset.preco),
      img: card.dataset.img
    };

    modalImg.src = produtoAtual.img;
    modalNome.textContent = produtoAtual.nome;
    modalPreco.textContent = `R$ ${produtoAtual.preco.toFixed(2)}`;
    qtd = 1;
    qtdSpan.textContent = qtd;

    modal.style.display = "block";
  });
});

// Fechar modal
closeModal.addEventListener("click", () => modal.style.display = "none");
window.addEventListener("click", e => {
  if (e.target === modal) modal.style.display = "none";
});

// Alterar quantidade
menos.addEventListener("click", () => {
  if (qtd > 1) qtd--;
  qtdSpan.textContent = qtd;
});
mais.addEventListener("click", () => {
  qtd++;
  qtdSpan.textContent = qtd;
});

// Adicionar ao carrinho e mostrar mensagem
addCarrinho.addEventListener("click", () => {
  const item = {
    nome: produtoAtual.nome,
    preco: produtoAtual.preco,
    qtd: qtd,
    img: produtoAtual.img
  };

  // Pegar carrinho atual do localStorage
  let carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];

  // Verificar se o produto já existe no carrinho
  const indexExistente = carrinho.findIndex(prod => prod.nome === item.nome);
  if (indexExistente > -1) {
    // Se existir, apenas aumenta a quantidade
    carrinho[indexExistente].qtd += qtd;
  } else {
    // Senão, adiciona novo item
    carrinho.push(item);
  }

  // Salvar de volta no localStorage
  localStorage.setItem("carrinho", JSON.stringify(carrinho));

  // Atualizar número no botão do carrinho
  atualizarQtdCarrinho();

  // Fechar modal
  modal.style.display = "none";

  // Mostrar mensagem de confirmação
  const mensagem = document.createElement("div");
  mensagem.textContent = `${produtoAtual.nome} adicionado ao carrinho!`;
  mensagem.className = "mensagem-carrinho"; // estilize no CSS
  document.body.appendChild(mensagem);

  // Remover mensagem depois de 2 segundos
  setTimeout(() => {
    mensagem.remove();
  }, 2000);
});

// Botão do carrinho no header
btnCarrinho.addEventListener("click", () => {
  window.location.href = "../PAGINAS/carrinho.html";
});
