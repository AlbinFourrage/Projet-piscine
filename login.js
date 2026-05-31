const loginForm = document.getElementById("loginForm");
const message = document.getElementById("message");

function showMessage(text, type) {
  message.textContent = text;
  message.className = "alert " + type;
}

loginForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const formData = new FormData(loginForm);
  const data = Object.fromEntries(formData.entries());

  try {
    const response = await fetch(`${API_BASE_URL}/login.php`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      showMessage(result.message || "Erreur de connexion.", "error");
      return;
    }

    localStorage.setItem("autonova_user", JSON.stringify(result.user));
    showMessage("Connexion réussie ! Redirection...", "success");

    setTimeout(() => {
      window.location.href = "mes-ventes.html";
    }, 1000);
  } catch (error) {
    showMessage("Impossible de contacter le serveur PHP.", "error");
  }
});
