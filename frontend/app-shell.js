const sharedNavigation = [
  ['MAIN', [['dashboard.html', 'D', 'Dashboard']]],
  ['SALES', [['pos.html', 'P', 'Point of sale'], ['returns.html', 'R', 'Sales returns']]],
  ['INVENTORY', [['products.html', 'P', 'Products', '24'], ['inventory.html', 'I', 'Inventory', '3', 'warn'], ['inventory.html#adjustments', 'A', 'Stock adjustment'], ['inventory.html#low-stock', 'L', 'Low stock']]],
  ['PURCHASES', [['purchases.html', 'O', 'Purchases'], ['suppliers.html', 'S', 'Suppliers']]],
  ['REPORTS', [['reports.html', 'S', 'Sales reports'], ['reports.html#inventory', 'I', 'Inventory reports'], ['reports.html#expenses', 'E', 'Expenses']]],
  ['SETTINGS', [['settings.html', 'B', 'Business settings'], ['settings.html#users', 'U', 'Users & roles']]],
];

function activeNavigationPath() {
  const page = window.location.pathname.split('/').pop() || 'dashboard.html';
  return `${page}${window.location.hash}`;
}

function renderSharedNavigation() {
  const nav = document.querySelector('.side .nav');
  if (!nav) return;

  const user = JSON.parse(localStorage.getItem('user') || 'null');
  const role = user?.role || document.body.dataset.role || 'Administrator';
  const currentPath = activeNavigationPath();
  const groups = role === 'Manager'
    ? sharedNavigation.filter(([group]) => group !== 'SETTINGS')
    : sharedNavigation;

  nav.setAttribute('aria-label', 'Main navigation');
  nav.innerHTML = `<span class="nav-indicator" aria-hidden="true"></span>${groups.map(([group, links]) => `<h6>${group}</h6>${links.map(([href, icon, label, badge, badgeClass = '']) => `<a href="${href}" class="${href === currentPath || (!window.location.hash && href === currentPath) ? 'active' : ''}"><span class="ico">${icon}</span>${label}${badge ? `<em class="${badgeClass}">${badge}</em>` : ''}</a>`).join('')}`).join('')}`;

  const userRole = document.getElementById('user-role');
  const userName = document.querySelector('.user-name');
  if (userRole && user?.role) userRole.textContent = user.role;
  if (userName && user?.first_name) userName.textContent = user.first_name;

  const indicator = nav.querySelector('.nav-indicator');
  const activeLink = nav.querySelector('.active');
  if (indicator && activeLink) {
    indicator.style.transform = `translateY(${activeLink.offsetTop}px)`;
  }

  nav.querySelectorAll('h6, a').forEach((item, index) => {
    item.style.animationDelay = `${index * 24}ms`;
  });
}

renderSharedNavigation();
