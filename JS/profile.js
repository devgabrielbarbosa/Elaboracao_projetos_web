document.addEventListener('DOMContentLoaded', () => {
  const DEFAULT_AVATAR = '../IMG/default-avatar.png';

  // --- Elementos do DOM ---
  const btnEdit = document.getElementById("btn-edit");
  const btnSave = document.getElementById("btn-save");
  const inputs = document.querySelectorAll("#profile-form input");
  const profilePic = document.getElementById("profile-pic");
  const uploadInput = document.getElementById("upload");
  const btnRemove = document.getElementById("btn-remove");
  const btnChange = document.getElementById("btn-change");
  const toggle = document.getElementById('toggle-notif');
  const btnCarrinhoHeader = document.getElementById("btn-carrinho-header");
  const btnCarrinhoMain = document.getElementById("btn-carrinho-main");
  const btnAddCard = document.getElementById('add-card');
  const modalCard = document.getElementById('modal-card');
  const formCard = document.getElementById('form-card');
  const paymentMethods = document.querySelector('.payment-methods');
  const qtdCarrinho = document.getElementById('qtd-carrinho');
  const btnVoltar = document.getElementById("btn-voltar");

  if (!btnEdit || !btnSave || !inputs.length || !profilePic) {
    console.error("Algum elemento principal do perfil não foi encontrado no DOM.");
    return;
  }

  // --- Inicialização ---
  btnEdit.dataset.editing = 'false';

  // --- Carregar dados do localStorage ---
  const userData = JSON.parse(localStorage.getItem("userProfile")) || {};
  inputs.forEach(input => {
    if (userData[input.id]) input.value = userData[input.id];
  });

  // Atualizar display-name e display-email no topo
  document.getElementById('display-name').textContent = userData.nome || 'Nome do Cliente';
  document.getElementById('display-email').textContent = userData.email || 'exemplo@mail.com';

  // Foto do perfil
  profilePic.src = localStorage.getItem("profilePic") || DEFAULT_AVATAR;

  // Toggle notificações
  toggle.checked = JSON.parse(localStorage.getItem('notifToggle')) || false;

  // Contador do carrinho
  const updateCartCount = () => {
    const carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
    qtdCarrinho.textContent = carrinho.length;
  };
  updateCartCount();

  // --- Upload e remover foto ---
  btnChange.addEventListener('click', () => uploadInput.click());
  uploadInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
      profilePic.src = reader.result;
      localStorage.setItem('profilePic', reader.result);
    };
    reader.readAsDataURL(file);
  });
  btnRemove.addEventListener('click', () => {
    profilePic.src = DEFAULT_AVATAR;
    uploadInput.value = '';
    localStorage.removeItem('profilePic');
  });

  // --- Editar perfil ---
  btnEdit.addEventListener('click', () => {
    const editing = btnEdit.dataset.editing === 'true';
    if (!editing) {
      inputs.forEach(i => i.disabled = false);
      btnEdit.textContent = 'Cancelar';
      btnEdit.dataset.editing = 'true';
      btnSave.style.display = 'inline-block';
    } else {
      inputs.forEach(i => i.disabled = true);
      btnEdit.textContent = 'Editar Perfil';
      btnEdit.dataset.editing = 'false';
      btnSave.style.display = 'none';
    }
  });

  // --- Salvar perfil ---
  btnSave.addEventListener('click', () => {
    const updatedData = {};
    inputs.forEach(i => {
      updatedData[i.id] = i.value;
      i.disabled = true;
    });
    localStorage.setItem('userProfile', JSON.stringify(updatedData));
    btnEdit.textContent = 'Editar Perfil';
    btnEdit.dataset.editing = 'false';
    btnSave.style.display = 'none';
    // Atualiza topo
    document.getElementById('display-name').textContent = updatedData.nome || 'Nome do Cliente';
    document.getElementById('display-email').textContent = updatedData.email || 'exemplo@mail.com';
    alert("Informações salvas!");
  });

  // --- Toggle notificações ---
  toggle?.addEventListener('change', () => {
    localStorage.setItem('notifToggle', toggle.checked);
  });

  // --- Botões para ir ao carrinho ---
  [btnCarrinhoHeader, btnCarrinhoMain].forEach(btn => {
    btn?.addEventListener("click", () => {
      window.location.href = "../PAGINAS/carrinho.html";
    });
  });

  // --- Repetir pedido ---
  document.querySelectorAll(".btn-repetir").forEach(btn => {
    btn.addEventListener("click", () => {
      const produtos = JSON.parse(btn.getAttribute("data-produto"));
      let carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
      carrinho.push(...produtos);
      localStorage.setItem("carrinho", JSON.stringify(carrinho));
      updateCartCount();
      alert("Pedido adicionado ao carrinho!");
      window.location.href = "../PAGINAS/carrinho.html";
    });
  });

  // --- Modal adicionar cartão ---
  if (btnAddCard && modalCard && formCard && paymentMethods) {
    const spanClose = modalCard.querySelector('.modal-close');

    // Abrir modal
    btnAddCard.addEventListener('click', () => modalCard.style.display = 'block');
    // Fechar modal
    spanClose?.addEventListener('click', () => modalCard.style.display = 'none');
    window.addEventListener('click', e => {
      if (e.target === modalCard) modalCard.style.display = 'none';
    });

    // Renderizar cartões salvos
    const savedCards = JSON.parse(localStorage.getItem('userCards')) || [];
    savedCards.forEach(card => {
      const btnCard = document.createElement('button');
      btnCard.classList.add('card-visual');
      btnCard.innerHTML = `
        <div class="card-brand">💳</div>
        <div class="card-number">**** •••• •••• ${card.cardNumber.slice(-4)}</div>
        <div class="card-info">
          <span>Val: ${card.cardValid}</span>
          <span>CVV: •••</span>
        </div>
        <div class="card-name">${card.cardName}</div>
      `;
      paymentMethods.insertBefore(btnCard, btnAddCard);
    });

    // Salvar cartão
    formCard.addEventListener('submit', (e) => {
      e.preventDefault();
      const cardNumber = document.getElementById('card-number').value;
      const cardName = document.getElementById('card-name').value;
      const cardValid = document.getElementById('card-valid').value;
      const cardCVV = document.getElementById('card-cvv').value;

      const btnCard = document.createElement('button');
      btnCard.classList.add('card-visual');
      btnCard.innerHTML = `
        <div class="card-brand">💳</div>
        <div class="card-number">**** •••• •••• ${cardNumber.slice(-4)}</div>
        <div class="card-info">
          <span>Val: ${cardValid}</span>
          <span>CVV: •••</span>
        </div>
        <div class="card-name">${cardName}</div>
      `;
      paymentMethods.insertBefore(btnCard, btnAddCard);

      savedCards.push({ cardNumber, cardName, cardValid, cardCVV });
      localStorage.setItem('userCards', JSON.stringify(savedCards));

      modalCard.style.display = 'none';
      formCard.reset();
    });
  }
  

  const btnExcluir = document.getElementById("excluir");

  btnExcluir.addEventListener("click", () => {
    // Remove os dados da conta do localStorage
    localStorage.removeItem("usuario");


    localStorage.clear();

    // Informar usuário e redirecionar, por exemplo, para a página inicial
    alert("Sua conta foi excluída!");
    window.location.href = "../index.html"; // substitua pelo caminho da sua home
});

                         
  // --- Botão Voltar ---
  btnVoltar?.addEventListener("click", () => window.history.back());
  
  function buildUrl(path) {
  return new URL(path, window.location.origin).href;
}

const btnPerfil = document.getElementById("btn-carrinho");

btnPerfil?.addEventListener("click", e => {
  e.preventDefault();
  window.location.href = buildUrl("/PAGINAS/carrinho.html");
});

});

