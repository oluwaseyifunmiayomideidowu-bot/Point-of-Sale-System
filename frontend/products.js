const productTableBody = document.getElementById("products-table-body");

let products = [];

async function getProducts() {
  try {
    const response = await fetch(
      "http://localhost/point_of_sale_system/backend/api/products",
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

    const result = await response.json();

    products = result.data || [];

    // Sort products:
    // 1. In Stock
    // 2. Almost Out
    // 3. Out Of Stock
    // 4. Inactive
    products.sort((a, b) => {
      function getStockPriority(product) {
        const quantity = Number(product.quantity) || 0;
        const reorderLevel = Number(product.reorder_level) || 0;

        const status = String(product.status).toLowerCase();

        // Inactive always comes last
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

    productTableBody.innerHTML = "";

    products.forEach((product) => {
      const row = document.createElement("tr");

      row.id = `product-${product.id}`;

      let stockClass;
      let stockText;

      const quantity = Number(product.quantity) || 0;
      const reorderLevel = Number(product.reorder_level) || 0;

      if (quantity === 0) {
        stockClass = "out";
        stockText = "Out Of Stock";
      } else if (quantity <= reorderLevel) {
        stockClass = "low";
        stockText = `${quantity} Remain`;
      } else if (quantity <= reorderLevel + 10) {
        stockClass = "low";
        stockText = "Almost Out";
      } else {
        stockClass = "good";
        stockText = "In Stock";
      }

      row.innerHTML = `
    <td class="item">
      <span class="thumb">
        <img
          src="../..//point_of_sale_system/backend/${product.image}"
          alt="${product.name}"
        >
      </span>

      ${product.name}
    </td>

    <td>${product.category_name}</td>

    <td>${product.sku}</td>

    <td>
      ₦${Number(product.cost_price).toLocaleString()}
    </td>

    <td>
      ₦${Number(product.selling_price).toLocaleString()}
    </td>

    <td>${product.quantity}</td>

    <td>${product.status}</td>

    <td>
      <div class="tag ${stockClass}">
        ${stockText}
      </div>
    </td>

    <td>${formatDate(product.created_at)}</td>

    <td class="product-actions">
  <button
    type="button"
    class="edit-product-button"
    data-edit-product="${product.id}"
    title="Edit product"
    aria-label="Edit ${product.name}"
  >
    ✎
  </button>
</td>
`;

      productTableBody.appendChild(row);
    });
  } catch (error) {
    console.error("Error getting products:", error);

    productTableBody.innerHTML = `
      <tr>
        <td colspan="11">
          Unable to load products.
        </td>
      </tr>
    `;
  }
}

function formatDate(dateString) {
  const date = new Date(dateString);

  return date.toLocaleDateString("en-NG", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}

getProducts();

const categorySelect = document.getElementById("category");

async function getCategories() {
  try {
    const response = await fetch(
      "/point_of_sale_system/backend/api/categories",
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

    const categories = data.data;

    categories.forEach((category) => {
      const option = document.createElement("option");

      option.id = category.id;
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

    const suppliers = data.data;

    suppliers.forEach((supplier) => {
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

const productForm = document.getElementById("productForm");
const productModal = document.getElementById("add-product");

const productModalTitle = document.getElementById("add-product-title");

const productSaveLabel = productForm.querySelector(".save-label");

const productImage = document.getElementById("image");

let editingProductId = null;

productForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const button = productForm.querySelector('[type="submit"]');

  const formData = new FormData();

  formData.append("productName", document.getElementById("name").value.trim());

  formData.append(
    "categoryName",
    document
      .getElementById("category")
      .selectedOptions[0]?.textContent.trim() || "",
  );

  formData.append(
    "supplierName",
    document
      .getElementById("supplier")
      .selectedOptions[0]?.textContent.trim() || "",
  );

  formData.append("costPrice", document.getElementById("costPrice").value);

  formData.append(
    "sellingPrice",
    document.getElementById("sellingPrice").value,
  );

  formData.append(
    "reorderLevel",
    document.getElementById("reorderLevel").value,
  );

  formData.append("status", document.getElementById("status").value);

  if (editingProductId === null) {
    formData.append("quantity", document.getElementById("quantity").value);

    const imageFile = productImage.files[0];

    if (imageFile) {
      formData.append("image", imageFile);
    }
  }

  if (editingProductId !== null) {
    formData.append("productId", editingProductId);

    const imageFile = productImage.files[0];

    if (imageFile) {
      formData.append("image", imageFile);
    }
  }

  const endpoint =
    editingProductId !== null
      ? "/point_of_sale_system/backend/api/updateProduct"
      : "/point_of_sale_system/backend/api/createProduct";

  try {
    button.disabled = true;
    button.classList.add("is-saving");

    const response = await fetch(endpoint, {
      method: "POST",
      body: formData,
      credentials: "include",
    });

    const text = await response.text();

    let data;

    try {
      data = JSON.parse(text);
    } catch {
      console.error("Server response:", text);

      throw new Error("Invalid response from server.");
    }

    if (!response.ok) {
      throw new Error(data.message || "Unable to save product.");
    }

    console.log("Product saved:", data);

    /*
     * Reload products so the table
     * immediately shows the changes.
     */
    await getProducts();

    productForm.reset();

    editingProductId = null;

    productModalTitle.textContent = "Add product";

    productSaveLabel.textContent = "Save product";

    productImage.required = true;

    closeModal(productModal);

    showToast(data.message || "Product saved successfully.");
  } catch (error) {
    console.error("Product save failed:", error);

    showToast(error.message || "Unable to save product.");
  } finally {
    button.disabled = false;
    button.classList.remove("is-saving");
  }
});
