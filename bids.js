const bidsBody = document.getElementById("bidsBody");
const auctionTitle = document.getElementById("auctionTitle");
const backToDetail = document.getElementById("backToDetail");

const params = new URLSearchParams(window.location.search);
const carId = params.get("car_id");

async function loadBids() {
  if (!carId) {
    bidsBody.innerHTML = `<tr><td colspan="4">Aucune voiture sélectionnée.</td></tr>`;
    return;
  }

  backToDetail.href = `detail-voiture.html?id=${carId}`;

  try {
    const response = await fetch(`${API_BASE_URL}/get_bids.php?car_id=${carId}`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (!result.success) {
      bidsBody.innerHTML = `<tr><td colspan="4">${result.message || "Erreur de chargement."}</td></tr>`;
      return;
    }

    auctionTitle.textContent = `${result.car.brand} ${result.car.model} - ${result.car.title}`;

    if (!result.bids || result.bids.length === 0) {
      bidsBody.innerHTML = `<tr><td colspan="4">Aucune enchère pour le moment.</td></tr>`;
      return;
    }

    bidsBody.innerHTML = result.bids.map((bid) => `
      <tr>
        <td><strong>${Number(bid.amount).toLocaleString("fr-FR")} €</strong></td>
        <td>${bid.first_name} ${bid.last_name}</td>
        <td>${bid.email}</td>
        <td>${new Date(bid.created_at).toLocaleString("fr-FR")}</td>
      </tr>
    `).join("");
  } catch (error) {
    bidsBody.innerHTML = `<tr><td colspan="4">Erreur de connexion au backend.</td></tr>`;
  }
}

loadBids();
