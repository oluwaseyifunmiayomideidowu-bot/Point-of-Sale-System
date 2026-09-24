const menuToggle = document.querySelector(".menu-toggle");
const sidebar = document.querySelector(".side");
const sidebarOverlay = document.querySelector(".sidebar-overlay");

const nav = document.querySelector(".side .nav");
const navIndicator = nav?.querySelector(".nav-indicator");


const user = JSON.parse(
  localStorage.getItem("user") || "null"
);

const role = String(
  user?.role || ""
).toLowerCase().trim();


const dashboardPages = {
  admin: "admin-dashboard.html",
  manager: "manager-dashboard.html",
  cashier: "cashier-dashboard.html"
};

const dashboardPage =
  dashboardPages[role] || "dashboard.html";


const userNameTexts =
  document.querySelectorAll(".user-name");

const userInitials =
  document.querySelectorAll(".user-initial");

const userRoleTexts =
  document.querySelectorAll(".user-role");


// First name
if (user?.first_name) {

  userNameTexts.forEach((element) => {
    element.textContent = user.first_name;
  });

}


// Initials
if (user?.first_name && user?.last_name) {

  const initials =
    user.first_name.charAt(0).toUpperCase() +
    user.last_name.charAt(0).toUpperCase();

  userInitials.forEach((element) => {
    element.textContent = initials;
  });

}


// Role
if (user?.role) {

  userRoleTexts.forEach((element) => {
    element.textContent = user.role;
  });

}


// Dashboard sidebar link
document.querySelectorAll(".dashboard-link").forEach((link) => {
  link.href = dashboardPage;
});


// Vendly logo
const brandLink = document.getElementById("brand-link");

if (brandLink) {
  brandLink.href = dashboardPage;
}


document.querySelectorAll("[data-roles]").forEach((element) => {

  const allowedRoles =
    element.dataset.roles
      .split(",")
      .map((item) => item.trim().toLowerCase());

  if (!allowedRoles.includes(role)) {

    element.style.display = "none";

  } else {

    element.style.display = "";

  }

});


function moveNavIndicator(activeLink) {

  if (!navIndicator || !activeLink || !nav) {
    return;
  }

  const navTop =
    nav.getBoundingClientRect().top;

  const linkTop =
    activeLink.getBoundingClientRect().top;

  navIndicator.style.transform =
    `translateY(${linkTop - navTop + nav.scrollTop}px)`;
}


function getCurrentPage() {

  const path =
    window.location.pathname
      .split("/")
      .pop();

  return path || "dashboard.html";
}


function setActiveNavigation() {

  if (!nav) return;

  const currentPage =
    getCurrentPage();

  const currentHash =
    window.location.hash;


  const links =
    nav.querySelectorAll("a");


  let activeLink = null;


  links.forEach((link) => {

    const url =
      new URL(link.href, window.location.href);

    const linkPage =
      url.pathname
        .split("/")
        .pop();

    const linkHash =
      url.hash;


    const pageMatches =
      linkPage === currentPage;

    const hashMatches =
      linkHash === currentHash;


    if (
      pageMatches &&
      (linkHash ? hashMatches : true)
    ) {

      activeLink = link;

    }

  });


  // Remove active from every link
  links.forEach((link) => {
    link.classList.remove("active");
  });


  // Add active to current link
  if (activeLink) {

    activeLink.classList.add("active");

    moveNavIndicator(activeLink);

  }

}


function animateNavigation() {

  if (!nav) return;

  const navItems =
    nav.querySelectorAll("h6, a");

  navItems.forEach((item, index) => {

    item.style.animationDelay =
      `${index * 24}ms`;

  });


  setActiveNavigation();


  nav.querySelectorAll("a").forEach((link) => {

    link.addEventListener("click", () => {

      nav.querySelectorAll("a").forEach((item) => {
        item.classList.remove("active");
      });

      link.classList.add("active");

      moveNavIndicator(link);

    });

  });

}


function openSidebar() {

  if (
    !sidebar ||
    !sidebarOverlay ||
    !menuToggle
  ) {
    return;
  }

  sidebar.classList.add("is-open");

  sidebarOverlay.classList.add("is-visible");

  menuToggle.setAttribute(
    "aria-expanded",
    "true"
  );

}


function closeSidebar() {

  if (
    !sidebar ||
    !sidebarOverlay ||
    !menuToggle
  ) {
    return;
  }

  sidebar.classList.remove("is-open");

  sidebarOverlay.classList.remove("is-visible");

  menuToggle.setAttribute(
    "aria-expanded",
    "false"
  );

}


if (menuToggle) {

  menuToggle.addEventListener("click", () => {

    if (
      sidebar?.classList.contains("is-open")
    ) {

      closeSidebar();

    } else {

      openSidebar();

    }

  });

}


sidebarOverlay?.addEventListener(
  "click",
  closeSidebar
);


window.addEventListener("resize", () => {

  if (window.innerWidth >= 768) {
    closeSidebar();
  }

  const activeLink =
    nav?.querySelector("a.active");

  moveNavIndicator(activeLink);

});


animateNavigation();