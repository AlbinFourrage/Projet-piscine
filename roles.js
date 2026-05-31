async function refreshUserInterface() {
  const user = await getCurrentUser();

  const role = user && user.role
    ? String(user.role).trim().toLowerCase()
    : null;

  document.querySelectorAll("[data-guest]").forEach((element) => {
    element.style.display = user ? "none" : "";
  });

  document.querySelectorAll("[data-logged]").forEach((element) => {
    element.style.display = user ? "" : "none";
  });

  document.querySelectorAll("[data-role-buyer]").forEach((element) => {
    element.style.display = role === "buyer" ? "" : "none";
  });

  document.querySelectorAll("[data-role-seller]").forEach((element) => {
    element.style.display = role === "seller" ? "" : "none";
  });

  document.querySelectorAll("[data-role-admin]").forEach((element) => {
    element.style.display = role === "admin" ? "inline-block" : "none";
  });

  const adminButton = document.getElementById("adminButton");

  if (adminButton && role === "admin") {
    adminButton.style.display = "inline-block";
  }

  const userLabel = document.getElementById("userLabel");

  if (userLabel) {
    userLabel.textContent = user ? `${user.first_name} (${role})` : "Visiteur";
  }
}

async function protectPage(allowedRoles = []) {
  const user = await getCurrentUser();

  if (!user) {
    window.location.href = "connexion.html";
    return null;
  }

  const role = user && user.role
    ? String(user.role).trim().toLowerCase()
    : null;

  const normalizedAllowedRoles = allowedRoles.map((allowedRole) =>
    String(allowedRole).trim().toLowerCase()
  );

  if (normalizedAllowedRoles.length > 0 && !normalizedAllowedRoles.includes(role)) {
    window.location.href = "acces-refuse.html";
    return null;
  }

  return user;
}
