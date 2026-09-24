const productTableBody = document.getElementById("products-table-body");


function formatDate(dateString) {
  const date = new Date(dateString);

  return date.toLocaleDateString("en-NG", {
    day: "2-digit",
    month: "short",
    year: "numeric"
  });
}

const inventoryApiUrl = '/point_of_sale_system/backend/api/getInventory';
const inventoryTableBody = document.getElementById('inventory-table-body');
const inventorySearch = document.getElementById('inventory-search');
const supplierFilter = document.getElementById('inventory-supplier-filter');
const statusFilter = document.getElementById('inventory-status-filter');
let inventoryProducts = [];

function getStockClass(quantity, reorderLevel) {
  let stockClass;
  if (quantity === 0) {

        stockClass = "out";

      } else if (quantity <= reorderLevel) {

        stockClass = "low";

      } else if (quantity <= reorderLevel + 10) {

        stockClass = "low";

      } else {

        stockClass = "good";
      }

  return stockClass
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
    ? filteredProducts.map((product) =>`<tr><td class="item "><span class="thumb"><img src="../..//point_of_sale_system/backend/${product.image}" alt="${product.name}"/></span>${product.productName}</td><td>${product.categoryName}</td><td>${product.sku}</td><td>${product.reorderLevel}</td><td><b>${product.quantity} ${product.quantity > 1 ? "units" : "unit"}</b></td><td>${product.status}</td><td><span  class="tag ${getStockClass(product.quantity, product.reorderLevel)}">${product.stockStatus}</span></td><td>${formatDate(product.updated_at)}</td></tr>`).join('')
    
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
