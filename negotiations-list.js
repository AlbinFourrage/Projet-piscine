const negotiationsBody = document.getElementById("negotiationsBody");

async function loadNegotiations() {
  try {
    const response = await fetch(`${API_BASE_URL}/get_negotiations.php`, { method: "GET", credentials: "include" });
    const result = await response.json();

    if (response.status === 401) { window.location.href = "connexion.html"; return; }

    if (!result.success || result.negotiations.length === 0) {
      negotiationsBody.innerHTML = `<tr><td colspan="6">Aucune négociation.</td></tr>`;
      return;
    }

    negotiationsBody.innerHTML = result.negotiations.map(neg => `
      <tr>
        <td>${neg.brand} ${neg.model} - ${neg.title}</td>
        <td>${neg.other_first_name} ${neg.other_last_name}</td>
        <td>${Number(neg.price).toLocaleString("fr-FR")} €</td>
        <td><span class="status">${formatStatus(neg.status)}</span></td>
        <td>${new Date(neg.updated_at).toLocaleString("fr-FR")}</td>
        <td><a href="negociation.html?id=${neg.id}" class="small-action view">Ouvrir</a></td>
      </tr>
    `).join("");
  } catch (e) {
    negotiationsBody.innerHTML = `<tr><td colspan="6">Erreur de connexion.</td></tr>`;
  }
}

function formatStatus(status) {
  if (status === "open") return "Ouverte";
  if (status === "accepted") return "Acceptée";
  if (status === "rejected") return "Refusée";
  if (status === "closed") return "Fermée";
  return status;
}
loadNegotiations();
