const registerForm = document.getElementById("registerForm");
const message = document.getElementById("message");

function showMessage(text, type) {
  message.textContent = text;
  message.className = "alert " + type;
}

registerForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const formData = new FormData(registerForm);
  const data = Object.fromEntries(formData.entries());

  if (data.password.length < 8) {
    showMessage("Le mot de passe doit contenir au moins 8 caractères.", "error");
    return;
  }

  if (data.password !== data.confirm_password) {
    showMessage("Les mots de passe ne correspondent pas.", "error");
    return;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/register.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      showMessage(result.message || "Erreur lors de la création du compte.", "error");
      return;
    }

    showMessage("Compte créé avec succès ! Redirection...", "success");
    registerForm.reset();

    setTimeout(() => {
      window.location.href = "connexion.html";
    }, 1200);
  } catch (error) {
    showMessage("Impossible de contacter le serveur PHP.", "error");
  }
});
