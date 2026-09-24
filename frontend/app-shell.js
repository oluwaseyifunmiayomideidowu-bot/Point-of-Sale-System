let lowStockCount = 0;

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

    lowStockCount = Number(data.data?.low_stock || 0);

    renderSharedNavigation();

  } catch (error) {
    console.error("Error getting low stock count:", error);
  }
}

getLowStockCount();

const sharedNavigation = [
  ['MAIN', [['dashboard.html', 'D', 'Dashboard']]],
  ['SALES', [['pos.html', 'P', 'Point of sale']]],
  ['INVENTORY', [['products.html', 'P', 'Products', null], ['inventory.html', 'I', 'Inventory', null, 'warn']]],
  ['PURCHASES', [['purchases.html', 'P', 'Purchases'], ['suppliers.html', 'S', 'Suppliers']]],
  ['REPORTS', [['reports.html', 'S', 'Sales reports'], ['reports.html#inventory', 'I', 'Inventory reports'], ['reports.html#expenses', 'E', 'Expenses']]],
];

function activeNavigationPath() {
  const page = window.location.pathname.split('/').pop() || 'dashboard.html';
  return `${page}${window.location.hash}`;
}

function renderSharedNavigation() {
  const nav = document.querySelector('.side .nav');
  if (!nav) return;

  const user = JSON.parse(localStorage.getItem('user') || 'null');

  const role =
    user?.role ||
    document.body.dataset.role ||
    'Administrator';

  const currentPath = activeNavigationPath();

  const groups = sharedNavigation;

  nav.setAttribute('aria-label', 'Main navigation');

  nav.innerHTML = `
    <span class="nav-indicator" aria-hidden="true"></span>

    ${groups.map(([group, links]) => `
      <h6>${group}</h6>

      ${links.map(([href, icon, label, badge, badgeClass = '']) => {

        // Use the real low-stock count for Inventory
        let actualBadge = badge;

        if (href === 'inventory.html') {
          actualBadge = lowStockCount;
        }

        return `
          <a
            href="${href}"
            class="${href === currentPath ? 'active' : ''}"
          >
            <span class="ico">${icon}</span>
            ${label}

            ${
              actualBadge > 0
                ? `<em class="${badgeClass}">${actualBadge}</em>`
                : ''
            }
          </a>
        `;
      }).join('')}
    `).join('')}
  `;

  const userRole = document.getElementById('user-role');
  const userName = document.querySelector('.user-name');

  if (userRole && user?.role) {
    userRole.textContent = user.role;
  }

  if (userName && user?.first_name) {
    userName.textContent = user.first_name;
  }

  const indicator = nav.querySelector('.nav-indicator');
  const activeLink = nav.querySelector('.active');

  if (indicator && activeLink) {
    indicator.style.transform =
      `translateY(${activeLink.offsetTop}px)`;
  }

  nav.querySelectorAll('h6, a').forEach((item, index) => {
    item.style.animationDelay = `${index * 24}ms`;
  });
}

renderSharedNavigation();
