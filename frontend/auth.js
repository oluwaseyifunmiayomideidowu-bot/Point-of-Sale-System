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

// if (loginForm) {
//   loginForm.addEventListener('submit', (event) => {
//     event.preventDefault();

//     const formData = new FormData(loginForm);
//     const accounts = {
//       'adele.admin': {
//         password: 'vendlyadmin',
//         destination: 'dashboard.html',
//       },
//       'musa.manager': {
//         password: 'vendlymanager',
//         destination: 'manager-dashboard.html',
//       },
//       'chi.cashier': {
//         password: 'vendlycashier',
//         destination: 'cashier-dashboard.html',
//       },
//     };

//     const account = accounts[formData.get('username').trim().toLowerCase()];

//     if (!account || account.password !== formData.get('password')) {
//       loginError.textContent = 'The username or password is incorrect. Check your details and try again.';
//       loginError.classList.add('is-visible');
//       return;
//     }

//     loginError.textContent = '';
//     loginError.classList.remove('is-visible');
//     window.location.href = account.destination;
//   });
// }

if (loginForm) {
  loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = new FormData(loginForm);

    const login = formData.get('username').trim();
    const password = formData.get('password');

    try {
      const response = await fetch(
        'http://localhost/point_of_sale_system/backend/api/login',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            login,
            password
          })
        }
      );

      const data = await response.json();

      console.log("Status:", response.status);
      console.log("Response:", data);

      if (!response.ok) {
        loginError.textContent = data.message || 'Login failed.';
        loginError.classList.add('is-visible');
        return;
      }

      const { id, first_name, last_name, username, role } = data.data.user;

      localStorage.setItem(
        "user",
        JSON.stringify({
          id,
          first_name,
          last_name,
          username,
          role
        })
      );

      console.log(role)

      if (role === 'Administrator'){
        window.location.href = "dashboard.html";
      }else if (role === 'Manager') {
        window.location.href = "manager-dashboard.html";
      }else if (role === 'Cashier') {
        window.location.href = "cashier-dashboard.html";
      }


    } catch (error) {
      console.error(error);

      loginError.textContent =
        'Unable to connect to the server. Please try again.';

      loginError.classList.add('is-visible');
    }
  });
}
