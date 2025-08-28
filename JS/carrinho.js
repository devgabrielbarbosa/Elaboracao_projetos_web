document.addEventListener("DOMContentLoaded", () => {
  const itensCarrinho = document.getElementById("itens-carrinho");
  const btnLimpar = document.getElementById("limpar-carrinho");

  // Função para renderizar o carrinho
  function renderCarrinho() {
    const carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
    itensCarrinho.innerHTML = "";

    carrinho.forEach((item, index) => {
      const divItem = document.createElement("div");
      divItem.className = "carrinho-item";

      divItem.innerHTML = `
        <img src="${item.img}" alt="${item.nome}" class="carrinho-img">
        <span class="carrinho-nome">${item.nome}</span>
        <span class="carrinho-preco">R$ ${(item.preco * item.qtd).toFixed(2)}</span>
        <div class="carrinho-qtd">
          <button class="menos-carrinho" data-index="${index}">-</button>
          <span>${item.qtd}</span>
          <button class="mais-carrinho" data-index="${index}">+</button>
        </div>
      `;
      itensCarrinho.appendChild(divItem);
    });

    // Adicionar eventos de mais/menos
    const botoesMais = document.querySelectorAll(".mais-carrinho");
    const botoesMenos = document.querySelectorAll(".menos-carrinho");

    botoesMais.forEach(btn => {
      btn.addEventListener("click", () => {
        const i = btn.dataset.index;
        carrinho[i].qtd++;
        localStorage.setItem("carrinho", JSON.stringify(carrinho));
        renderCarrinho();
      });
    });

    botoesMenos.forEach(btn => {
      btn.addEventListener("click", () => {
        const i = btn.dataset.index;
        if (carrinho[i].qtd > 1) {
          carrinho[i].qtd--;
          localStorage.setItem("carrinho", JSON.stringify(carrinho));
          renderCarrinho();
        }
      });
    });
  }


 // Botão de limpar carrinho
btnLimpar.addEventListener("click", () => {
  localStorage.removeItem("carrinho");  // Limpa o carrinho
  itensCarrinho.innerHTML = "";          // Limpa visualmente na página
  alert("Carrinho esvaziado!");          // Mostra alerta
});

  // Inicializar carrinho
  renderCarrinho();
});
