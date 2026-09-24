(() => {
  const SESSION_DURATION = 60 * 60 * 1000; // 1 hour

  const LOGIN_PAGE = "signin.html";

  function getUser() {
    const userData = sessionStorage.getItem("user");

    if (!userData) {
      return null;
    }

    try {
      return JSON.parse(userData);
    } catch (error) {
      console.error("Invalid user session:", error);

      sessionStorage.removeItem("user");
      sessionStorage.removeItem("loginTime");

      return null;
    }
  }

  function isSessionValid() {
    const user = getUser();

    if (!user) {
      return false;
    }

    const loginTime = Number(sessionStorage.getItem("loginTime"));

    if (!loginTime) {
      logout(false);

      return false;
    }

    const sessionAge = Date.now() - loginTime;

    if (sessionAge >= SESSION_DURATION) {
      logout(false);

      return false;
    }

    return true;
  }

  function createSession(user) {
    sessionStorage.setItem("user", JSON.stringify(user));

    sessionStorage.setItem("loginTime", Date.now().toString());
  }

  function logout(redirect = true) {
    sessionStorage.removeItem("user");
    sessionStorage.removeItem("loginTime");

    // Remove old localStorage session too
    // in case an older version of Vendly used it.
    localStorage.removeItem("user");
    localStorage.removeItem("loginTime");

    if (redirect) {
      window.location.replace(LOGIN_PAGE);
    }
  }

  function protectPage() {
    if (!isSessionValid()) {
      window.location.replace(LOGIN_PAGE);

      return false;
    }

    return true;
  }

  function startSessionWatcher() {
    setInterval(() => {
      if (!isSessionValid()) {
        window.location.replace(LOGIN_PAGE);
      }
    }, 60 * 1000);
  }

  function setupLogout() {
    const logoutButtons = document.querySelectorAll("[data-logout]");

    logoutButtons.forEach((button) => {
      button.addEventListener("click", (event) => {
        event.preventDefault();

        logout(true);
      });
    });
  }

  window.VendlyAuth = {
    getUser,
    isSessionValid,
    protectPage,
    createSession,
    logout,
  };

  const currentPage = window.location.pathname.split("/").pop().toLowerCase();

  const isLoginPage = currentPage === LOGIN_PAGE;

  if (!isLoginPage) {
    protectPage();

    startSessionWatcher();
  }

  // Logout buttons can exist on protected pages
  setupLogout();
})();
