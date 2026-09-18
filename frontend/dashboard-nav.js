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

animateNavigation();
