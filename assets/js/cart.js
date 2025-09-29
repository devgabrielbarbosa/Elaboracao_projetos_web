const CART_KEY = 'delivery_cart';

function getCart() { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); }
function saveCart(cart) { localStorage.setItem(CART_KEY, JSON.stringify(cart)); }

document.querySelectorAll('.add-cart').forEach(btn => {
    btn.addEventListener('click', () => {
        const cart = getCart();
        const id = btn.dataset.id;
        const nome = btn.dataset.nome;
        const preco = Number(btn.dataset.preco);
        const item = cart.find(i => i.id == id);
        if(item) item.qtd++;
        else cart.push({id, nome, preco, qtd:1});
        saveCart(cart);
        alert('Adicionado ao carrinho!');
    });
});
