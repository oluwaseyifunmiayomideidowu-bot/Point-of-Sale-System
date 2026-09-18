const products = [
  ['Lavazza Qualità Rossa', 'Beverages', 8500, 'BEV-001', 18, 'LR'], ['Fresh Orange Juice', 'Beverages', 3200, 'BEV-002', 9, 'OJ'], ['Coca-Cola Zero 500ml', 'Beverages', 850, 'BEV-003', 6, 'CZ'], ['Maltina Can', 'Beverages', 900, 'BEV-004', 22, 'MC'], ['Pepsi 50cl', 'Beverages', 800, 'BEV-005', 15, 'PP'], ['Bottled Water 75cl', 'Beverages', 500, 'BEV-006', 30, 'BW'], ['Schweppes Tonic', 'Beverages', 1200, 'BEV-007', 11, 'ST'], ['Red Bull Energy Drink', 'Beverages', 2300, 'BEV-008', 8, 'RB'], ['Lipton Ice Tea', 'Beverages', 1100, 'BEV-009', 17, 'LI'], ['Five Alive Pulpy', 'Beverages', 1000, 'BEV-010', 14, 'FA'],
  ['Extra Virgin Olive Oil', 'Pantry', 7500, 'PAN-001', 7, 'EO'], ['Golden Penny Spaghetti', 'Pantry', 1300, 'PAN-002', 26, 'GS'], ['Indomie Chicken Noodles', 'Pantry', 700, 'PAN-003', 40, 'IN'], ['Dangote Sugar 1kg', 'Pantry', 2200, 'PAN-004', 12, 'DS'], ['Peak Milk Tin', 'Pantry', 1650, 'PAN-005', 10, 'PM'], ['Honeywell Semolina', 'Pantry', 2400, 'PAN-006', 4, 'HS'], ['Bama Mayonnaise', 'Pantry', 1850, 'PAN-007', 16, 'BM'], ['Titus Sardines', 'Pantry', 1350, 'PAN-008', 5, 'TS'], ['Devon Kings Vegetable Oil', 'Pantry', 3800, 'PAN-009', 13, 'DV'], ['Nasco Corn Flakes', 'Pantry', 2700, 'PAN-010', 9, 'NC'],
  ['Farmhouse Milk 1L', 'Dairy & Eggs', 2500, 'DAI-001', 0, 'FM'], ['Fresh Brown Eggs 12 Pack', 'Dairy & Eggs', 3200, 'DAI-002', 18, 'FE'], ['Dano Full Cream Milk', 'Dairy & Eggs', 1900, 'DAI-003', 14, 'DM'], ['President Butter', 'Dairy & Eggs', 4200, 'DAI-004', 6, 'PB'], ['Hollandia Yoghurt', 'Dairy & Eggs', 1450, 'DAI-005', 20, 'HY'], ['Arla Cheddar Slices', 'Dairy & Eggs', 3600, 'DAI-006', 7, 'AC'], ['Nunu Plain Yoghurt', 'Dairy & Eggs', 1250, 'DAI-007', 16, 'NY'], ['Farm Fresh Sour Cream', 'Dairy & Eggs', 2900, 'DAI-008', 5, 'FS'], ['Lurpak Spreadable', 'Dairy & Eggs', 5100, 'DAI-009', 8, 'LS'], ['Goat Cheese Crumbles', 'Dairy & Eggs', 4600, 'DAI-010', 11, 'GC'],
].map(([name, category, price, sku, stock, initials]) => ({ name, category, price, sku, stock, initials }));

const cart = [];
const vatRate = 0.075;
const productGrid = document.getElementById('pos-products');
const productSearch = document.getElementById('product-search');
const categoryButtons = document.querySelectorAll('[data-category]');
const cartItems = document.getElementById('cart-items');
const clearCartButton = document.getElementById('clear-cart');
const completeSaleButton = document.getElementById('complete-sale');
const discountToggle = document.getElementById('discount-toggle');
const discountControl = document.getElementById('discount-control');
const discountAmount = document.getElementById('discount-amount');
const amountReceived = document.getElementById('amount-received');
const paymentButtons = document.querySelectorAll('.pay-options button');
const saleStatus = document.getElementById('sale-status');
let selectedCategory = 'All';
let paymentMethod = 'Cash';

function formatNaira(amount) {
  return `₦${Math.max(0, amount).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function getCartTotals() {
  const subtotal = cart.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
  const discount = Math.min(Number(discountAmount.value) || 0, subtotal);
  const taxableAmount = subtotal - discount;
  const vat = taxableAmount * vatRate;
  return { subtotal, discount, vat, total: taxableAmount + vat };
}

function updateTotals() {
  const { subtotal, vat, total } = getCartTotals();
  const received = Number(amountReceived.value) || 0;

  document.getElementById('cart-subtotal').textContent = formatNaira(subtotal);
  document.getElementById('cart-vat').textContent = formatNaira(vat);
  document.getElementById('cart-total').textContent = formatNaira(total);
  document.getElementById('complete-sale-total').textContent = formatNaira(total);
  document.getElementById('cart-change').textContent = formatNaira(Math.max(0, received - total));
  completeSaleButton.disabled = cart.length === 0 || received < total;
}

function renderCart() {
  cartItems.innerHTML = cart.length
    ? cart.map((item, index) => `<article class="cart-row"><span class="cart-monogram">${item.product.initials}</span><div><strong>${item.product.name}</strong><small>${formatNaira(item.product.price)} each</small><span class="quantity-stepper"><button type="button" data-cart-action="decrease" data-cart-index="${index}" aria-label="Decrease ${item.product.name} quantity">−</button><b>${item.quantity}</b><button type="button" data-cart-action="increase" data-cart-index="${index}" aria-label="Increase ${item.product.name} quantity">+</button></span></div><aside><b>${formatNaira(item.product.price * item.quantity)}</b><button class="remove-item" type="button" data-cart-action="remove" data-cart-index="${index}">Remove</button></aside></article>`).join('')
    : '<div class="empty-cart"><b>□</b><strong>Your cart is empty</strong><small>Choose products to start a sale.</small></div>';
  updateTotals();
}

function renderProducts() {
  const searchTerm = productSearch.value.trim().toLowerCase();
  const visibleProducts = products.filter((product) => {
    const matchesCategory = selectedCategory === 'All' || product.category === selectedCategory;
    return matchesCategory && `${product.name} ${product.sku}`.toLowerCase().includes(searchTerm);
  });

  productGrid.innerHTML = visibleProducts.length
    ? visibleProducts.map((product) => {
      const state = product.stock === 0 ? ['Out of stock', 'out'] : product.stock <= 7 ? ['Low stock', 'low'] : [`${product.stock} in stock`, ''];
      return `<button class="pos-product" type="button" data-product-sku="${product.sku}" ${product.stock === 0 ? 'disabled' : ''}><span class="picture">${product.initials}</span><strong>${product.name}</strong><small>${formatNaira(product.price)}</small><span class="product-meta ${state[1]}">SKU ${product.sku} · ${state[0]}</span></button>`;
    }).join('')
    : '<p class="no-products">No products match this search.</p>';
}

productGrid.addEventListener('click', (event) => {
  const productButton = event.target.closest('[data-product-sku]');
  if (!productButton) return;

  const product = products.find((item) => item.sku === productButton.dataset.productSku);
  const cartItem = cart.find((item) => item.product.sku === product.sku);
  if (cartItem) cartItem.quantity += 1;
  else cart.push({ product, quantity: 1 });

  saleStatus.textContent = '';
  renderCart();
});

cartItems.addEventListener('click', (event) => {
  const actionButton = event.target.closest('[data-cart-action]');
  if (!actionButton) return;

  const index = Number(actionButton.dataset.cartIndex);
  const item = cart[index];
  if (!item) return;

  if (actionButton.dataset.cartAction === 'increase') item.quantity += 1;
  if (actionButton.dataset.cartAction === 'decrease') item.quantity -= 1;
  if (actionButton.dataset.cartAction === 'remove' || item.quantity <= 0) cart.splice(index, 1);
  renderCart();
});

categoryButtons.forEach((button) => {
  button.addEventListener('click', () => {
    selectedCategory = button.dataset.category;
    categoryButtons.forEach((item) => item.classList.toggle('active', item === button));
    renderProducts();
  });
});

paymentButtons.forEach((button) => {
  button.addEventListener('click', () => {
    paymentMethod = button.textContent.trim();
    paymentButtons.forEach((item) => item.classList.toggle('active', item === button));
  });
});

discountToggle.addEventListener('click', () => {
  discountControl.hidden = !discountControl.hidden;
  discountToggle.textContent = discountControl.hidden ? 'Add' : 'Remove';
  if (!discountControl.hidden) discountAmount.focus();
  else {
    discountAmount.value = 0;
    updateTotals();
  }
});

productSearch.addEventListener('input', renderProducts);
discountAmount.addEventListener('input', updateTotals);
amountReceived.addEventListener('input', updateTotals);

clearCartButton.addEventListener('click', () => {
  cart.length = 0;
  amountReceived.value = '';
  discountAmount.value = 0;
  discountControl.hidden = true;
  discountToggle.textContent = 'Add';
  saleStatus.textContent = '';
  renderCart();
});

completeSaleButton.addEventListener('click', () => {
  const { total } = getCartTotals();
  const received = Number(amountReceived.value) || 0;
  if (!cart.length || received < total) return;

  const receiptNumber = `VN-${String(Math.floor(10000 + Math.random() * 90000))}`;
  saleStatus.textContent = `Sale completed by ${paymentMethod}. Receipt ${receiptNumber} is ready to print — ${formatNaira(total)}.`;
  cart.length = 0;
  amountReceived.value = '';
  discountAmount.value = 0;
  discountControl.hidden = true;
  discountToggle.textContent = 'Add';
  renderCart();
});

renderProducts();
renderCart();
