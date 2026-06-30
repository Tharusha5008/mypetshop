
const CART_KEY = 'mealtime_cart';

function getCart(){
  try{
    return JSON.parse(localStorage.getItem(CART_KEY)) || [];
  }catch(e){
    return [];
  }
}

function saveCart(cart){
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
}

function addToCart(product){
  const cart = getCart();
  const existing = cart.find(i => i.id === product.id);
  if(existing){ existing.qty++; }
  else { cart.push({...product, qty:1}); }
  saveCart(cart);
}

function changeCartQty(id, delta){
  const cart = getCart();
  const item = cart.find(i => i.id === id);
  if(!item) return;
  item.qty += delta;
  const updated = item.qty <= 0 ? cart.filter(i => i.id !== id) : cart;
  saveCart(updated);
  return updated;
}

function removeFromCart(id){
  const cart = getCart().filter(i => i.id !== id);
  saveCart(cart);
  return cart;
}

function clearCart(){
  saveCart([]);
}

function cartCount(){
  return getCart().reduce((s,i) => s + i.qty, 0);
}

function cartSubtotal(){
  return getCart().reduce((s,i) => s + i.price * i.qty, 0);
}

function updateCartBadge(){
  const el = document.getElementById('cartCount');
  if(el) el.textContent = cartCount();
}

document.addEventListener('DOMContentLoaded', updateCartBadge);
