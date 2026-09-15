const demoRole = document.body.dataset.role || 'admin';

function formatMoney(value) {
  return `₦${Number(value).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function showToast(message) {
  const toast = document.querySelector('.toast');
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add('show');
  window.setTimeout(() => toast.classList.remove('show'), 2600);
}

function setFieldError(field, message) {
  const error = field.closest('.form-field')?.querySelector('.field-error');
  if (error) error.textContent = message;
  field.setAttribute('aria-invalid', message ? 'true' : 'false');
  return !message;
}

function validateField(field) {
  const value = field.value.trim();
  if (field.required && !value) return setFieldError(field, 'This field is required.');
  if (field.name === 'username' && value && !/^[a-zA-Z0-9_]{3,30}$/.test(value)) return setFieldError(field, 'Use 3–30 letters, numbers, or underscores.');
  if (field.type === 'email' && value && !field.validity.valid) return setFieldError(field, 'Enter a valid email address.');
  if (field.name === 'phone' && value && !/^(?:\d{11}|\+234\d{10})$/.test(value.replace(/\s/g, ''))) return setFieldError(field, 'Use 11 digits or +234 followed by 10 digits.');
  if (field.name === 'password' && value && (!/(?=.*[A-Za-z])(?=.*\d).{8,}/.test(value))) return setFieldError(field, 'Use at least 8 characters, including a letter and a number.');
  if (field.name === 'confirmPassword' && value !== field.form.querySelector('[name=password]')?.value) return setFieldError(field, 'Passwords must match.');
  if (field.type === 'number' && value && Number(value) < 0) return setFieldError(field, 'Use zero or a positive number.');
  return setFieldError(field, '');
}

function closeModal(modal) {
  modal.classList.remove('is-open');
}

document.querySelectorAll('[data-role-access]').forEach((element) => {
  const roles = element.dataset.roleAccess.split(',');
  if (!roles.includes(demoRole)) element.remove();
});

document.querySelectorAll('[data-open-modal]').forEach((button) => {
  button.addEventListener('click', () => document.querySelector(button.dataset.openModal)?.classList.add('is-open'));
});

document.querySelectorAll('[data-close-modal]').forEach((button) => {
  button.addEventListener('click', () => closeModal(button.closest('.modal-overlay')));
});

document.querySelectorAll('.modal-overlay').forEach((modal) => {
  modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(modal); });
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') document.querySelectorAll('.modal-overlay.is-open').forEach(closeModal);
});

document.querySelectorAll('.form-modal input, .form-modal select, .form-modal textarea').forEach((field) => {
  field.addEventListener('blur', () => validateField(field));
});

document.querySelectorAll('[data-add-form]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const fields = [...form.querySelectorAll('input, select, textarea')];
    const valid = fields.map(validateField).every(Boolean);
    if (!valid) {
      fields.find((field) => field.getAttribute('aria-invalid') === 'true')?.focus();
      return;
    }
    const button = form.querySelector('[type=submit]');
    button.disabled = true;
    button.classList.add('is-saving');
    window.setTimeout(() => {
      const modal = form.closest('.modal-overlay');
      const table = document.querySelector(form.dataset.tableTarget);
      const title = form.dataset.entity || 'Record';
      if (table) {
        const values = new FormData(form);
        const row = document.createElement('tr');
        row.className = 'new-row';
        row.innerHTML = values.get('name') ? `<td><strong>${values.get('name')}</strong></td><td>${values.get('category') || '—'}</td><td>${values.get('supplier') || '—'}</td><td class="money">${formatMoney(values.get('cost') || 0)}</td><td class="money">${formatMoney(values.get('price') || 0)}</td><td>${values.get('quantity') || '—'}</td><td>${values.get('reorder') || '—'}</td><td><span class="tag good">${values.get('status') || 'Active'}</span></td><td>•••</td>` : '';
        if (row.innerHTML) table.prepend(row);
      }
      form.reset();
      button.disabled = false;
      button.classList.remove('is-saving');
      closeModal(modal);
      showToast(`${title} saved.`);
    }, 500);
  });
});

document.querySelectorAll('[data-table-add-form]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const fields = [...form.querySelectorAll('input, select, textarea')];
    const valid = fields.map(validateField).every(Boolean);
    if (!valid) {
      fields.find((field) => field.getAttribute('aria-invalid') === 'true')?.focus();
      return;
    }
    const button = form.querySelector('[type=submit]');
    button.disabled = true;
    button.classList.add('is-saving');
    window.setTimeout(() => {
      const data = new FormData(form);
      const table = document.querySelector(form.dataset.tableTarget);
      const record = document.createElement('tr');
      record.className = 'new-row';
      if (form.dataset.recordKind === 'supplier') {
        record.innerHTML = `<td><strong>${data.get('firstName')} ${data.get('lastName')}</strong></td><td>${data.get('username')}</td><td>${data.get('email')}</td><td>${data.get('phone')}</td><td>0</td><td><span class="tag good">${data.get('status')}</span></td><td>•••</td>`;
      } else {
        const total = Number(data.get('quantity')) * Number(data.get('cost'));
        const orderNumber = `PO-${String(table.rows.length + 46).padStart(4, '0')}`;
        const date = data.get('deliveryDate')
          ? new Date(`${data.get('deliveryDate')}T00:00:00`).toLocaleDateString('en-NG', { day: '2-digit', month: 'short', year: 'numeric' })
          : 'Not scheduled';
        record.innerHTML = `<td><strong>${orderNumber}</strong></td><td>${data.get('supplier')}</td><td>${data.get('quantity')} × ${data.get('product')}</td><td class="money">${formatMoney(total)}</td><td>${date}</td><td><span class="tag low">Pending</span></td><td>•••</td>`;
      }
      table.prepend(record);
      form.reset();
      button.disabled = false;
      button.classList.remove('is-saving');
      closeModal(form.closest('.modal-overlay'));
      showToast(`${form.dataset.entity} saved.`);
    }, 500);
  });
});
