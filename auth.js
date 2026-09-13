const loginForm = document.getElementById('loginForm');

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
      window.alert('The username or password is incorrect. Please try again.');
      return;
    }

    window.location.href = account.destination;
  });
}
