async function refreshNotificationBadge() {
  const badgeElements = document.querySelectorAll("[data-notification-badge]");

  if (badgeElements.length === 0) {
    return;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/get_notification_count.php`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (!result.success) {
      return;
    }

    badgeElements.forEach((badge) => {
      if (result.unread_count > 0) {
        badge.textContent = result.unread_count;
        badge.style.display = "inline-flex";
      } else {
        badge.textContent = "";
        badge.style.display = "none";
      }
    });
  } catch (error) {
    // silencieux pour ne pas bloquer les pages
  }
}

refreshNotificationBadge();
