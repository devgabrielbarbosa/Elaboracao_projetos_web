document.addEventListener('DOMContentLoaded', async () => {
  const container = document.getElementById('containerProdutos');
  
  async function carregarProdutos() {
    try {
      const res = await fetch('php/home.php');
      const produtos = await res.json();
      if(produtos.erro) { alert(produtos.erro); return; }

      container.innerHTML = '';
      produtos.forEach(p => {
        const div = document.createElement('div');
        div.className = 'produto';
        div.innerHTML = `
          <img src="${p.imagem || 'https://via.placeholder.com/200'}" alt="${p.nome}" style="width:100%">
          <h3>${p.nome}</h3>
          <p>${p.descricao || ''}</p>
          <p>R$ ${parseFloat(p.preco).toFixed(2).replace('.',',')}</p>
          <button onclick="adicionarCarrinho(${p.id},${p.preco})">Adicionar ao carrinho</button>
        `;
        container.appendChild(div);
      });
    } catch(err){
      console.error(err);
      alert('Erro ao carregar produtos.');
    }
  }

  window.adicionarCarrinho = (produtoId, preco) => {
    let carrinho = JSON.parse(localStorage.getItem('carrinho')||'[]');
    const item = carrinho.find(i=>i.produto_id===produtoId);
    if(item) item.quantidade++;
    else carrinho.push({produto_id:produtoId,quantidade:1,preco_unitario:preco});
    localStorage.setItem('carrinho',JSON.stringify(carrinho));
    alert('Produto adicionado ao carrinho!');
  }

  carregarProdutos();
});
