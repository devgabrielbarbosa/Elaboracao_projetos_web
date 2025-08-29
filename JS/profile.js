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
  const btnCarrinho = document.getElementById("btn-carrinho");
  const btnAddCard = document.getElementById('add-card');
  const modalCard = document.getElementById('modal-card');
  const formCard = document.getElementById('form-card');
  const paymentMethods = document.querySelector('.payment-methods');

  if (!btnEdit || !btnSave || !inputs || !profilePic) {
    console.error("Algum elemento principal do perfil não foi encontrado no DOM.");
    return;
  }

  // --- Carregar dados do localStorage ---
  const userData = JSON.parse(localStorage.getItem("userProfile")) || {};
  inputs.forEach(input => {
    if (userData[input.id]) input.value = userData[input.id];
  });
  profilePic.src = localStorage.getItem("profilePic") || DEFAULT_AVATAR;

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
    document.getElementById('display-name').textContent = updatedData.nome || 'Nome do Cliente';
    document.getElementById('display-email').textContent = updatedData.email || 'exemplo@mail.com';
    alert("Informações salvas!");
  });

  // --- Toggle notificações ---
  toggle?.addEventListener('change', () => {
    console.log('Notificações:', toggle.checked);
  });

  // --- Repetir pedido (adiciona ao carrinho) ---
  document.querySelectorAll(".btn-repetir").forEach(btn => {
    btn.addEventListener("click", () => {
      const produtos = JSON.parse(btn.getAttribute("data-produto"));
      let carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
      carrinho.push(...produtos);
      localStorage.setItem("carrinho", JSON.stringify(carrinho));
      alert("Pedido adicionado ao carrinho!");
      window.location.href = "../PAGINAS/carrinho.html";
    });
  });

  // --- Botão para ir ao carrinho ---
  btnCarrinho?.addEventListener("click", () => {
    window.location.href = "../PAGINAS/carrinho.html";
  });

  // --- Modal adicionar cartão ---
  if (btnAddCard && modalCard && formCard && paymentMethods) {
    const spanClose = modalCard.querySelector('.modal-close');

    // Abrir modal
    btnAddCard.addEventListener('click', () => {
      modalCard.style.display = 'block';
    });

    // Fechar modal
    spanClose?.addEventListener('click', () => {
      modalCard.style.display = 'none';
    });
    window.addEventListener('click', e => {
      if (e.target === modalCard) modalCard.style.display = 'none';
    });

    // Salvar cartão
    formCard.addEventListener('submit', (e) => {
      e.preventDefault();
      const cardNumber = document.getElementById('card-number').value;
      const cardName = document.getElementById('card-name').value;
      const cardValid = document.getElementById('card-valid').value;
      const cardCVV = document.getElementById('card-cvv').value;

      // Criar botão visual do cartão
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

      // Salvar no localStorage
      let savedCards = JSON.parse(localStorage.getItem('userCards')) || [];
      savedCards.push({ cardNumber, cardName, cardValid, cardCVV });
      localStorage.setItem('userCards', JSON.stringify(savedCards));

      // Fechar modal e resetar form
      modalCard.style.display = 'none';
      formCard.reset();
    });
  } else {
    console.warn("Elementos do modal não encontrados. Modal de cartão não será funcional.");
  }
});



// JavaScript: adicionar funcionalidade ao botão
const btnVoltar = document.getElementById("btn-voltar");

btnVoltar.addEventListener("click", () => {
  // Volta para a página anterior
  window.history.back();
});
