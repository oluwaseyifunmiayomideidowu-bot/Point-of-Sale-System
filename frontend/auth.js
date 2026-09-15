const loginForm = document.getElementById('loginForm');
const loginError = document.getElementById('loginError');
const passwordToggle = document.querySelector('.password-toggle');
const loginPassword = document.getElementById('loginPassword');

if (passwordToggle && loginPassword) {
  passwordToggle.addEventListener('click', () => {
    const showingPassword = loginPassword.type === 'text';
    loginPassword.type = showingPassword ? 'password' : 'text';
    passwordToggle.textContent = showingPassword ? 'Show' : 'Hide';
    passwordToggle.setAttribute('aria-label', showingPassword ? 'Show password' : 'Hide password');
  });
}

if (loginForm) {
  loginForm.addEventListener('submit', (event) => {
    event.preventDefault();

    const formData = new FormData(loginForm);
    const accounts = {
      'adele.admin': {
        password: 'vendlyadmin',
        destination: 'dashboard.html',
      },
      'musa.manager': {
        password: 'vendlymanager',
        destination: 'manager-dashboard.html',
      },
      'chi.cashier': {
        password: 'vendlycashier',
        destination: 'cashier-dashboard.html',
      },
    };

    const account = accounts[formData.get('username').trim().toLowerCase()];

    if (!account || account.password !== formData.get('password')) {
      loginError.textContent = 'The username or password is incorrect. Check your details and try again.';
      loginError.classList.add('is-visible');
      return;
    }

    loginError.textContent = '';
    loginError.classList.remove('is-visible');
    window.location.href = account.destination;
  });
}
