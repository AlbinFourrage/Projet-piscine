const catalogueGrid = document.getElementById("catalogueGrid");
const resultCount = document.getElementById("resultCount");

const searchInput = document.getElementById("searchInput");
const maxPriceInput = document.getElementById("maxPriceInput");
const fuelSelect = document.getElementById("fuelSelect");
const conditionSelect = document.getElementById("conditionSelect");
const sortSelect = document.getElementById("sortSelect");
const resetFiltersBtn = document.getElementById("resetFiltersBtn");

async function loadCatalogue() {
  const params = new URLSearchParams();

  if (searchInput.value.trim()) {
    params.append("search", searchInput.value.trim());
  }

  if (maxPriceInput.value.trim()) {
    params.append("max_price", maxPriceInput.value.trim());
  }

  if (fuelSelect.value) {
    params.append("fuel", fuelSelect.value);
  }

  if (conditionSelect.value) {
    params.append("condition", conditionSelect.value);
  }

  if (sortSelect.value) {
    params.append("sort", sortSelect.value);
  }

  try {
    catalogueGrid.innerHTML = "<p>Chargement...</p>";

    const response = await fetch(`${API_BASE_URL}/get_cars.php?${params.toString()}`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (!result.success) {
      catalogueGrid.innerHTML = "<p>Erreur lors du chargement du catalogue.</p>";
      resultCount.textContent = "0 annonce trouvée";
      return;
    }

    displayCars(result.cars);
  } catch (error) {
    catalogueGrid.innerHTML = "<p>Impossible de contacter le backend.</p>";
    resultCount.textContent = "Erreur de chargement";
    console.error(error);
  }
}

function displayCars(cars) {
  if (!cars || cars.length === 0) {
    catalogueGrid.innerHTML = "<p>Aucune voiture ne correspond à ta recherche.</p>";
    resultCount.textContent = "0 annonce trouvée";
    return;
  }

  resultCount.textContent = `${cars.length} annonce(s) trouvée(s)`;

  catalogueGrid.innerHTML = cars.map((car) => {
    const image = car.image_url && car.image_url.trim()
      ? car.image_url
      : "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?q=80&w=900&auto=format&fit=crop";

    const price = Number(car.price).toLocaleString("fr-FR");
    const mileage = Number(car.mileage).toLocaleString("fr-FR");

    return `
      <article class="car-card">
        <img src="${image}" alt="${car.title}" />

        <div>
          <span class="sale-type">${formatSaleType(car.sale_type)}</span>

          <h3>${car.title}</h3>

          <p>${car.brand} ${car.model} • ${car.year}</p>
          <p>${mileage} km • ${car.fuel} • ${car.car_condition}</p>
          <p>Vendeur : ${car.seller_first_name} ${car.seller_last_name}</p>

          <strong>${price} €</strong>

          <a href="detail-voiture.html?id=${car.id}">Détails</a>
        </div>
      </article>
    `;
  }).join("");
}

function formatSaleType(type) {
  if (type === "direct") return "Achat immédiat";
  if (type === "auction") return "Enchère";
  if (type === "negotiation") return "Négociation";
  return type;
}

function debounce(callback, delay = 350) {
  let timer;

  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => callback(...args), delay);
  };
}

const delayedLoad = debounce(loadCatalogue);

searchInput.addEventListener("input", delayedLoad);
maxPriceInput.addEventListener("input", delayedLoad);
fuelSelect.addEventListener("change", loadCatalogue);
conditionSelect.addEventListener("change", loadCatalogue);
sortSelect.addEventListener("change", loadCatalogue);

resetFiltersBtn.addEventListener("click", () => {
  searchInput.value = "";
  maxPriceInput.value = "";
  fuelSelect.value = "";
  conditionSelect.value = "";
  sortSelect.value = "recent";

  loadCatalogue();
});

loadCatalogue();
