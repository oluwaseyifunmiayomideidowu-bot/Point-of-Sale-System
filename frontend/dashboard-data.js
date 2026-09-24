const dashboardApiUrl = 'http://localhost/point_of_sale_system/backend/api/reports/dashboard-summary';

const currency = (value) => `₦${Number(value || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

function categoryHealthLabel(category) {
  if (category.reorderQuantity === 0) return 'No reorder target';
  return `${category.currentQuantity} units / reorder target ${category.reorderQuantity}`;
}

function initials(value) {
  return value.split(/\s+/).slice(0, 2).map((word) => word[0]).join('').toUpperCase();
}

let salesChart;

async function getWeeklySales() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/reports/weekly-sales",
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

    const result = await response.json();

    if (!result.success) {
      throw new Error(
        result.message || "Unable to get weekly sales."
      );
    }

    renderSalesChart(result.data.sales);

  } catch (error) {
    console.error("Error getting weekly sales:", error);
  }
}

function renderSalesChart(sales) {
  const canvas = document.getElementById("salesChart");

  if (!canvas) return;

  const labels = sales.map((item) => item.day);

  const values = sales.map((item) =>
    Number(item.totalSales) || 0
  );

  if (salesChart) {
    salesChart.destroy();
  }

  salesChart = new Chart(canvas, {
    type: "line",

    data: {
      labels: labels,

      datasets: [
        {
          label: "Sales",
          data: values,

          borderColor: "#b5651d",
          backgroundColor: "rgba(181, 101, 29, 0.12)",

          borderWidth: 3,
          fill: true,

          tension: 0.4,

          pointRadius: 4,
          pointHoverRadius: 7,

          pointBackgroundColor: "#b5651d",
          pointBorderWidth: 0
        }
      ]
    },

    options: {
      responsive: true,
      maintainAspectRatio: false,

      /*
       * This is the important part.
       * The points start from the bottom and
       * animate into their actual positions.
       */
      animation: {
        duration: 1800,
        easing: "easeOutQuart",

        onComplete: function () {
          console.log("Sales chart animation complete");
        }
      },

      animations: {
        y: {
          from: function (ctx) {
            return ctx.chart.scales.y.getPixelForValue(0);
          },
          duration: 1800,
          easing: "easeOutQuart"
        },

        x: {
          duration: 1400,
          easing: "easeOutQuart"
        }
      },

      plugins: {
        legend: {
          display: false
        },

        tooltip: {
          callbacks: {
            label: function (context) {
              return `Sales: ₦${Number(
                context.raw
              ).toLocaleString("en-NG", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              })}`;
            }
          }
        }
      },

      scales: {
        x: {
          grid: {
            display: false
          },

          ticks: {
            color: "#777"
          }
        },

        y: {
          beginAtZero: true,

          grid: {
            color: "rgba(0, 0, 0, 0.06)"
          },

          ticks: {
            color: "#777",

            callback: function (value) {
              return `₦${Number(value).toLocaleString("en-NG")}`;
            }
          }
        }
      }
    }
  });
}

getWeeklySales();

function renderDashboard(data) {
  document.getElementById('today-sales-value').textContent = currency(data.today.totalRevenue);
  document.getElementById('chart-number-revenue').textContent = currency(data.today.totalRevenue);
  document.getElementById('purchases-mtd-value').textContent = `${data.today.totalSales} sales today`;
  document.getElementById('active-products-value').textContent = data.inventory.totalProducts;
  document.getElementById('low-stock-value').textContent = data.inventory.lowStockProducts;

  const categoryProgress = document.getElementById('category-progress');
  categoryProgress.innerHTML = data.categories.length
    ? data.categories.map((category) => `<div><span><b>${category.name}</b><small>${category.healthPercent}% · ${categoryHealthLabel(category)}</small></span><i><em style="width:${category.healthPercent}%"></em></i></div>`).join('')
    : '<p class="dashboard-empty">No active inventory categories yet.</p>';

  const alertList = document.getElementById('stock-alert-list');
  alertList.innerHTML = data.stockAlerts.length
    ? data.stockAlerts.map((item) => `<div><b class="alert-ico">${initials(item.name)}</b><span><strong>${item.name}</strong><small>${item.quantity} left · Reorder at ${item.reorderLevel}</small></span><a class="mini-action" href="inventory.html">Restock</a></div>`).join('')
    : '<p class="dashboard-empty">No low-stock products right now.</p>';

  const recentSales = document.getElementById('recent-sales-list');
  recentSales.innerHTML = data.recentSales.length
    ? data.recentSales.map((sale) => `<div class="recent-row"><span><strong>${sale.saleNumber}</strong><small>${new Date(sale.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} · ${sale.paymentMethod}</small></span><span><b>${currency(sale.totalAmount)}</b><small>${sale.firstName}.${sale.lastName}</small></span></div>`).join('')
    : '<p class="dashboard-empty">No completed sales yet.</p>';

  const recentManagerSales = document.getElementById('recent-sales-lists');
  recentManagerSales.innerHTML = data.recentSales.length
    ? data.recentSales.map((sale) => `<div class="recent-row"><span><strong>${sale.saleNumber}1</strong><small>${new Date(sale.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} · ${sale.paymentMethod}</small></span><b>${currency(sale.totalAmount)}</b></div>`).join('')
    : '<p class="dashboard-empty">No completed sales yet.</p>';

}

fetch(dashboardApiUrl, { credentials: 'include' })
  .then((response) => response.ok ? response.json() : Promise.reject(new Error('Dashboard data is unavailable.')))
  .then((payload) => {
    if (!payload.success) throw new Error(payload.message);
    renderDashboard(payload.data);
  })
  .catch((error) => console.warn(error.message));
