const detailContainer = document.getElementById("detailContainer");
const params = new URLSearchParams(window.location.search);
const carId = params.get("id");

async function loadCarDetail() {
  const response = await fetch(`${API_BASE_URL}/get_car.php?id=${carId}`, {
    method: "GET",
    credentials: "include"
  });

  const result = await response.json();

  if (!result.success) {
    detailContainer.innerHTML = "<p>Voiture introuvable.</p>";
    return;
  }

  displayCar(result.car);

  if (String(result.car.sale_type).trim().toLowerCase() === "auction") {
    loadAuctionBox(result.car.id);
  }
}

async function loadAuctionBox(carId) {
  const auctionBox = document.getElementById("auctionBox");

  if (!auctionBox) return;

  try {
    const response = await fetch(`${API_BASE_URL}/get_highest_bid.php?car_id=${carId}`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (!result.success) {
      auctionBox.innerHTML = "";
      return;
    }

    const highest = result.highest_bid;

    const currentAmount = highest
      ? Number(highest.amount).toLocaleString("fr-FR")
      : Number(result.start_price).toLocaleString("fr-FR");

    auctionBox.innerHTML = `
      <div class="auction-detail-box">
        <h3>Enchère en cours</h3>

        <p>
          Meilleure offre actuelle :
          <strong>${currentAmount} €</strong>
        </p>

        ${
          highest
            ? `<p>Par : ${highest.first_name} ${highest.last_name}</p>`
            : `<p>Aucune enchère pour le moment.</p>`
        }

        <form id="bidForm" class="bid-form">
          <input
            type="number"
            id="bidAmount"
            min="1"
            step="0.01"
            placeholder="Votre offre en €"
            required
          />

          <button type="submit" class="auction-btn">
            Faire une enchère
          </button>
        </form>

        <p id="bidMessage" class="bid-message"></p>
      </div>
    `;

    document.getElementById("bidForm").addEventListener("submit", async (event) => {
      event.preventDefault();
      await placeBid(carId);
    });

  } catch (error) {
    auctionBox.innerHTML = "";
  }
}

async function placeBid(carId) {
  const bidAmount = document.getElementById("bidAmount").value;
  const bidMessage = document.getElementById("bidMessage");

  try {
    const response = await fetch(`${API_BASE_URL}/place_bid.php`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        car_id: carId,
        amount: bidAmount
      })
    });

    const result = await response.json();

    if (response.status === 401) {
      window.location.href = "connexion.html";
      return;
    }

    if (!result.success) {
      bidMessage.textContent = result.message || "Erreur lors de l'enchère.";
      bidMessage.className = "bid-message error";
      return;
    }

    bidMessage.textContent = "Enchère enregistrée avec succès.";
    bidMessage.className = "bid-message success";

    await loadAuctionBox(carId);

  } catch (error) {
    bidMessage.textContent = "Erreur de connexion au backend.";
    bidMessage.className = "bid-message error";
  }
}

function displayCar(car) {
  const image = car.image_url || "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?q=80&w=1200&auto=format&fit=crop";

  detailContainer.innerHTML = `
    <section class="car-detail-page">
      <div class="car-detail-image">
        <img src="${image}" alt="${car.title}">
      </div>

      <div class="car-detail-info">
        <div class="detail-badges">
          <span class="sale-type">${formatSaleType(car.sale_type)}</span>
          <span class="status">${car.status}</span>
        </div>

        <h2>${car.title}</h2>

        <p class="detail-price">
          ${Number(car.price).toLocaleString("fr-FR")} €
        </p>

        <div class="detail-specs">
          <div>
            <span>Marque</span>
            <strong>${car.brand}</strong>
          </div>

          <div>
            <span>Modèle</span>
            <strong>${car.model}</strong>
          </div>

          <div>
            <span>Année</span>
            <strong>${car.year}</strong>
          </div>

          <div>
            <span>Kilométrage</span>
            <strong>${Number(car.mileage).toLocaleString("fr-FR")} km</strong>
          </div>

          <div>
            <span>Carburant</span>
            <strong>${car.fuel}</strong>
          </div>

          <div>
            <span>État</span>
            <strong>${car.car_condition}</strong>
          </div>
        </div>

        <div class="seller-box">
          <h3>Vendeur</h3>
          <p>${car.seller_first_name} ${car.seller_last_name}</p>
          <p>${car.seller_email}</p>
        </div>

        <div id="auctionBox"></div>

        <div class="detail-actions">
          ${getActionButton(car)}

          <a href="catalogue.html" class="secondary-btn">
            Retour au catalogue
          </a>
        </div>
      </div>
    </section>

    <section class="description-box">
      <h2>Description</h2>
      <p>${car.description}</p>
    </section>
  `;
}

function getActionButton(car) {
  const saleType = String(car.sale_type).trim().toLowerCase();

  if (saleType === "direct") {
    return `<button class="primary-btn">Acheter maintenant</button>`;
  }

  if (saleType === "auction") {
    return `
      <a href="encheres.html?car_id=${car.id}" class="secondary-btn">
        Voir l'historique des enchères
      </a>
    `;
  }

  if (saleType === "negotiation") {
    return `
      <button onclick="showNegotiationForm(${car.id})" class="primary-btn">
        Proposer un prix
      </button>

      <a href="mes-negociations.html" class="secondary-btn">
        Mes négociations
      </a>
    `;
  }

  return `<button class="primary-btn">Contacter le vendeur</button>`;
}

function showNegotiationForm(carId) {
  const oldForm = document.getElementById("negotiationFormBox");

  if (oldForm) {
    oldForm.remove();
  }

  const actions = document.querySelector(".detail-actions");

  if (!actions) {
    return;
  }

  actions.insertAdjacentHTML("beforebegin", `
    <div id="negotiationFormBox" class="negotiation-box">
      <h3>Faire une proposition</h3>

      <input
        id="negotiationPrice"
        type="number"
        min="1"
        step="0.01"
        placeholder="Votre prix proposé"
        required
      />

      <textarea
        id="negotiationMessage"
        required
      >Bonjour, je souhaite négocier le prix de ce véhicule.</textarea>

      <button onclick="startNegotiation(${carId})" class="primary-btn">
        Envoyer la proposition
      </button>

      <p id="negotiationMessageResult" class="bid-message"></p>
    </div>
  `);
}

async function startNegotiation(carId) {
  const price = document.getElementById("negotiationPrice").value;
  const message = document.getElementById("negotiationMessage").value;
  const resultBox = document.getElementById("negotiationMessageResult");

  try {
    const response = await fetch(`${API_BASE_URL}/start_negotiation.php`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        car_id: carId,
        proposed_price: price,
        message: message
      })
    });

    const result = await response.json();

    if (response.status === 401) {
      window.location.href = "connexion.html";
      return;
    }

    if (!result.success) {
      resultBox.textContent = result.message || "Erreur lors de la proposition.";
      resultBox.className = "bid-message error";
      return;
    }

    window.location.href = `negociation.html?id=${result.negotiation_id}`;

  } catch (error) {
    resultBox.textContent = "Erreur de connexion au backend.";
    resultBox.className = "bid-message error";
  }
}

function formatSaleType(type) {
  const saleType = String(type).trim().toLowerCase();

  if (saleType === "direct") return "Achat immédiat";
  if (saleType === "auction") return "Enchère";
  if (saleType === "negotiation") return "Négociation";

  return type;
}

window.showNegotiationForm = showNegotiationForm;
window.startNegotiation = startNegotiation;
window.placeBid = placeBid;


loadCarDetail();
