const productGrid = document.getElementById("productsContainer");

let products = [];

const cart = [];
const vatRate = 0.075;

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
  return `₦${Math.max(0, Number(amount) || 0).toLocaleString('en-NG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })}`;
}


async function getProducts() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/products",
      {
        method: "GET",
        headers: {
          "Accept": "application/json"
        },
        credentials: "include"
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const data = await response.json();

    products = data.data || [];

    renderProducts();

  } catch (error) {
    console.error("Error getting products:", error);

    productGrid.innerHTML = `
      <div>Unable to load products.</div>
    `;
  }
}
const categoryButtonsContainer =
  document.getElementById("categoryButtons");

async function getCategories() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/categories",
      {
        method: "GET",
        headers: {
          "Accept": "application/json"
        },
        credentials: "include"
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const data = await response.json();

    const categories = data.data || [];

    categoryButtonsContainer.innerHTML = "";

    // Default "All" button
    const allButton = document.createElement("button");

    allButton.type = "button";
    allButton.className = "active";
    allButton.dataset.category = "All";
    allButton.textContent = "All";

    allButton.addEventListener("click", () => {
      selectedCategory = "All";

      document
        .querySelectorAll("[data-category]")
        .forEach((button) => {
          button.classList.toggle(
            "active",
            button === allButton
          );
        });

      renderProducts();
    });

    categoryButtonsContainer.appendChild(allButton);


    // Categories from API
    categories.forEach((category) => {

      const button = document.createElement("button");

      button.type = "button";
      button.className = "category-button";
      button.dataset.category = category.name;
      button.textContent = category.name;

      button.addEventListener("click", () => {

        selectedCategory = category.name;

        document
          .querySelectorAll("[data-category]")
          .forEach((item) => {
            item.classList.toggle(
              "active",
              item === button
            );
          });

        renderProducts();
      });

      categoryButtonsContainer.appendChild(button);
    });

  } catch (error) {
    console.error("Error getting categories:", error);

    categoryButtonsContainer.innerHTML = `
      <p>Unable to load categories.</p>
    `;
  }
}

async function completeSale() {

  if (cart.length === 0) {
    return;
  }

  const {
    subtotal,
    discount,
    vat,
    total
  } = getCartTotals();

  const amountPaid =
    Number(amountReceived.value) || 0;

  if (amountPaid < total) {
    return;
  }


  const saleData = {

    items: cart.map((item) => ({
      productId: item.product.id,
      quantity: item.quantity
    })),

    discount: discount,

    paymentMethod: paymentMethod,

    amountPaid: amountPaid

  };


  try {

    const response = await fetch(
      "/point_of_sale_system/backend/api/createSale",
      {
        method: "POST",

        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },

        credentials: "include",

        body: JSON.stringify(saleData)
      }
    );


    const data = await response.json();


    if (!response.ok) {
      throw new Error(
        data.message || "Failed to complete sale."
      );
    }


    console.log("Sale completed:", data);


    // Clear cart
    cart.length = 0;

    amountReceived.value = "";

    discountAmount.value = 0;

    discountControl.hidden = true;

    discountToggle.textContent = "Add";

    saleStatus.textContent =
      "Sale completed successfully.";

    renderCart();


  } catch (error) {

    console.error("Create sale failed:", error);

    saleStatus.textContent =
      error.message || "Unable to complete sale.";

  }
}


completeSaleButton.addEventListener(
  "click",
  completeSale
);
getCategories();


function renderProducts() {

  const searchTerm = productSearch.value.trim().toLowerCase();

  const visibleProducts = products.filter((product) => {

    const matchesCategory =
      selectedCategory === 'All' ||
      product.category_name === selectedCategory;

    const matchesSearch =
      `${product.name} ${product.sku}`
        .toLowerCase()
        .includes(searchTerm);

    return matchesCategory && matchesSearch;
  });


  productGrid.innerHTML = visibleProducts.length

    ? visibleProducts.map((product) => {

        const quantity = Number(product.quantity) || 0;
        const reorderLevel = Number(product.reorder_level) || 0;

        let state;

        if (quantity === 0) {
          state = ['Out of stock', 'out'];

        } else if (quantity <= reorderLevel) {
          state = ['Low stock', 'low'];

        } else {
          state = [`${quantity} in stock`, ''];
        }


        return `
          <button
            class="pos-product"
            type="button"
            data-product-id="${product.id}"
            ${quantity === 0 ? 'disabled' : ''}
          >

            <span class="picture">

              <img
                src="../..//point_of_sale_system/backend/${product.image}"
                alt="${product.name}"
              >

            </span>

            <div class="picture-text">
            <strong>${product.name}</strong>

            <small>
              ${formatNaira(product.selling_price)}
            </small>

            <span class="product-meta ${state[1]}">
              SKU ${product.sku} · ${state[0]}
            </span>
            </div>
          </button>
        `;

      }).join('')

    : '<p class="no-products">No products match this search.</p>';
}

function getCartTotals() {

  const subtotal = cart.reduce((sum, item) => sum + Number(item.product.selling_price) * item.quantity,0);


  const discount = Math.min(
    Number(discountAmount.value) || 0,
    subtotal
  );


  const taxableAmount = subtotal - discount;

  const vat = taxableAmount * vatRate;

  const total = taxableAmount + vat;


  return {
    subtotal,
    discount,
    vat,
    total
  };
}


function updateTotals() {

  const {
    subtotal,
    vat,
    total
  } = getCartTotals();


  const received =
    Number(amountReceived.value) || 0;


  document.getElementById('cart-subtotal').textContent =
    formatNaira(subtotal);

  document.getElementById('cart-vat').textContent =
    formatNaira(vat);

  document.getElementById('cart-total').textContent =
    formatNaira(total);

  document.getElementById('complete-sale-total').textContent =
    formatNaira(total);

  document.getElementById('cart-change').textContent =
    formatNaira(
      Math.max(0, received - total)
    );

  completeSaleButton.disabled =
    cart.length === 0 ||
    received < total;
}


function renderCart() {

  cartItems.innerHTML = cart.length

    ? cart.map((item, index) => `

      <article class="cart-row">

        <span class="cart-monogram">
         <img
                src="../..//point_of_sale_system/backend/${item.product.image}"
                alt="${item.product.name}"
              >
        </span>


        <div>

          <strong>
            ${item.product.name}
          </strong>


          <small>
            ${formatNaira(item.product.selling_price)}
            each
          </small>


          <span class="quantity-stepper">

            <button
              type="button"
              data-cart-action="decrease"
              data-cart-index="${index}"
              aria-label="Decrease ${item.product.name} quantity"
            >
              −
            </button>


            <b>
              ${item.quantity}
            </b>


            <button
              type="button"
              data-cart-action="increase"
              data-cart-index="${index}"
              aria-label="Increase ${item.product.name} quantity"
            >
              +
            </button>

          </span>

        </div>


        <aside>

          <b>
            ${formatNaira(
              Number(item.product.selling_price) *
              item.quantity
            )}
          </b>


          <button
            class="remove-item"
            type="button"
            data-cart-action="remove"
            data-cart-index="${index}"
          >
            Remove
          </button>

        </aside>

      </article>

    `).join('')

    : `
      <div class="empty-cart">

        <b>□</b>

        <strong>
          Your cart is empty
        </strong>

        <small>
          Choose products to start a sale.
        </small>

      </div>
    `;


  updateTotals();
}


productGrid.addEventListener('click', (event) => {

  const productButton =
    event.target.closest('[data-product-id]');


  if (!productButton) return;


  const product =
    products.find(
      (item) =>
        String(item.id) ===
        productButton.dataset.productId
    );


  if (!product) return;


  const cartItem =
    cart.find(
      (item) =>
        item.product.id === product.id
    );


  if (cartItem) {

    // Don't allow selling more than available stock
    if (cartItem.quantity >= Number(product.quantity)) {
      return;
    }

    cartItem.quantity += 1;

  } else {

    cart.push({
      product: product,
      quantity: 1
    });

  }


  saleStatus.textContent = '';

  renderCart();
});


cartItems.addEventListener('click', (event) => {

  const actionButton =
    event.target.closest('[data-cart-action]');


  if (!actionButton) return;


  const index =
    Number(actionButton.dataset.cartIndex);


  const item = cart[index];


  if (!item) return;


  const action =
    actionButton.dataset.cartAction;


  if (action === 'increase') {

    if (
      item.quantity <
      Number(item.product.quantity)
    ) {
      item.quantity += 1;
    }

  }


  if (action === 'decrease') {

    item.quantity -= 1;

  }


  if (
    action === 'remove' ||
    item.quantity <= 0
  ) {

    cart.splice(index, 1);

  }


  renderCart();
});


categoryButtons.forEach((button) => {

  button.addEventListener('click', () => {

    selectedCategory =
      button.dataset.category;


    categoryButtons.forEach((item) => {

      item.classList.toggle(
        'active',
        item === button
      );

    });


    renderProducts();

  });

});


paymentButtons.forEach((button) => {

  button.addEventListener('click', () => {

    paymentMethod =
      button.textContent.trim();


    paymentButtons.forEach((item) => {

      item.classList.toggle(
        'active',
        item === button
      );

    });

  });

});


discountToggle.addEventListener('click', () => {

  discountControl.hidden =
    !discountControl.hidden;


  discountToggle.textContent =
    discountControl.hidden
      ? 'Add'
      : 'Remove';


  if (!discountControl.hidden) {

    discountAmount.focus();

  } else {

    discountAmount.value = 0;

    updateTotals();

  }

});

productSearch.addEventListener(
  'input',
  renderProducts
);

discountAmount.addEventListener(
  'input',
  updateTotals
);

amountReceived.addEventListener(
  'input',
  updateTotals
);

clearCartButton.addEventListener('click', () => {

  cart.length = 0;

  amountReceived.value = '';

  discountAmount.value = 0;

  discountControl.hidden = true;

  discountToggle.textContent = 'Add';

  saleStatus.textContent = '';

  renderCart();

});


completeSaleButton.addEventListener(
  'click',
  completeSale
);






getProducts();

renderCart();