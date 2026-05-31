const notificationsList = document.getElementById("notificationsList");
const notificationBadge = document.getElementById("notificationBadge");
const notificationCountText = document.getElementById("notificationCountText");
const markAllReadBtn = document.getElementById("markAllReadBtn");
const filterButtons = document.querySelectorAll(".filter-notification");

let currentFilter = "all";

async function loadNotifications() {
  try {
    const response = await fetch(`${API_BASE_URL}/get_notifications.php?filter=${currentFilter}`, {
      method: "GET",
      credentials: "include"
    });

    const result = await response.json();

    if (response.status === 401) {
      window.location.href = "connexion.html";
      return;
    }

    if (!result.success) {
      notificationsList.innerHTML = `<p>${result.message || "Erreur de chargement."}</p>`;
      return;
    }

    updateNotificationBadge(result.unread_count);
    displayNotifications(result.notifications, result.unread_count);
  } catch (error) {
    notificationsList.innerHTML = "<p>Erreur de connexion au backend.</p>";
  }
}

function displayNotifications(notifications, unreadCount) {
  notificationCountText.textContent = `${unreadCount} notification(s) non lue(s)`;

  if (!notifications || notifications.length === 0) {
    notificationsList.innerHTML = "<p>Aucune notification.</p>";
    return;
  }

  notificationsList.innerHTML = notifications.map((notification) => `
    <article class="notification-card ${Number(notification.is_read) === 0 ? "unread" : "read"}">
      <div class="notification-icon">
        ${Number(notification.is_read) === 0 ? "🔔" : "✓"}
      </div>

      <div class="notification-content">
        <p>${notification.message}</p>
        <small>${new Date(notification.created_at).toLocaleString("fr-FR")}</small>
      </div>

      <div class="notification-actions">
        ${
          Number(notification.is_read) === 0
            ? `<button onclick="markAsRead(${notification.id})" class="small-action view">Marquer lu</button>`
            : ""
        }

        <button onclick="deleteNotification(${notification.id})" class="small-action delete">
          Supprimer
        </button>
      </div>
    </article>
  `).join("");
}

function updateNotificationBadge(count) {
  if (!notificationBadge) {
    return;
  }

  if (count > 0) {
    notificationBadge.textContent = count;
    notificationBadge.style.display = "inline-flex";
  } else {
    notificationBadge.textContent = "0";
    notificationBadge.style.display = "none";
  }
}

async function markAsRead(id) {
  try {
    const response = await fetch(`${API_BASE_URL}/mark_notification_read.php`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ id })
    });

    const result = await response.json();

    if (!result.success) {
      alert(result.message || "Erreur lors de la mise à jour.");
      return;
    }

    await loadNotifications();
  } catch (error) {
    alert("Erreur de connexion au backend.");
  }
}

async function markAllAsRead() {
  try {
    const response = await fetch(`${API_BASE_URL}/mark_all_notifications_read.php`, {
      method: "POST",
      credentials: "include"
    });

    const result = await response.json();

    if (!result.success) {
      alert(result.message || "Erreur lors de la mise à jour.");
      return;
    }

    await loadNotifications();
  } catch (error) {
    alert("Erreur de connexion au backend.");
  }
}

async function deleteNotification(id) {
  if (!confirm("Supprimer cette notification ?")) {
    return;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/delete_notification.php`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ id })
    });

    const result = await response.json();

    if (!result.success) {
      alert(result.message || "Erreur lors de la suppression.");
      return;
    }

    await loadNotifications();
  } catch (error) {
    alert("Erreur de connexion au backend.");
  }
}

filterButtons.forEach((button) => {
  button.addEventListener("click", () => {
    filterButtons.forEach((btn) => btn.classList.remove("active"));
    button.classList.add("active");
    currentFilter = button.dataset.filter;
    loadNotifications();
  });
});

markAllReadBtn.addEventListener("click", markAllAsRead);

window.markAsRead = markAsRead;
window.deleteNotification = deleteNotification;

loadNotifications();
