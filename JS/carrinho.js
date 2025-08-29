document.addEventListener("DOMContentLoaded", () => {
  const itensCarrinho = document.getElementById("itens-carrinho");
  const btnLimpar = document.getElementById("limpar-carrinho");
  const btnPerfil = document.getElementById("btn-perfil");
  const btnCarrinho = document.getElementById("btn-carrinho");
  const badgeQtd = document.getElementById("qtd-carrinho");

  // --- helpers de storage ---
  const getCarrinho = () => JSON.parse(localStorage.getItem("carrinho")) || [];
  const saveCarrinho = (c) => localStorage.setItem("carrinho", JSON.stringify(c));

  // --- adicionar produto ao carrinho ---
  function adicionarAoCarrinho(produto) {
    let carrinho = getCarrinho();
    const idx = carrinho.findIndex(p => p.nome === produto.nome);
    if (idx >= 0) {
      carrinho[idx].qtd += produto.qtd;
    } else {
      carrinho.push(produto);
    }
    saveCarrinho(carrinho);
    renderCarrinho();
  }

  // --- atualizar badge ---
  function atualizarBadgeETotais(carrinho) {
    const totalItens = carrinho.reduce((acc, it) => acc + (it.qtd || 0), 0);
    const totalValor = carrinho.reduce((acc, it) => acc + (it.qtd || 0) * Number(it.preco || 0), 0);

    if (badgeQtd) badgeQtd.textContent = totalItens;

    const totalItensEl = document.getElementById("total-itens");
    const totalValorEl = document.getElementById("total-valor");
    const resumoTotalEl = document.getElementById("resumo-total");

    if (totalItensEl) totalItensEl.textContent = totalItens;
    if (totalValorEl) totalValorEl.textContent = totalValor.toFixed(2);
    if (resumoTotalEl) resumoTotalEl.textContent = totalValor.toFixed(2);
  }

  // --- render carrinho (lista principal) ---
  function renderCarrinho() {
    const carrinho = getCarrinho();
    itensCarrinho.innerHTML = "";

    if (carrinho.length === 0) {
      itensCarrinho.innerHTML = '<p class="carrinho-vazio">Carrinho vazio</p>';
      atualizarBadgeETotais(carrinho);
      return;
    }

    carrinho.forEach((item, index) => {
      const divItem = document.createElement("div");
      divItem.className = "carrinho-item";
      divItem.innerHTML = `
        <img src="${item.img}" alt="${item.nome}" class="carrinho-img" style="width:64px;height:64px;object-fit:cover;border-radius:8px;margin-right:12px;">
        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span class="carrinho-nome">${item.nome}</span>
            <span class="carrinho-preco">R$ ${(Number(item.preco) * item.qtd).toFixed(2)}</span>
          </div>
          <div class="carrinho-qtd" style="display:flex;align-items:center;gap:8px;">
            <button class="menos-carrinho" data-index="${index}">-</button>
            <span>${item.qtd}</span>
            <button class="mais-carrinho" data-index="${index}">+</button>
          </div>
        </div>
      `;
      divItem.style.display = "flex";
      divItem.style.alignItems = "center";
      divItem.style.padding = "10px";
      divItem.style.borderRadius = "8px";
      divItem.style.background = "#fff";
      divItem.style.marginBottom = "10px";
      itensCarrinho.appendChild(divItem);
    });

    // botões + e -
    itensCarrinho.querySelectorAll(".mais-carrinho").forEach(btn => {
      btn.addEventListener("click", () => {
        const i = Number(btn.dataset.index);
        const carrinho = getCarrinho();
        carrinho[i].qtd++;
        saveCarrinho(carrinho);
        renderCarrinho();
      });
    });

    itensCarrinho.querySelectorAll(".menos-carrinho").forEach(btn => {
      btn.addEventListener("click", () => {
        const i = Number(btn.dataset.index);
        const carrinho = getCarrinho();
        if (carrinho[i].qtd > 1) {
          carrinho[i].qtd--;
        } else if (confirm("Remover este item do carrinho?")) {
          carrinho.splice(i, 1);
        }
        saveCarrinho(carrinho);
        renderCarrinho();
      });
    });

    atualizarBadgeETotais(carrinho);
  }

  // --- modal resumo ---
  function ensureModalAndFinalizeButton() {
    let btnFinalizar = document.getElementById("btn-finalizar");
    if (!btnFinalizar) {
      const main = document.querySelector(".main") || document.body;
      const resumoWrapper = document.createElement("div");
      resumoWrapper.className = "resumo-carrinho";
      resumoWrapper.innerHTML = `
        <p>Total de itens: <span id="total-itens">0</span></p>
        <p>Valor total: R$ <span id="total-valor">0.00</span></p>
      `;
      btnFinalizar = document.createElement("button");
      btnFinalizar.id = "btn-finalizar";
      btnFinalizar.textContent = "Finalizar Pedido";
      resumoWrapper.appendChild(btnFinalizar);
      main.appendChild(resumoWrapper);
    }

    let modal = document.getElementById("modal-resumo");
    if (!modal) {
      const modalHtml = `
      <div id="modal-resumo" class="modal" style="display:none;position:fixed;inset:0;justify-content:center;align-items:center;background:rgba(0,0,0,0.5);z-index:9999;">
        <div class="modal-content" style="background:#fff;padding:20px;border-radius:10px;width:90%;max-width:520px;position:relative;">
          <button class="close" aria-label="Fechar" style="position:absolute;right:12px;top:8px;font-size:1.4rem;border:none;background:transparent;cursor:pointer;">&times;</button>
          <h2>Resumo do Pedido</h2>
          <div id="resumo-itens" style="max-height:320px;overflow:auto;margin-bottom:12px;"></div>
          <p style="font-weight:600">Total: R$ <span id="resumo-total">0.00</span></p>
          <form id="form-pedido">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="endereco" placeholder="Endereço" required>
            <input type="tel" name="telefone" placeholder="Telefone" required>
            <textarea name="observacoes" placeholder="Observações"></textarea>
            <button type="submit">Confirmar Pedido</button>
          </form>
        </div>
      </div>`;
      document.body.insertAdjacentHTML("beforeend", modalHtml);
      modal = document.getElementById("modal-resumo");
    }
    return {
      btnFinalizar: document.getElementById("btn-finalizar"),
      modalResumo: document.getElementById("modal-resumo")
    };
  }

  const { btnFinalizar, modalResumo } = ensureModalAndFinalizeButton();

  function popularResumo() {
  const carrinho = getCarrinho();
  const resumoItens = document.getElementById("resumo-itens");
  resumoItens.innerHTML = "";

  if (carrinho.length === 0) {
    resumoItens.innerHTML = "<p>Seu carrinho está vazio.</p>";
    return;
  }

  carrinho.forEach((item, index) => {
    const linha = document.createElement("div");
    linha.style.display = "flex";
    linha.style.justifyContent = "space-between";
    linha.style.alignItems = "center";
    linha.style.padding = "8px 0";
    linha.style.borderBottom = "1px solid #ddd";

    linha.innerHTML = `
      <div style="display:flex;gap:10px;align-items:center;">
        <img src="${item.img || 'https://via.placeholder.com/48'}" 
             alt="${item.nome}" 
             style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
        <div>
          <div style="font-weight:600">${item.nome}</div>
          <div style="font-size:0.9rem">R$ ${Number(item.preco).toFixed(2)} x ${item.qtd}</div>
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:600">R$ ${(Number(item.preco) * item.qtd).toFixed(2)}</div>
        <div style="margin-top:6px; display:flex; gap:4px; justify-content:end;">
          <button class="menos-resumo" data-index="${index}" style="padding:2px 6px;">-</button>
          <span>${item.qtd}</span>
          <button class="mais-resumo" data-index="${index}" style="padding:2px 6px;">+</button>
          <button class="remover-item-resumo" data-index="${index}" style="margin-left:6px;padding:2px 6px;">Remover</button>
        </div>
      </div>
    `;

    resumoItens.appendChild(linha);
  });

  // Eventos dos botões de quantidade e remover
  resumoItens.querySelectorAll(".mais-resumo").forEach(btn => {
    btn.addEventListener("click", () => {
      const i = Number(btn.dataset.index);
      const carrinho = getCarrinho();
      carrinho[i].qtd++;
      saveCarrinho(carrinho);
      popularResumo();
      renderCarrinho();
    });
  });

  resumoItens.querySelectorAll(".menos-resumo").forEach(btn => {
    btn.addEventListener("click", () => {
      const i = Number(btn.dataset.index);
      const carrinho = getCarrinho();
      if (carrinho[i].qtd > 1) {
        carrinho[i].qtd--;
      } else if (confirm("Remover este item do carrinho?")) {
        carrinho.splice(i, 1);
      }
      saveCarrinho(carrinho);
      popularResumo();
      renderCarrinho();
    });
  });

  resumoItens.querySelectorAll(".remover-item-resumo").forEach(btn => {
    btn.addEventListener("click", () => {
      const i = Number(btn.dataset.index);
      const carrinho = getCarrinho();
      carrinho.splice(i, 1);
      saveCarrinho(carrinho);
      popularResumo();
      renderCarrinho();
    });
  });

  atualizarBadgeETotais(carrinho);
}


  function abrirModal() {
    popularResumo();
    modalResumo.style.display = "flex";
  }
  function fecharModal() {
    modalResumo.style.display = "none";
  }

  if (btnCarrinho) btnCarrinho.addEventListener("click", abrirModal);
  if (btnFinalizar) btnFinalizar.addEventListener("click", abrirModal);
  const closeBtn = modalResumo.querySelector(".close");
  if (closeBtn) closeBtn.addEventListener("click", fecharModal);
  modalResumo.addEventListener("click", e => {
    if (e.target === modalResumo) fecharModal();
  });

  if (btnLimpar) {
    btnLimpar.addEventListener("click", () => {
      if (confirm("Deseja esvaziar o carrinho?")) {
        localStorage.removeItem("carrinho");
        renderCarrinho();
      }
    });
  }

  if (btnPerfil) {
    btnPerfil.addEventListener("click", () => {
      window.location.href = "../PAGINAS/profile.html";
    });
  }

  const formPedido = document.getElementById("form-pedido");
  if (formPedido) {
    formPedido.addEventListener("submit", e => {
      e.preventDefault();
      const carrinho = getCarrinho();
      if (carrinho.length === 0) {
        alert("Seu carrinho está vazio.");
        return;
      }
      const dados = new FormData(formPedido);
      const nome = dados.get("nome");
      alert(`Pedido confirmado! Nome: ${nome} Total: R$ ${document.getElementById("resumo-total").textContent}`);
      localStorage.removeItem("carrinho");
      formPedido.reset();
      fecharModal();
      renderCarrinho();
    });
  }

  // --- inicialização ---
  renderCarrinho();
});
// JavaScript: adicionar funcionalidade ao botão
const btnVoltar = document.getElementById("btn-voltar");

btnVoltar.addEventListener("click", () => {
  // Volta para a página anterior
  window.history.back();
});
