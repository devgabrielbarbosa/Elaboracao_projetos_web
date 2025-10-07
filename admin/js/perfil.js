
document.addEventListener("DOMContentLoaded", () => {

  // Função para criar novo bloco de horário
  function criarHorario(dia, index) {
    const row = document.createElement("div");
    row.classList.add("row","mb-2","align-items-center","horario-bloco");
    row.dataset.dia = dia;

    row.innerHTML = `
      <div class="col-md-2"></div>
      <div class="col-md-4">
        <input type="time" name="horarios[${dia}][${index}][entrada]" class="form-control">
      </div>
      <div class="col-md-4">
        <input type="time" name="horarios[${dia}][${index}][saida]" class="form-control">
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
      const bloco = e.target.closest(".horario-bloco");
      const dia = bloco.dataset.dia;
      const container = bloco.parentNode;

      // Conta quantos horários já existem para esse dia
      const existentes = container.querySelectorAll(`.horario-bloco[data-dia="${dia}"]`).length;
      const novo = criarHorario(dia, existentes);
      container.appendChild(novo);
    });
  });

  // Remover horário
  document.querySelector("#horarios-container").addEventListener("click", (e) => {
    if(e.target.classList.contains("btn-remove-horario")){
      const bloco = e.target.closest(".horario-bloco");
      bloco.remove();
    }
  });

});
