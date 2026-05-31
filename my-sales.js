const salesBody = document.getElementById("salesBody");

const totalSales = document.getElementById("totalSales");
const activeSales = document.getElementById("activeSales");
const soldSales = document.getElementById("soldSales");
const inactiveSales = document.getElementById("inactiveSales");

async function loadSales() {
  try {
    const response = await fetch(`${API_BASE_URL}/get_my_sales.php`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (response.status === 401) {
      window.location.href = "connexion.html";
      return;
    }

    if (!result.success) {
      salesBody.innerHTML = `<tr><td colspan="7">${result.message || "Erreur de chargement."}</td></tr>`;
      return;
    }

    updateStats(result.sales);
    displaySales(result.sales);
  } catch (error) {
    salesBody.innerHTML = `<tr><td colspan="7">Erreur de connexion au backend.</td></tr>`;
    console.error(error);
  }
}

function updateStats(sales) {
  totalSales.textContent = sales.length;
  activeSales.textContent = sales.filter(sale => sale.status === "active").length;
  soldSales.textContent = sales.filter(sale => sale.status === "sold").length;
  inactiveSales.textContent = sales.filter(sale => sale.status === "inactive" || sale.status === "pending").length;
}

function displaySales(sales) {
  if (!sales || sales.length === 0) {
    salesBody.innerHTML = `<tr><td colspan="7">Aucune annonce pour le moment.</td></tr>`;
    return;
  }

  salesBody.innerHTML = sales.map((sale) => `
    <tr>
      <td>${sale.title}</td>
      <td>${sale.brand} ${sale.model} (${sale.year})</td>
      <td>${Number(sale.price).toLocaleString("fr-FR")} €</td>
      <td>${formatSaleType(sale.sale_type)}</td>
      <td><span class="status">${formatStatus(sale.status)}</span></td>
      <td>${new Date(sale.created_at).toLocaleDateString("fr-FR")}</td>
      <td>
        <div class="action-buttons">
          <a href="detail-voiture.html?id=${sale.id}" class="small-action view">Voir</a>
          <a href="modifier-vente.html?id=${sale.id}" class="small-action edit">Modifier</a>
          ${sale.status !== "sold" ? `<button onclick="markAsSold(${sale.id})" class="small-action sold">Vendue</button>` : ""}
          <button onclick="deleteSale(${sale.id})" class="small-action delete">Supprimer</button>
        </div>
      </td>
    </tr>
  `).join("");
}

async function deleteSale(id) {
  if (!confirm("Voulez-vous vraiment supprimer cette annonce ?")) {
    return;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/delete_sale.php`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ id })
    });

    const result = await response.json();

    if (!result.success) {
      alert(result.message || "Erreur lors de la suppression.");
      return;
    }

    loadSales();
  } catch (error) {
    alert("Erreur de connexion au backend.");
  }
}

async function markAsSold(id) {
  if (!confirm("Marquer cette annonce comme vendue ?")) {
    return;
  }

  await changeSaleStatus(id, "sold");
}

async function changeSaleStatus(id, status) {
  try {
    const response = await fetch(`${API_BASE_URL}/change_sale_status.php`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ id, status })
    });

    const result = await response.json();

    if (!result.success) {
      alert(result.message || "Erreur lors du changement de statut.");
      return;
    }

    loadSales();
  } catch (error) {
    alert("Erreur de connexion au backend.");
  }
}

function formatSaleType(type) {
  if (type === "direct") return "Achat immédiat";
  if (type === "auction") return "Enchère";
  if (type === "negotiation") return "Négociation";
  return type;
}

function formatStatus(status) {
  if (status === "active") return "Active";
  if (status === "sold") return "Vendue";
  if (status === "inactive") return "Inactive";
  if (status === "pending") return "En attente";
  return status;
}

loadSales();
