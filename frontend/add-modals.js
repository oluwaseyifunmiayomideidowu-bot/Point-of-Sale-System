const productTableBody = document.getElementById("products-table-body");

async function getProducts() {
  try {
    const response = await fetch("http://localhost/point_of_sale_system/backend/api/products", {
      method: "GET",
      headers: {
        "Accept": "application/json"
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const result = await response.json();

    const products = result.data;

    productTableBody.innerHTML = "";

    products.forEach(product => {
      const row = document.createElement("tr");

      row.id = `product-${product.id}`
      let stockClass;
      let stockText;

      if (product.quantity === 0) {
        stockClass = "out";
        stockText = "Out Of Stock"
      } else if (product.quantity <= product.reorder_level) {
        stockClass = "low";
        stockText = "Almost Out"
      } else {
        stockClass = "good";
        stockText = "In Stock"
      }

      row.innerHTML = `
        <td class="item">
          <span class="thumb">
            <img src="../..//point_of_sale_system/backend/${product.image}" alt="">
          </span>
          ${product.name}
        </td>
        <td>${product.category_name}</td>
        <td>${product.sku}</td>
        <td>₦${Number(product.cost_price).toLocaleString()}</td>
        <td>₦${Number(product.selling_price).toLocaleString()}</td>
        <td>${product.quantity}</td>
        <td>${product.status}</td>
        <td>
          <div class="tag ${stockClass}">
            ${stockText}
          </div>
        </td>
        <td>${formatDate(product.created_at)}</td>
      `;

      productTableBody.appendChild(row);
    });

  } catch (error) {
    console.error("Error getting products:", error);

    productTableBody.innerHTML = `
      <tr>
        <td colspan="5">Unable to load products.</td>
      </tr>
    `;
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

const purchaseTableBody = document.getElementById("purchaseTableBody");

async function getPurchases() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/purchases",
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

    const purchases = data.data;

    purchaseTableBody.innerHTML = "";

    purchases.forEach(purchase => {

      const row = document.createElement("tr");

      row.id = `purchase-${purchase.id}`;

      row.innerHTML = `
        <td>${purchase.purchaseNumber}</td>
        <td>${purchase.supplierName}</td>
        <td>${purchase.createdBy}</td>
        <td>₦${Number(purchase.totalAmount).toLocaleString()}</td>
        <td>${formatDate(purchase.createdAt)}</td>
        <td>${purchase.status}</td>
        `;
        // <td><button>Compelte</button> <button>Cancelled</button></td>

      purchaseTableBody.appendChild(row);
    });

  } catch (error) {

    console.error("Error getting purchases:", error);

    purchaseTableBody.innerHTML = `
      <tr>
        <td colspan="7">Unable to load purchases.</td>
      </tr>
    `;
  }
}


getProducts();

getPurchases();


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
    if (form.id === "productForm") return;
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

  if (form.id === "productForm") return;

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

const categorySelect = document.getElementById("category");

async function getCategories() {
  try {
    const response = await fetch("/point_of_sale_system/backend/api/categories", {
      method: "GET",
      headers: {
        "Accept": "application/json"
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const data = await response.json();

    const categories = data.data;

    categories.forEach(category => {
      const option = document.createElement("option");

      option.id = category.id
      option.value = category.name;
      option.textContent = category.name;

      categorySelect.appendChild(option);
    });

  } catch (error) {
    console.error("Error getting categories:", error);
  }
}

getCategories();

const supplierSelect = document.getElementById("supplier");

async function getSuppliers() {
  try {
    const response = await fetch("/point_of_sale_system/backend/api/suppliers", {
      method: "GET",
      headers: {
        "Accept": "application/json"
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const data = await response.json();

    const suppliers = data.data;

    suppliers.forEach(supplier => {
      const option = document.createElement("option");

      option.value = supplier.username;
      option.textContent = supplier.username;

      supplierSelect.appendChild(option);
    });

  } catch (error) {
    console.error("Error getting suppliers:", error);
  }
}

getSuppliers();

const suppliersTableBody =
  document.getElementById("suppliersTableBody");

  

async function getSuppliersTable() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/suppliers",
      {
        method: "GET",
        headers: {
          "Accept": "application/json"
        }
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const data = await response.json();

    const suppliers = data.data || [];

    suppliersTableBody.innerHTML = "";

    suppliers.forEach(supplier => {

      const row = document.createElement("tr");

      row.id = `supplier-${supplier.id}`;

      if (supplier.status === "Active") {
        statusClass = "good";
      } else {
        statusClass = "out";
      }

      row.innerHTML = `
        <td>${supplier.first_name} ${supplier.last_name}</td>
        <td>${supplier.username}</td>
        <td>${supplier.email || "—"}</td>
        <td>${supplier.phone || "—"}</td>
        <td class="tag ${statusClass}">${supplier.status || "—"}</td>
      `;

      suppliersTableBody.appendChild(row);
    });

  } catch (error) {
    console.error("Error getting suppliers:", error);

    suppliersTableBody.innerHTML = `
      <tr>
        <td colspan="3">Unable to load suppliers.</td>
      </tr>
    `;
  }
}

const productSelect = document.getElementById("product");

async function getProductsSelect() {
  try {
    const response = await fetch(
      "http://localhost/point_of_sale_system/backend/api/products",
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

    const products = data.data || [];

    productSelect.innerHTML = `
      <option value="">Select product</option>
    `;

    products.forEach(product => {
      const option = document.createElement("option");

      option.value = product.id;
      option.textContent = product.name;

      productSelect.appendChild(option);
    });

  } catch (error) {
    console.error("Error getting products:", error);
  }
}

getProductsSelect();

getSuppliersTable();

async function updatePurchase(purchaseId, purchaseStatus) {
  const purchaseData = {
    id: purchaseId,
    status: purchaseStatus
  };

  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/updatePurchase",
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        credentials: "include",
        body: JSON.stringify(purchaseData)
      }
    );

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Failed to update purchase.");
    }

    console.log("Purchase updated:", data);

    // Get the updated purchases
    await getPurchases();

  } catch (error) {
    console.error("Error updating purchase:", error);
  }
}

const productForm = document.getElementById("productForm");

productForm.addEventListener("submit", async (event) => {

    event.preventDefault();
    const formData = new FormData();

    formData.append(
        "categoryName",
        document.getElementById("category").value
    );

    formData.append(
        "supplierName",
        document.getElementById("supplier").value
    );

    formData.append(
        "productName",
        document.getElementById("name").value
    );

    formData.append(
        "costPrice",
        document.getElementById("costPrice").value
    );

    formData.append(
        "sellingPrice",
        document.getElementById("sellingPrice").value
    );

    formData.append(
        "quantity",
        document.getElementById("quantity").value
    );

    formData.append(
        "reorderLevel",
        document.getElementById("reorderLevel").value
    );

    formData.append(
        "status",
        document.getElementById("status").value
    );


    const imageFile = document.getElementById("image").files[0];

    if (imageFile) {
        formData.append("image", imageFile);
    }


    // responseOutput.textContent = "Sending request...";

    try {
        const response = await fetch(
            "/point_of_sale_system/backend/api/createProduct",
            {
                method: "POST",

                body: formData,

                credentials: "include"
            }
        );

        const text = await response.text();

        try {

            const data = JSON.parse(text);
            getProducts();

            console.log(
                JSON.stringify(data, null, 2));

        } catch {

            console.log(text);

        }

    } catch (error) {

        console.log(
          "Request failed:\n\n" + error.message);

    }
});

