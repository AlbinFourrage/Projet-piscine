const editSaleForm = document.getElementById("editSaleForm");
const message = document.getElementById("message");

const params = new URLSearchParams(window.location.search);
const saleId = params.get("id");

function showMessage(text, type) {
  message.textContent = text;
  message.className = "alert " + type;
}

async function loadSale() {
  if (!saleId) {
    showMessage("Aucune annonce sélectionnée.", "error");
    editSaleForm.style.display = "none";
    return;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/get_sale.php?id=${saleId}`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (!result.success) {
      showMessage(result.message || "Annonce introuvable.", "error");
      editSaleForm.style.display = "none";
      return;
    }

    fillForm(result.sale);
  } catch (error) {
    showMessage("Erreur de connexion au backend.", "error");
  }
}

function fillForm(sale) {
  document.getElementById("id").value = sale.id;
  document.getElementById("title").value = sale.title;
  document.getElementById("brand").value = sale.brand;
  document.getElementById("model").value = sale.model;
  document.getElementById("year").value = sale.year;
  document.getElementById("mileage").value = sale.mileage;
  document.getElementById("fuel").value = sale.fuel;
  document.getElementById("condition").value = sale.car_condition;
  document.getElementById("price").value = sale.price;
  document.getElementById("sale_type").value = sale.sale_type;
  document.getElementById("status").value = sale.status;
  document.getElementById("image_url").value = sale.image_url || "";
  document.getElementById("description").value = sale.description;
}

editSaleForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const formData = new FormData(editSaleForm);
  const data = Object.fromEntries(formData.entries());

  try {
    const response = await fetch(`${API_BASE_URL}/update_sale.php`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (!result.success) {
      showMessage(result.message || "Erreur lors de la modification.", "error");
      return;
    }

    showMessage("Annonce modifiée avec succès.", "success");

    setTimeout(() => {
      window.location.href = "mes-ventes.html";
    }, 1000);
  } catch (error) {
    showMessage("Erreur de connexion au backend.", "error");
  }
});

loadSale();
