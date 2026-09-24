const purchaseTableBody = document.getElementById("purchaseTableBody");

async function getPurchases() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/purchases",
      {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
        credentials: "include",
      },
    );

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const data = await response.json();

    const purchases = data.data || [];

    // Sort purchases by product stock status
    purchases.sort((a, b) => {
      function getStockPriority(purchase) {
        const quantity = Number(purchase.quantity) || 0;
        const reorderLevel = Number(purchase.reorderLevel) || 0;

        const status = String(purchase.status).toLowerCase();

        // Inactive = last
        if (status === "inactive") {
          return 4;
        }

        // Out of stock
        if (quantity === 0) {
          return 3;
        }

        // Almost out
        if (quantity <= reorderLevel + 10) {
          return 2;
        }

        // In stock
        return 1;
      }

      return getStockPriority(a) - getStockPriority(b);
    });

    purchaseTableBody.innerHTML = "";

    purchases.forEach((purchase) => {
      const row = document.createElement("tr");

      row.id = `purchase-${purchase.id}`;

      row.innerHTML = `
        <td>${purchase.purchaseNumber}</td>
        <td>${purchase.supplierName}</td>
        <td>${purchase.createdBy}</td>
        <td>
          ₦${Number(purchase.totalAmount).toLocaleString()}
        </td>
        <td>${formatDate(purchase.createdAt)}</td>
        <td>${purchase.status}</td>
      `;

      purchaseTableBody.appendChild(row);
    });
  } catch (error) {
    console.error("Error getting purchases:", error);

    purchaseTableBody.innerHTML = `
      <tr>
        <td colspan="7">
          Unable to load purchases.
        </td>
      </tr>
    `;
  }
}

getPurchases();

const demoRole = document.body.dataset.role || "admin";

function formatMoney(value) {
  return `₦${Number(value).toLocaleString("en-NG", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function showToast(message) {
  const toast = document.querySelector(".toast");
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add("show");
  window.setTimeout(() => toast.classList.remove("show"), 2600);
}

function setFieldError(field, message) {
  const error = field.closest(".form-field")?.querySelector(".field-error");
  if (error) error.textContent = message;
  field.setAttribute("aria-invalid", message ? "true" : "false");
  return !message;
}

function validateField(field) {
  const value = field.value.trim();
  if (field.required && !value)
    return setFieldError(field, "This field is required.");
  if (field.name === "username" && value && !/^[a-zA-Z0-9_]{3,30}$/.test(value))
    return setFieldError(field, "Use 3–30 letters, numbers, or underscores.");
  if (field.type === "email" && value && !field.validity.valid)
    return setFieldError(field, "Enter a valid email address.");
  if (
    field.name === "phone" &&
    value &&
    !/^(?:\d{11}|\+234\d{10})$/.test(value.replace(/\s/g, ""))
  )
    return setFieldError(field, "Use 11 digits or +234 followed by 10 digits.");
  if (
    field.name === "password" &&
    value &&
    !/(?=.*[A-Za-z])(?=.*\d).{8,}/.test(value)
  )
    return setFieldError(
      field,
      "Use at least 8 characters, including a letter and a number.",
    );
  if (
    field.name === "confirmPassword" &&
    value !== field.form.querySelector("[name=password]")?.value
  )
    return setFieldError(field, "Passwords must match.");
  if (field.type === "number" && value && Number(value) < 0)
    return setFieldError(field, "Use zero or a positive number.");
  return setFieldError(field, "");
}

function closeModal(modal) {
  modal.classList.remove("is-open");
}

document.querySelectorAll("[data-role-access]").forEach((element) => {
  const roles = element.dataset.roleAccess.split(",");
  if (!roles.includes(demoRole)) element.remove();
});

document.querySelectorAll("[data-open-modal]").forEach((button) => {
  button.addEventListener("click", () =>
    document.querySelector(button.dataset.openModal)?.classList.add("is-open"),
  );
});

document.querySelectorAll("[data-close-modal]").forEach((button) => {
  button.addEventListener("click", () =>
    closeModal(button.closest(".modal-overlay")),
  );
});

document.querySelectorAll(".modal-overlay").forEach((modal) => {
  modal.addEventListener("click", (event) => {
    if (event.target === modal) closeModal(modal);
  });
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape")
    document.querySelectorAll(".modal-overlay.is-open").forEach(closeModal);
});

document
  .querySelectorAll(
    ".form-modal input, .form-modal select, .form-modal textarea",
  )
  .forEach((field) => {
    field.addEventListener("blur", () => validateField(field));
  });

document.querySelectorAll("[data-add-form]").forEach((form) => {
  if (form.id === "productForm") return;
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    const fields = [...form.querySelectorAll("input, select, textarea")];
    const valid = fields.map(validateField).every(Boolean);
    if (!valid) {
      fields
        .find((field) => field.getAttribute("aria-invalid") === "true")
        ?.focus();
      return;
    }
    const button = form.querySelector("[type=submit]");
    button.disabled = true;
    button.classList.add("is-saving");
    window.setTimeout(() => {
      const modal = form.closest(".modal-overlay");
      const table = document.querySelector(form.dataset.tableTarget);
      const title = form.dataset.entity || "Record";
      if (table) {
        const values = new FormData(form);
        const row = document.createElement("tr");
        row.className = "new-row";
        row.innerHTML = values.get("name")
          ? `<td><strong>${values.get("name")}</strong></td><td>${values.get("category") || "—"}</td><td>${values.get("supplier") || "—"}</td><td class="money">${formatMoney(values.get("cost") || 0)}</td><td class="money">${formatMoney(values.get("price") || 0)}</td><td>${values.get("quantity") || "—"}</td><td>${values.get("reorder") || "—"}</td><td><span class="tag good">${values.get("status") || "Active"}</span></td><td>•••</td>`
          : "";
        if (row.innerHTML) table.prepend(row);
      }
      form.reset();
      button.disabled = false;
      button.classList.remove("is-saving");
      closeModal(modal);
      showToast(`${title} saved.`);
    }, 500);
  });
});

document.querySelectorAll("[data-table-add-form]").forEach((form) => {
  if (form.id === "productForm") return;

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    const fields = [...form.querySelectorAll("input, select, textarea")];
    const valid = fields.map(validateField).every(Boolean);
    if (!valid) {
      fields
        .find((field) => field.getAttribute("aria-invalid") === "true")
        ?.focus();
      return;
    }
    const button = form.querySelector("[type=submit]");
    button.disabled = true;
    button.classList.add("is-saving");
    window.setTimeout(() => {
      const data = new FormData(form);
      const table = document.querySelector(form.dataset.tableTarget);
      const record = document.createElement("tr");
      record.className = "new-row";
      if (form.dataset.recordKind === "supplier") {
        record.innerHTML = `<td><strong>${data.get("firstName")} ${data.get("lastName")}</strong></td><td>${data.get("username")}</td><td>${data.get("email")}</td><td>${data.get("phone")}</td><td>0</td><td><span class="tag good">${data.get("status")}</span></td><td>•••</td>`;
      } else {
        const total = Number(data.get("quantity")) * Number(data.get("cost"));
        const orderNumber = `PO-${String(table.rows.length + 46).padStart(4, "0")}`;
        const date = data.get("deliveryDate")
          ? new Date(`${data.get("deliveryDate")}T00:00:00`).toLocaleDateString(
              "en-NG",
              { day: "2-digit", month: "short", year: "numeric" },
            )
          : "Not scheduled";
        record.innerHTML = `<td><strong>${orderNumber}</strong></td><td>${data.get("supplier")}</td><td>${data.get("quantity")} × ${data.get("product")}</td><td class="money">${formatMoney(total)}</td><td>${date}</td><td><span class="tag low">Pending</span></td><td>•••</td>`;
      }
      table.prepend(record);
      form.reset();
      button.disabled = false;
      button.classList.remove("is-saving");
      closeModal(form.closest(".modal-overlay"));
      showToast(`${form.dataset.entity} saved.`);
    }, 500);
  });
});

document.addEventListener("click", (event) => {
  const editButton = event.target.closest("[data-edit-product]");

  if (!editButton) return;

  const productId = editButton.dataset.editProduct;

  const product = products.find(
    (item) => String(item.id) === String(productId),
  );

  if (!product) {
    console.error("Product not found:", productId);
    return;
  }

  openEditProduct(product);
});

function openEditProduct(product) {
  editingProductId = product.id;

  productModalTitle.textContent = "Edit product";

  productSaveLabel.textContent = "Save changes";

  document.getElementById("name").value = product.name || "";

  document.getElementById("costPrice").value = product.cost_price ?? "";

  document.getElementById("sellingPrice").value = product.selling_price ?? "";

  document.getElementById("reorderLevel").value = product.reorder_level ?? 5;

  document.getElementById("status").value = product.status || "Active";

  /*
   * Quantity is intentionally NOT changed here.
   * Inventory controls stock quantity.
   */

  productImage.value = "";

  /*
   * Image is optional during editing.
   * If the user doesn't select a new image,
   * PHP keeps the existing image.
   */
  productImage.required = false;

  /*
   * Category and supplier are handled below.
   */
  setSelectValue(document.getElementById("category"), product.category_name);

  setSelectValue(document.getElementById("supplier"), product.supplier_name);

  productModal.classList.add("is-open");
}

function setSelectValue(select, value) {
  if (!select) return;

  const stringValue = String(value ?? "").trim();

  /*
   * First try matching the option value.
   */
  const valueMatch = Array.from(select.options).find(
    (option) => String(option.value).trim() === stringValue,
  );

  if (valueMatch) {
    select.value = valueMatch.value;
    return;
  }

  /*
   * If no value matches, try matching
   * the visible option text.
   */
  const textMatch = Array.from(select.options).find(
    (option) =>
      option.textContent.trim().toLowerCase() === stringValue.toLowerCase(),
  );

  if (textMatch) {
    select.value = textMatch.value;
  }
}

function openAddProduct() {
  editingProductId = null;

  productForm.reset();

  productModalTitle.textContent = "Add product";

  productSaveLabel.textContent = "Save product";

  productImage.required = true;

  document.getElementById("reorderLevel").value = 5;

  productModal.classList.add("is-open");
}

const suppliersTableBody = document.getElementById("suppliersTableBody");

async function getSuppliersTable() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/suppliers",
      {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const data = await response.json();

    const suppliers = data.data || [];

    suppliersTableBody.innerHTML = "";

    suppliers.forEach((supplier) => {
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
          Accept: "application/json",
        },
        credentials: "include",
      },
    );

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const data = await response.json();

    const productOptions = data.data || [];

    productSelect.innerHTML = `
      <option value="">Select product</option>
    `;

    productOptions.forEach((product) => {
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
    status: purchaseStatus,
  };

  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/updatePurchase",
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        credentials: "include",
        body: JSON.stringify(purchaseData),
      },
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

// const productForm = document.getElementById("productForm");
