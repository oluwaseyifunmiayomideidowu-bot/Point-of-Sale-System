
async function getTodayRevenue() {
  try {
    const response = await fetch(
      "http://localhost/point_of_sale_system/backend/api/reports/daily-sales",
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

    const revenue = Number(data.data.totalRevenue || 0);

    document.getElementById("todayRevenue").textContent =
      `₦${Number(revenue).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })}`;

  } catch (error) {
    console.error("Error getting today's revenue:", error);

    document.getElementById("todayRevenue").textContent = "₦0";
  }
}

function formatDate(dateString) {
  const date = new Date(dateString);

  return date.toLocaleDateString("en-NG", {
    day: "2-digit",
    month: "short",
    year: "numeric"
  });
}

const bestSellingProducts = document.getElementById("bestSellingProducts");

async function getBestSellingProducts() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/reports/best-selling-products",
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

    const products = data.data.products;

    bestSellingProducts.innerHTML = "";

    let num = 0;

    products.forEach(product => {
      const li = document.createElement("li");
      num++

      li.innerHTML = `<span>${num}</span><span class="thumb"><img src="../../point_of_sale_system/backend/${product.productImage}" alt=""></span><div><strong>${product.productName}</strong><small>${product.quantitySold} units . ₦${Number(product.salesAmount).toLocaleString(undefined, {minimumFractionDigits: 2,maximumFractionDigits: 2})}</small></div><b>₦${Number(product.productPrice).toLocaleString(undefined, {minimumFractionDigits: 2,maximumFractionDigits: 2})}<b>`;

      bestSellingProducts.appendChild(li);
    });

  } catch (error) {
    console.error("Error getting best selling products:", error);

    bestSellingProducts.innerHTML = `
      <li>Unable to load best selling products.</li>
    `;
  }
}

const lowStockCount = document.getElementById("lowStockCount");

async function getLowStockCount() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/reports/low-stock",
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

    lowStockCount.textContent =
      Number(data.data.low_stock || 0).toLocaleString();

  } catch (error) {
    console.error("Error getting low stock count:", error);

    lowStockCount.textContent = "0";
  }
}

async function getMonthlySales() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/reports/low-stock",
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

    const monthlySales = data.data;

    console.log(monthlySales);

    monthlySales.forEach(month => {
      console.log(
        `${month.month}: ₦${Number(month.sales).toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        })}`
      );
    });

  } catch (error) {
    console.error("Error getting monthly sales:", error);
  }
}

const monthSales = document.getElementById("monthSales");

async function getMonthSales() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/reports/monthly-sales",
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

    const monthRevenue = Number(data.data.totals.totalRevenue || 0);

    monthSales.textContent = `₦${monthRevenue.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })}`;

  } catch (error) {
    console.error("Error getting month sales:", error);

    monthSales.textContent = "₦0.00";
  }
}

const salesContainer = document.getElementById("salesContainer");

async function getSales() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/sales/getSales",
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

    const sales = data.data;

    salesContainer.innerHTML = "";

    sales.forEach(sale => {
      const saleDiv = document.createElement("div");

      saleDiv.className = "recent-row";
      saleDiv.id = `sale-${sale.id}`;

      saleDiv.innerHTML = `
      <span><strong>${sale.sale_number}</strong><small>6 . ${sale.payment_method}</small></span>
        <div class="sale-product"></div>
        <div class="sale-quantity">Qty: ${sale.quantity}</div>
        <div class="sale-total">
          ₦${Number(sale.total_amount).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          })}
        </div>
        <div class="sale-date">${formatDate(sale.created_at)}</div>
      `;

      salesContainer.appendChild(saleDiv);
    });

  } catch (error) {
    console.log("Error getting sales:", error)
    console.log("hi")
    console.error("Error getting sales:", error);

    salesContainer.innerHTML = `
      <div class="recent-row">Unable to load sales.</div>
    `;
  }
}

getSales();

getMonthSales();

getMonthlySales();

getLowStockCount();

getBestSellingProducts();

getTodayRevenue();

const menuToggle = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('.side');
const sidebarOverlay = document.querySelector('.sidebar-overlay');
const userRoleText = document.getElementById('user-role');
const userNameText = document.querySelector('.user-name');
const nav = document.querySelector('.nav');
const navIndicator = document.querySelector('.nav-indicator');

const user = JSON.parse(localStorage.getItem('user') || 'null');

if (userRoleText && user?.role) {
  userRoleText.textContent = user.role;
}

if (userNameText && user?.first_name) {
  userNameText.textContent = user.first_name;
}

function moveNavIndicator(activeLink) {
  if (!navIndicator || !activeLink || !nav) return;

  const navTop = nav.getBoundingClientRect().top;
  const linkTop = activeLink.getBoundingClientRect().top;
  navIndicator.style.transform = `translateY(${linkTop - navTop + nav.scrollTop}px)`;
}

function animateNavigation() {
  if (!nav) return;

  const navItems = nav.querySelectorAll('h6, a');
  navItems.forEach((item, index) => {
    item.style.animationDelay = `${index * 24}ms`;
  });

  const activeLink = nav.querySelector('a.active');
  moveNavIndicator(activeLink);

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      nav.querySelectorAll('a').forEach((item) => item.classList.remove('active'));
      link.classList.add('active');
      moveNavIndicator(link);
    });
  });
}

function closeSidebar() {
  if (!sidebar || !sidebarOverlay || !menuToggle) return;

  sidebar.classList.remove('is-open');
  sidebarOverlay.classList.remove('is-visible');
  menuToggle.setAttribute('aria-expanded', 'false');
}

function openSidebar() {
  if (!sidebar || !sidebarOverlay || !menuToggle) return;

  sidebar.classList.add('is-open');
  sidebarOverlay.classList.add('is-visible');
  menuToggle.setAttribute('aria-expanded', 'true');
}

if (menuToggle) {
  menuToggle.addEventListener('click', () => {
    if (sidebar?.classList.contains('is-open')) closeSidebar();
    else openSidebar();
  });
}

sidebarOverlay?.addEventListener('click', closeSidebar);

window.addEventListener('resize', () => {
  if (window.innerWidth >= 768) closeSidebar();
  moveNavIndicator(nav?.querySelector('a.active'));
});

<<<<<<< HEAD


=======
animateNavigation();
>>>>>>> origin/my-local-changes
