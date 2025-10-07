$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const produtoId = urlParams.get('id');

    if(!produtoId) {
        alert('Produto não especificado.');
        window.location.href = 'produtos.html';
    }

    // Carrega dados do produto
    $.get('../php/editar_produto.php', {id: produtoId}, function(res){
        let data = JSON.parse(res);
        if(data.erro){
            alert(data.erro);
            window.location.href = 'produtos.html';
            return;
        }
        $('#nome').val(data.nome);
        $('#descricao').val(data.descricao);
        $('#preco').val(data.preco);
        $('#produto-img').attr('src', data.imagem || 'https://via.placeholder.com/600x250?text=Sem+Imagem');
        $('#produto-img').attr('alt', data.nome);
    });

    // Submeter formulário
    $('#editar-produto-form').submit(function(e){
        e.preventDefault();
        let formData = new FormData(this);
        formData.append('id', produtoId);

        $.ajax({
            url: '../php/editar_produto.php?id=' + produtoId,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res){
                let data = JSON.parse(res);
                if(data.sucesso){
                    $('#mensagem').html("<div class='alert alert-success'>"+data.sucesso+"</div>");
                    if(data.produto.imagem) $('#produto-img').attr('src', data.produto.imagem);
                } else {
                    $('#mensagem').html("<div class='alert alert-danger'>"+(data.erro||'Erro ao atualizar')+"</div>");
                }
            }
        });
    });
});
