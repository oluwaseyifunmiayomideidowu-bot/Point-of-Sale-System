const productTableBody = document.getElementById("products-table-body");


function formatDate(dateString) {
  const date = new Date(dateString);

  return date.toLocaleDateString("en-NG", {
    day: "2-digit",
    month: "short",
    year: "numeric"
  });
}


async function getProducts() {
  try {
    const response = await fetch("http://localhost/point_of_sale_system/backend/api/products", {
      method: "GET",
      headers: {
        "Accept": "application/json"
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const result = await response.json();

    const products = result.data;

    productTableBody.innerHTML = "";

    products.forEach(product => {
      const row = document.createElement("tr");

      row.id = `product-${product.id}`
      let stockClass;
      let stockText;

      if (product.quantity === 0) {
        stockClass = "out";
        stockText = "Out Of Stock"
      } else if (product.quantity <= product.reorder_level) {
        stockClass = "low";
        stockText = "Almost Out"
      } else {
        stockClass = "good";
        stockText = "In Stock"
      }

      row.innerHTML = `
        <td class="item">
          <span class="thumb">
            <img src="../..//point_of_sale_system/backend/${product.image}" alt="">
          </span>
          ${product.name}
        </td>
        <td>${product.category_name}</td>
        <td>${product.sku}</td>
        <td>${product.reorder_level}</td>
        
        <td>${product.quantity}</td>
        <td>${product.status}</td>
        <td>
          <div class="tag ${stockClass}">
            ${stockText}
          </div>
        </td>
        <td>${formatDate(product.created_at)}</td>
      `;

      productTableBody.appendChild(row);
    });

  } catch (error) {
    console.error("Error getting products:", error);

    productTableBody.innerHTML = `
      <tr>
        <td colspan="5">Unable to load products.</td>
      </tr>
    `;
  }
}

getProducts();

const inventoryApiUrl = 'http://localhost/point_of_sale_system/backend/api/inventory';
const inventoryTableBody = document.getElementById('inventory-table-body');
const inventorySearch = document.getElementById('inventory-search');
const supplierFilter = document.getElementById('inventory-supplier-filter');
const statusFilter = document.getElementById('inventory-status-filter');
let inventoryProducts = [];

function stockClass(status) {
  return status === 'Out of Stock' ? 'out' : status === 'Low Stock' ? 'low' : 'good';
}

function productInitials(name) {
  return name.split(/\s+/).slice(0, 2).map((word) => word[0]).join('').toUpperCase();
}

function renderInventory() {
  const query = inventorySearch.value.trim().toLowerCase();
  const supplier = supplierFilter.value;
  const status = statusFilter.value;
  const filteredProducts = inventoryProducts.filter((product) => {
    const matchesSearch = `${product.productName} ${product.sku}`.toLowerCase().includes(query);
    return matchesSearch && (!supplier || product.supplierName === supplier) && (!status || product.stockStatus === status);
  });

  inventoryTableBody.innerHTML = filteredProducts.length
    ? filteredProducts.map((product) => `<tr><td class="item"><span class="thumb">${productInitials(product.productName)}</span>${product.productName}<small>${product.sku}</small></td><td><b>${product.quantity} units</b></td><td>${product.reorderLevel} units</td><td>${product.supplierName || '—'}</td><td><span class="tag ${stockClass(product.stockStatus)}">${product.stockStatus}</span></td><td>Live inventory</td></tr>`).join('')
    : '<tr><td colspan="6" class="empty-table">No inventory matches this filter.</td></tr>';
}

function populateSupplierFilter() {
  const suppliers = [...new Set(inventoryProducts.map((product) => product.supplierName).filter(Boolean))].sort();
  supplierFilter.insertAdjacentHTML('beforeend', suppliers.map((supplier) => `<option value="${supplier}">${supplier}</option>`).join(''));
}

function applyHashFilter() {
  if (window.location.hash === '#low-stock') statusFilter.value = 'Low Stock';
}

fetch(inventoryApiUrl, { credentials: 'include' })
  .then((response) => response.ok ? response.json() : Promise.reject(new Error('Inventory is unavailable.')))
  .then((payload) => {
    if (!payload.success) throw new Error(payload.message);
    inventoryProducts = payload.data.products;
    populateSupplierFilter();
    applyHashFilter();
    renderInventory();
  })
  .catch((error) => console.warn(error.message));

inventorySearch.addEventListener('input', renderInventory);
supplierFilter.addEventListener('change', renderInventory);
statusFilter.addEventListener('change', renderInventory);
