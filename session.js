async function getCurrentUser() {
  try {
    const response = await fetch(`${API_BASE_URL}/me.php`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (!result.success) {
      localStorage.removeItem("autonova_user");
      return null;
    }

    localStorage.setItem("autonova_user", JSON.stringify(result.user));
    return result.user;
  } catch (error) {
    localStorage.removeItem("autonova_user");
    return null;
  }
}

async function requireLogin() {
  const user = await getCurrentUser();

  if (!user) {
    window.location.href = "connexion.html";
    return null;
  }

  return user;
}

async function logout() {
  try {
    await fetch(`${API_BASE_URL}/logout.php`, {
      method: "POST",
      credentials: "include"
    });
  } catch (error) {}

  localStorage.removeItem("autonova_user");
  window.location.href = "connexion.html";
}
