const form = document.getElementById("saleForm");
const message = document.getElementById("message");

function showMessage(text, type) {
  message.textContent = text;
  message.className = "alert " + type;
}

document.addEventListener("DOMContentLoaded", async () => {
  const user = await requireLogin();

  if (user && user.role !== "seller" && user.role !== "admin") {
    showMessage("Seuls les vendeurs peuvent créer une vente.", "error");
    form.style.display = "none";
  }
});

form.addEventListener("submit", async (event) => {
  event.preventDefault();

  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  try {
    const response = await fetch(`${API_BASE_URL}/create_sale.php`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (response.status === 401) {
      window.location.href = "connexion.html";
      return;
    }

    if (!response.ok || !result.success) {
      showMessage(result.message || "Erreur lors de la création de l’annonce.", "error");
      return;
    }

    showMessage("Annonce créée avec succès !", "success");
    form.reset();

    setTimeout(() => {
      window.location.href = "mes-ventes.html";
    }, 1000);
  } catch (error) {
    showMessage("Impossible de contacter le serveur PHP.", "error");
  }
});
