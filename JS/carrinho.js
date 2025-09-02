document.addEventListener("DOMContentLoaded", () => {
  // --- Elementos principais ---
  const itensCarrinho = document.getElementById("itens-carrinho");
  const btnFinalizar = document.getElementById("btn-finalizar");
  const modalResumo = document.getElementById("modal-resumo");
  const pixDiv = document.getElementById("pix-codigo");

  // --- Helpers Storage ---
  const getCarrinho = () => JSON.parse(localStorage.getItem("carrinho")) || [];
  const saveCarrinho = (c) =>
    localStorage.setItem("carrinho", JSON.stringify(c));

  // --- Ajustar caminho da imagem ---
  const ajustarImgPath = (img) =>
    !img
      ? "https://via.placeholder.com/64"
      : img.startsWith("./")
      ? "../" + img.slice(2)
      : img;

  // --- Atualizar badge e totais ---
  function atualizarBadgeETotais(carrinho) {
    const totalItens = carrinho.reduce((acc, it) => acc + (it.qtd || 0), 0);
    const totalValor = carrinho.reduce(
      (acc, it) => acc + (it.qtd || 0) * Number(it.preco || 0),
      0
    );

    const badgeQtd = document.getElementById("qtd-carrinho");
    if (badgeQtd) badgeQtd.textContent = totalItens;

    const totalItensEl = document.getElementById("total-itens");
    const totalValorEl = document.getElementById("total-valor");
    const resumoTotalEl = document.getElementById("resumo-total");

    if (totalItensEl) totalItensEl.textContent = totalItens;
    if (totalValorEl) totalValorEl.textContent = totalValor.toFixed(2);
    if (resumoTotalEl) resumoTotalEl.textContent = totalValor.toFixed(2);
  }

  // --- Renderizar carrinho ---
  function renderCarrinho() {
    const carrinho = getCarrinho();
    if (!itensCarrinho) return;
    itensCarrinho.innerHTML = "";

    if (carrinho.length === 0) {
      itensCarrinho.innerHTML = '<p class="carrinho-vazio">Carrinho vazio</p>';
      atualizarBadgeETotais(carrinho);
      atualizarEstadoBotaoConfirmar();
      return;
    }

    carrinho.forEach((item, index) => {
      const divItem = document.createElement("div");
      divItem.className = "carrinho-item";
      divItem.style.display = "flex";
      divItem.style.alignItems = "center";
      divItem.style.padding = "10px";
      divItem.style.borderRadius = "8px";
      divItem.style.background = "#fff";
      divItem.style.marginBottom = "10px";

      divItem.innerHTML = `
        <img src="${ajustarImgPath(item.img)}" alt="${
        item.nome
      }" class="carrinho-img" style="width:64px;height:64px;object-fit:cover;border-radius:8px;margin-right:12px;">
        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span class="carrinho-nome">${item.nome}</span>
           <span class="carrinho-preco">
  R$ ${Number(item.preco).toFixed(2)} x ${item.qtd} = R$ ${(
        Number(item.preco) * item.qtd
      ).toFixed(2)}
</span>

            <button class="remover-carrinho" data-index="${index}" title="Remover item" style="margin-top:6px; background:none; border:none; cursor:pointer; color:red;">
              <i class="fas fa-trash"></i> Excluir
            </button>
          </div>
          <div class="carrinho-qtd" style="display:flex;align-items:center;gap:8px;">
            <button class="menos-carrinho" data-index="${index}">-</button>
            <span>${item.qtd}</span>
            <button class="mais-carrinho" data-index="${index}">+</button>
          </div>
        </div>
      `;
      itensCarrinho.appendChild(divItem);
    });

    // Botões + / -
    itensCarrinho.querySelectorAll(".mais-carrinho").forEach((btn) => {
      btn.addEventListener("click", () => {
        const i = Number(btn.dataset.index);
        const carrinho = getCarrinho();
        carrinho[i].qtd++;
        saveCarrinho(carrinho);
        renderCarrinho();
      });
    });

    itensCarrinho.querySelectorAll(".menos-carrinho").forEach((btn) => {
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

    // Botão remover item
    itensCarrinho.querySelectorAll(".remover-carrinho").forEach((btn) => {
      btn.addEventListener("click", () => {
        const i = Number(btn.dataset.index);
        const carrinho = getCarrinho();
        if (confirm("Você realmente deseja excluir este item do carrinho?")) {
          carrinho.splice(i, 1);
          saveCarrinho(carrinho);
          renderCarrinho();
        }
      });
    });

    atualizarBadgeETotais(carrinho);
    atualizarEstadoBotaoConfirmar();
  }

  // --- Função botão confirmar ---
  function atualizarEstadoBotaoConfirmar() {
    const formPedido = document.getElementById("form-pedido");
    if (!formPedido) return;
    const btnConfirmar = formPedido.querySelector('button[type="submit"]');
    if (!btnConfirmar) return;
    const carrinho = getCarrinho();
    btnConfirmar.disabled = carrinho.length === 0;
    btnConfirmar.style.opacity = carrinho.length === 0 ? "0.6" : "";
    btnConfirmar.title =
      carrinho.length === 0
        ? "Adicione itens ao carrinho para confirmar o pedido"
        : "";
  }

  // --- Modal abrir/fechar ---
  const abrirModal = () => {
    atualizarEstadoBotaoConfirmar();
    if (modalResumo) modalResumo.style.display = "flex";
  };
  const fecharModal = () => {
    if (modalResumo) modalResumo.style.display = "none";
  };
  if (btnFinalizar) btnFinalizar.addEventListener("click", abrirModal);
  const closeBtnModal = modalResumo?.querySelector(".close");
  if (closeBtnModal) closeBtnModal.addEventListener("click", fecharModal);
  modalResumo?.addEventListener("click", (e) => {
    if (e.target === modalResumo) fecharModal();
  });

  // --- Formulário Finalizar Pedido ---
  const formPedido = document.getElementById("form-pedido");
  formPedido?.addEventListener("submit", (e) => {
    e.preventDefault();
    const carrinho = getCarrinho();
    if (carrinho.length === 0) {
      alert("Seu carrinho está vazio.");
      return;
    }

    const dados = new FormData(formPedido);
    const nome = dados.get("nome");
    const endereco = dados.get("endereco");
    const telefone = dados.get("telefone");
    const observacoes = dados.get("observacoes");
    const pagamento = dados.get("forma-pagamento");

    // --- Criar objeto pedido ---
    const totalValor = carrinho.reduce(
      (acc, it) => acc + it.qtd * Number(it.preco),
      0
    );
    const pedidos = JSON.parse(localStorage.getItem("pedidos")) || [];
    // Dados do pedido
    const novoPedido = {
      id: pedidos.length + 1,
      cliente: nome || "Cliente Anônimo",
      endereco: endereco || "Não especificado",
      telefone: telefone || "Não especificado",
      observacoes: observacoes || "",
      pagamento: pagamento || "Não especificado",
      itens: carrinho,
      total: totalValor.toFixed(2),
      data: new Date().toLocaleString(),
      status: "pendente",
    };

    // Montando a mensagem
    let mensagem =
      `Olá! 🌸\n\nNovo pedido recebido:\n\n` +
      `Nome: ${novoPedido.cliente}\n` +
      `Endereço: ${novoPedido.endereco}\n` +
      `Telefone: ${novoPedido.telefone}\n` +
      `Itens: ${novoPedido.itens
        .map((item) => `${item.nome} x${item.quantidade}`)
        .join(", ")}\n` +
      `Total: R$ ${novoPedido.total}\n` +
      `Pagamento: ${novoPedido.pagamento}\n` +
      `Observações: ${novoPedido.observacoes || "Nenhuma"}\n\n` +
      `Obrigada pelo pedido! 💖`;

    // Número da empresa com código do país (55 para Brasil)
    const numeroEmpresa = "5563991300213";

    // Abrindo WhatsApp Web com a mensagem
    const urlWhatsApp = `https://wa.me/${numeroEmpresa}?text=${encodeURIComponent(
      mensagem
    )}`;

    // Abrindo em nova aba
    window.open(urlWhatsApp, "_blank");

    pedidos.push(novoPedido);
    localStorage.setItem("pedidos", JSON.stringify(pedidos));

    alert(
      `Pedido confirmado!\nNome: ${nome}\nTotal: R$ ${totalValor.toFixed(
        2
      )} \nPagamento: ${pagamento} enviado para ${telefone}\nEndereço: ${endereco}`
    );

    // --- Limpar carrinho e formulário ---
    localStorage.removeItem("carrinho");
    formPedido.reset();
    if (pixDiv) pixDiv.style.display = "none";
    fecharModal();
    renderCarrinho();
  });

  // --- Botão Cancelar ---
  const btnCancelar = modalResumo?.querySelector(".btn-cancelar");
  btnCancelar?.addEventListener("click", () => {
    formPedido?.reset();
    if (pixDiv) pixDiv.style.display = "none";
    fecharModal();
  });

  // --- Inicialização ---
  renderCarrinho();
  atualizarEstadoBotaoConfirmar();

  // --- Botão voltar ---
  const btnVoltar = document.getElementById("btn-voltar");

  if (btnVoltar) {
    btnVoltar.addEventListener("click", () => {
      window.history.back();
    });
  }
function buildUrl(path) {
  return new URL(path, window.location.origin).href;
}

const btnPerfil = document.getElementById("btn-perfil");

btnPerfil?.addEventListener("click", e => {
  e.preventDefault();
  window.location.href = buildUrl("./PAGINAS/profile.html");
});

  // --- Inicialização ---
  renderCarrinho();
  // também garante estado inicial do botão confirmar caso o formulário exista
  atualizarEstadoBotaoConfirmar();
});
