document.addEventListener("DOMContentLoaded", () => {

  function criarHorario(dia, index, entrada = '', saida = '') {
    const row = document.createElement("div");
    row.classList.add("row","mb-2","align-items-center","horario-bloco");
    row.dataset.dia = dia;

    row.innerHTML = `
      <div class="col-md-2">${dia}</div>
      <div class="col-md-4">
        <input type="time" name="horarios[${dia}][${index}][entrada]" class="form-control" value="${entrada}">
      </div>
      <div class="col-md-4">
        <input type="time" name="horarios[${dia}][${index}][saida]" class="form-control" value="${saida}">
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-sm btn-danger btn-remove-horario">-</button>
      </div>
    `;
    return row;
  }

  // Adicionar novo horário
  document.querySelectorAll(".btn-add-horario").forEach(btn => {
    btn.addEventListener("click", (e) => {
      const bloco = e.target.closest(".horario-dia");
      const dia = bloco.dataset.dia;
      const container = bloco.querySelector(".horarios-container");

      const existentes = container.querySelectorAll(`.horario-bloco`).length;
      const novo = criarHorario(dia, existentes);
      container.appendChild(novo);
    });
  });

  // Remover horário
  document.querySelectorAll(".horarios-container").forEach(container => {
    container.addEventListener("click", (e) => {
      if(e.target.classList.contains("btn-remove-horario")){
        e.target.closest(".horario-bloco").remove();
      }
    });
  });

  // Ao enviar o formulário de perfil, envia os horários
  const formPerfil = document.getElementById('formPerfil');
  if(formPerfil){
    formPerfil.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(formPerfil);

      try {
        const res = await fetch('../php/perfil_loja.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });
        const data = await res.json();
        if(data.sucesso) alert(data.sucesso);
        else alert(data.erro || 'Erro ao salvar perfil.');
      } catch (err) {
        console.error(err);
        alert('Erro na requisição.');
      }
    });
  }

});
