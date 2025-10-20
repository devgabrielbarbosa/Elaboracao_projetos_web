document.addEventListener("DOMContentLoaded", () => {

  const diasSemana = ['Segunda','Terca','Quarta','Quinta','Sexta','Sabado','Domingo'];
  const containerHorarios = document.getElementById('horariosContainer');
  const logoInput = document.getElementById('logo');
  const logoPreview = document.getElementById('logoPreview');
  const formPerfil = document.getElementById('formPerfil');
  const respostaDiv = document.getElementById('resposta');

  // Função para criar bloco de horário
  function criarHorario(dia, hora_abertura='', hora_fechamento='', status='aberto') {
    const row = document.createElement("div");
    row.classList.add("row","mb-2","align-items-center","horario-bloco");
    row.dataset.dia = dia;

    row.innerHTML = `
      <div class="col-md-2 horario-dia-title">${dia}</div>
      <div class="col-md-3">
        <input type="time" name="horarios[${dia}][hora_abertura]" class="form-control" value="${hora_abertura}">
      </div>
      <div class="col-md-3">
        <input type="time" name="horarios[${dia}][hora_fechamento]" class="form-control" value="${hora_fechamento}">
      </div>
      <div class="col-md-2">
        <select name="horarios[${dia}][status]" class="form-select">
          <option value="aberto" ${status==='aberto'?'selected':''}>Aberto</option>
          <option value="fechado" ${status==='fechado'?'selected':''}>Fechado</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-sm btn-danger btn-remove-horario">-</button>
      </div>
    `;
    return row;
  }

  // Remover horário
  containerHorarios.addEventListener('click', e => {
    if(e.target.classList.contains('btn-remove-horario')){
      e.target.closest('.horario-bloco').remove();
    }
  });

  // Pré-visualização do logo
  if(logoInput && logoPreview){
    logoInput.addEventListener('change', e => {
      const file = e.target.files[0];
      if(file){
        const reader = new FileReader();
        reader.onload = () => { logoPreview.src = reader.result; };
        reader.readAsDataURL(file);
      } else {
        logoPreview.src = '';
      }
    });
  }

  // Carregar dados do perfil
  async function carregarPerfil(){
    try {
      const res = await fetch('../php/perfil_loja_dados.php', { credentials:'same-origin' });
      const data = await res.json();
      if(data.loja){
        const loja = data.loja;

        document.getElementById('nome').value = loja.nome || '';
        document.getElementById('telefone').value = loja.telefone || '';
        document.getElementById('email').value = loja.email || '';
        document.getElementById('endereco').value = loja.endereco || '';
        document.getElementById('cidade').value = loja.cidade || '';
        document.getElementById('estado').value = loja.estado || '';
        document.getElementById('cep').value = loja.cep || '';
        document.getElementById('taxa_entrega_padrao').value = loja.taxa_entrega_padrao || '';
        document.getElementById('status').value = loja.status || 'ativa';
        document.getElementById('mensagem').value = loja.mensagem || '';

        // Logo
        if(loja.logo){
          logoPreview.src = 'data:image/*;base64,' + btoa(loja.logo);
        }

        // Horários
        containerHorarios.innerHTML = '';
        diasSemana.forEach(dia => {
          const horario = loja.horarios[dia] || {hora_abertura:'', hora_fechamento:'', status:'aberto'};
          containerHorarios.appendChild(criarHorario(dia, horario.hora_abertura, horario.hora_fechamento, horario.status));
        });

      } else {
        respostaDiv.innerHTML = `<div class="alert alert-danger">${data.erro}</div>`;
      }
    } catch(err){
      console.error(err);
      respostaDiv.innerHTML = `<div class="alert alert-danger">Erro ao carregar perfil.</div>`;
    }
  }

  // Envio do formulário via AJAX
  if(formPerfil){
    formPerfil.addEventListener('submit', async (e) => {
      e.preventDefault();
      respostaDiv.innerHTML = '';

      const formData = new FormData(formPerfil);

      try {
        const res = await fetch('../php/perfil_loja.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        const data = await res.json();
        if(data.sucesso){
          respostaDiv.innerHTML = `<div class="alert alert-success">${data.sucesso}</div>`;
        } else {
          respostaDiv.innerHTML = `<div class="alert alert-danger">${data.erro || 'Erro ao salvar perfil.'}</div>`;
        }

      } catch(err){
        console.error(err);
        respostaDiv.innerHTML = `<div class="alert alert-danger">Erro na requisição.</div>`;
      }
    });
  }

  carregarPerfil();

});
