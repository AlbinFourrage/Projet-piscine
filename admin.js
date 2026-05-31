const totalUsers = document.getElementById("totalUsers");
const totalCars = document.getElementById("totalCars");
const activeCars = document.getElementById("activeCars");
const soldCars = document.getElementById("soldCars");
const usersBody = document.getElementById("usersBody");
const carsBody = document.getElementById("carsBody");
const adminMessage = document.getElementById("adminMessage");
const usersPanel = document.getElementById("usersPanel");
const carsPanel = document.getElementById("carsPanel");
const tabButtons = document.querySelectorAll(".admin-tab");

function showAdminMessage(text, type) {
  adminMessage.textContent = text;
  adminMessage.className = "alert " + type;
}

tabButtons.forEach((button) => {
  button.addEventListener("click", () => {
    tabButtons.forEach((btn) => btn.classList.remove("active"));
    button.classList.add("active");

    if (button.dataset.tab === "users") {
      usersPanel.classList.remove("hidden");
      carsPanel.classList.add("hidden");
    } else {
      carsPanel.classList.remove("hidden");
      usersPanel.classList.add("hidden");
    }
  });
});

async function loadAdminDashboard() {
  await loadStats();
  await loadUsers();
  await loadCars();
}

async function loadStats() {
  const response = await fetch(`${API_BASE_URL}/admin_get_stats.php`, {
    method: "GET",
    credentials: "include"
  });

  const result = await response.json();

  if (!result.success) {
    showAdminMessage(result.message || "Erreur statistiques.", "error");
    return;
  }

  totalUsers.textContent = result.stats.total_users;
  totalCars.textContent = result.stats.total_cars;
  activeCars.textContent = result.stats.active_cars;
  soldCars.textContent = result.stats.sold_cars;
}

async function loadUsers() {
  try {
    const response = await fetch(`${API_BASE_URL}/admin_get_users.php`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (!result.success) {
      usersBody.innerHTML = `<tr><td colspan="6">${result.message || "Erreur."}</td></tr>`;
      return;
    }

    usersBody.innerHTML = result.users.map((user) => `
      <tr>
        <td>${user.id}</td>
        <td>${user.first_name} ${user.last_name}</td>
        <td>${user.email}</td>
        <td>
          <select onchange="updateUserRole(${user.id}, this.value)" class="admin-select">
            <option value="buyer" ${user.role === "buyer" ? "selected" : ""}>Acheteur</option>
            <option value="seller" ${user.role === "seller" ? "selected" : ""}>Vendeur</option>
            <option value="admin" ${user.role === "admin" ? "selected" : ""}>Admin</option>
          </select>
        </td>
        <td>${new Date(user.created_at).toLocaleDateString("fr-FR")}</td>
        <td><button onclick="deleteUser(${user.id})" class="small-action delete">Supprimer</button></td>
      </tr>
    `).join("");
  } catch (error) {
    usersBody.innerHTML = `<tr><td colspan="6">Erreur de connexion.</td></tr>`;
  }
}

async function loadCars() {
  try {
    const response = await fetch(`${API_BASE_URL}/admin_get_cars.php`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (!result.success) {
      carsBody.innerHTML = `<tr><td colspan="7">${result.message || "Erreur."}</td></tr>`;
      return;
    }

    carsBody.innerHTML = result.cars.map((car) => `
      <tr>
        <td>${car.id}</td>
        <td>${car.brand} ${car.model}<br><small>${car.title}</small></td>
        <td>${car.seller_first_name} ${car.seller_last_name}<br><small>${car.seller_email}</small></td>
        <td>${Number(car.price).toLocaleString("fr-FR")} €</td>
        <td>${formatSaleType(car.sale_type)}</td>
        <td>
          <select onchange="updateCarStatus(${car.id}, this.value)" class="admin-select">
            <option value="active" ${car.status === "active" ? "selected" : ""}>Active</option>
            <option value="sold" ${car.status === "sold" ? "selected" : ""}>Vendue</option>
            <option value="inactive" ${car.status === "inactive" ? "selected" : ""}>Inactive</option>
            <option value="pending" ${car.status === "pending" ? "selected" : ""}>En attente</option>
          </select>
        </td>
        <td>
          <a href="detail-voiture.html?id=${car.id}" class="small-action view">Voir</a>
          <button onclick="deleteCar(${car.id})" class="small-action delete">Supprimer</button>
        </td>
      </tr>
    `).join("");
  } catch (error) {
    carsBody.innerHTML = `<tr><td colspan="7">Erreur de connexion.</td></tr>`;
  }
}

async function updateUserRole(id, role) {
  const response = await fetch(`${API_BASE_URL}/admin_update_user_role.php`, {
    method: "POST",
    credentials: "include",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({id, role})
  });

  const result = await response.json();

  if (!result.success) {
    showAdminMessage(result.message || "Erreur.", "error");
    return;
  }

  showAdminMessage("Rôle mis à jour.", "success");
  await loadStats();
}

async function deleteUser(id) {
  if (!confirm("Supprimer cet utilisateur ?")) return;

  const response = await fetch(`${API_BASE_URL}/admin_delete_user.php`, {
    method: "POST",
    credentials: "include",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({id})
  });

  const result = await response.json();

  if (!result.success) {
    showAdminMessage(result.message || "Erreur.", "error");
    return;
  }

  showAdminMessage("Utilisateur supprimé.", "success");
  await loadAdminDashboard();
}

async function updateCarStatus(id, status) {
  const response = await fetch(`${API_BASE_URL}/admin_update_car_status.php`, {
    method: "POST",
    credentials: "include",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({id, status})
  });

  const result = await response.json();

  if (!result.success) {
    showAdminMessage(result.message || "Erreur.", "error");
    return;
  }

  showAdminMessage("Statut mis à jour.", "success");
  await loadStats();
}

async function deleteCar(id) {
  if (!confirm("Supprimer cette annonce ?")) return;

  const response = await fetch(`${API_BASE_URL}/admin_delete_car.php`, {
    method: "POST",
    credentials: "include",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({id})
  });

  const result = await response.json();

  if (!result.success) {
    showAdminMessage(result.message || "Erreur.", "error");
    return;
  }

  showAdminMessage("Annonce supprimée.", "success");
  await loadAdminDashboard();
}

function formatSaleType(type) {
  if (type === "direct") return "Achat immédiat";
  if (type === "auction") return "Enchère";
  if (type === "negotiation") return "Négociation";
  return type;
}

window.updateUserRole = updateUserRole;
window.deleteUser = deleteUser;
window.updateCarStatus = updateCarStatus;
window.deleteCar = deleteCar;

loadAdminDashboard();

