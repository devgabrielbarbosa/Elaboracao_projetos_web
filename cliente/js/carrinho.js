document.addEventListener('DOMContentLoaded', ()=>{
  const lista = document.getElementById('listaCarrinho');
  const totalEl = document.getElementById('totalCarrinho');
  const btnFinalizar = document.getElementById('btnFinalizar');

  function atualizarCarrinho(){
    const carrinho = JSON.parse(localStorage.getItem('carrinho')||'[]');
    lista.innerHTML='';
    let total=0;
    carrinho.forEach(i=>{
      total+=i.quantidade*i.preco_unitario;
      const li = document.createElement('li');
      li.textContent=`Produto ${i.produto_id} x ${i.quantidade} - R$ ${ (i.quantidade*i.preco_unitario).toFixed(2).replace('.',',') }`;
      lista.appendChild(li);
    });
    totalEl.textContent='R$ '+total.toFixed(2).replace('.',',');
  }

  btnFinalizar.addEventListener('click', async ()=>{
    const carrinho = JSON.parse(localStorage.getItem('carrinho')||'[]');
    if(carrinho.length===0){ alert('Carrinho vazio'); return; }

    const res = await fetch('php/carrinho.php',{method:'POST',body:JSON.stringify(carrinho)});
    const json = await res.json();
    if(json.sucesso){
      alert('Pedido realizado com sucesso! ID: '+json.pedido_id);
      localStorage.removeItem('carrinho');
      atualizarCarrinho();
    } else alert(json.erro);
  });

  atualizarCarrinho();
});
