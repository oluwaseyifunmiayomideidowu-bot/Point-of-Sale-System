const dashboardApiUrl = 'http://localhost/point_of_sale_system/backend/api/reports/dashboard-summary';

const currency = (value) => `₦${Number(value || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

function categoryHealthLabel(category) {
  if (category.reorderQuantity === 0) return 'No reorder target';
  return `${category.currentQuantity} units / reorder target ${category.reorderQuantity}`;
}

function initials(value) {
  return value.split(/\s+/).slice(0, 2).map((word) => word[0]).join('').toUpperCase();
}

function renderDashboard(data) {
  document.getElementById('today-sales-value').textContent = currency(data.today.totalRevenue);
  document.getElementById('purchases-mtd-value').textContent = `${data.today.totalSales} sales today`;
  document.getElementById('active-products-value').textContent = data.inventory.totalProducts;
  document.getElementById('low-stock-value').textContent = data.inventory.lowStockProducts;

  const categoryProgress = document.getElementById('category-progress');
  categoryProgress.innerHTML = data.categories.length
    ? data.categories.map((category) => `<div><span><b>${category.name}</b><small>${category.healthPercent}% · ${categoryHealthLabel(category)}</small></span><i><em style="width:${category.healthPercent}%"></em></i></div>`).join('')
    : '<p class="dashboard-empty">No active inventory categories yet.</p>';

  const alertList = document.getElementById('stock-alert-list');
  alertList.innerHTML = data.stockAlerts.length
    ? data.stockAlerts.map((item) => `<div><b class="alert-ico">${initials(item.name)}</b><span><strong>${item.name}</strong><small>${item.quantity} left · Reorder at ${item.reorderLevel}</small></span><a class="mini-action" href="inventory.html#low-stock">Restock</a></div>`).join('')
    : '<p class="dashboard-empty">No low-stock products right now.</p>';

  const recentSales = document.getElementById('recent-sales-list');
  recentSales.innerHTML = data.recentSales.length
    ? data.recentSales.map((sale) => `<div class="recent-row"><span><strong>${sale.saleNumber}</strong><small>${new Date(sale.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} · ${sale.paymentMethod}</small></span><b>${currency(sale.totalAmount)}</b></div>`).join('')
    : '<p class="dashboard-empty">No completed sales yet.</p>';
}

fetch(dashboardApiUrl, { credentials: 'include' })
  .then((response) => response.ok ? response.json() : Promise.reject(new Error('Dashboard data is unavailable.')))
  .then((payload) => {
    if (!payload.success) throw new Error(payload.message);
    renderDashboard(payload.data);
  })
  .catch((error) => console.warn(error.message));
