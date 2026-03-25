<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mes notifications</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .actions { margin-bottom: 16px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { border: none; border-radius: 6px; padding: 8px 12px; cursor: pointer; font-weight: 700; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-muted { background: #e5e7eb; color: #111827; }
        .notification-list { display: grid; gap: 12px; }
        .notification-item { border: 1px solid #ddd; border-radius: 10px; padding: 12px; background: #fff; }
        .notification-item.unread { border-left: 6px solid #2563eb; background: #eff6ff; }
        .notification-title { font-weight: 800; color: #111827; }
        .notification-meta { color: #6b7280; font-size: 0.9rem; margin-top: 6px; }
        .notification-content { margin-top: 10px; white-space: pre-wrap; }
        .notification-actions { margin-top: 10px; display: flex; gap: 8px; }
        .empty { padding: 12px; background: #f3f4f6; border-radius: 8px; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<h1>Mes notifications</h1>
<div class="actions">
    <button class="btn btn-primary" onclick="marquerToutesLues()">Tout marquer comme lu</button>
    <button class="btn btn-muted" onclick="chargerNotifications()">Actualiser</button>
</div>

<div id="resultat" class="notification-list"><div class="empty">Chargement...</div></div>

<?php include 'includes/footer.php'; ?>

<script>
function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

async function chargerNotifications() {
    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const zone = document.getElementById("resultat");

    const response = await fetch(base + "/notifications", {
        method: "GET",
        headers: { "Token": token }
    });

    if (!response.ok) {
        const text = await response.text();
        zone.innerHTML = "<div class='empty'>" + escapeHtml(text || "Erreur de chargement") + "</div>";
        return;
    }

    const data = await response.json();
    if (!Array.isArray(data.notification) || data.notification.length === 0) {
        zone.innerHTML = "<div class='empty'>Aucune notification</div>";
        return;
    }

    let html = "";
    data.notification.forEach(n => {
        const unreadClass = Number(n.lu) === 1 ? "" : " unread";
        html += "<div class='notification-item" + unreadClass + "'>";
        html += "<div class='notification-title'>" + escapeHtml(n.titre) + "</div>";
        html += "<div class='notification-meta'>" + escapeHtml(n.date_envoie) + " - De: " + escapeHtml(n.expediteur || "Système") + "</div>";
        html += "<div class='notification-content'>" + escapeHtml(n.contenu) + "</div>";
        html += "<div class='notification-actions'>";
        if (Number(n.lu) !== 1) {
            html += "<button class='btn btn-primary' onclick='marquerLue(" + Number(n.id) + ")'>Marquer lue</button>";
        }
        html += "<button class='btn btn-danger' onclick='supprimerNotification(" + Number(n.id) + ")'>Supprimer</button>";
        html += "</div></div>";
    });

    zone.innerHTML = html;
}

async function marquerLue(id) {
    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const response = await fetch(base + "/notifications/" + encodeURIComponent(id) + "/read", {
        method: "PATCH",
        headers: { "Token": token }
    });

    if (!response.ok) {
        const text = await response.text();
        alert(text || "Erreur");
        return;
    }
    chargerNotifications();
}

async function marquerToutesLues() {
    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const response = await fetch(base + "/notifications/read-all", {
        method: "PATCH",
        headers: { "Token": token }
    });

    if (!response.ok) {
        const text = await response.text();
        alert(text || "Erreur");
        return;
    }
    chargerNotifications();
}

async function supprimerNotification(id) {
    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const response = await fetch(base + "/notifications/" + encodeURIComponent(id), {
        method: "DELETE",
        headers: { "Token": token }
    });

    if (!response.ok) {
        const text = await response.text();
        alert(text || "Erreur");
        return;
    }
    chargerNotifications();
}

async function init() {
    const token = localStorage.getItem("token") || "";
    if (!await loginUser("online", token)) return;
    chargerNotifications();
}

window.addEventListener("pageshow", function(event) {
    if (event.persisted) window.location.reload();
});

init();
</script>
</body>
</html>
