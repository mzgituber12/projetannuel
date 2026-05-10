<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mes notifications</title>
    <style>.mb-custom{
        margin-bottom: 2.3rem
      }</style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="police.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <h1 class="mb-4 text-center" style="font-size:50px">Mes notifications</h1>
    <p class="mb-custom text-center">
        Vos notifications apparaîtront ici. 
        <br>N’hésitez pas à les consulter régulièrement afin de rester informé des dernières mises à jour, messages et actions importantes concernant votre compte.
    </p>
    <div class="mb-4 d-flex gap-2">
        <button class="btn btn-primary" onclick="marquerToutesLues()"><i class="bi bi-check-all"></i> Tout marquer comme lu</button>
        <button class="btn btn-secondary" onclick="chargerNotifications()"><i class="bi bi-arrow-clockwise"></i> Actualiser</button>
    </div>

    <div id="resultat">
        <div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Chargement...</div>
    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
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
        zone.innerHTML = "<div class='alert alert-danger'>" + String(text || "Erreur de chargement") + "</div>";
        return;
    }

    const data = await response.json();
    if (!Array.isArray(data.notification) || data.notification.length === 0) {
        zone.innerHTML = "<div class='alert alert-info'><i class='bi bi-inbox'></i> Aucune notification</div>";
        return;
    }

    let html = "";
    data.notification.forEach(n => {
        const unreadClass = Number(n.lu) === 1 ? "" : "border-start border-primary border-4 bg-light";
        const badge = Number(n.lu) === 1 ? "<span class='badge bg-success'>Lue</span>" : "<span class='badge bg-primary'>Non lue</span>";
        html += "<div class='card mb-3 " + unreadClass + "'>";
        html += "<div class='card-body'>";
        html += "<div class='d-flex justify-content-between align-items-start mb-2'>";
        html += "<h5 class='card-title mb-0'>" + String(n.titre) + "</h5>";
        html += badge;
        html += "</div>";
        html += "<p class='card-subtitle text-muted small mb-3'><i class='bi bi-clock'></i> " + String(n.date_envoie) + " - <i class='bi bi-person'></i> " + String(n.expediteur || "Système") + "</p>";
        html += "<p class='card-text'>" + String(n.contenu) + "</p>";
        html += "<div class='d-flex gap-2'>";
        if (Number(n.lu) !== 1) {
            html += "<button class='btn btn-sm btn-primary' onclick='marquerLue(" + Number(n.id) + ")' title='Marquer comme lue'><i class='bi bi-check-circle'></i> Marquer lue</button>";
        }
        html += "<button class='btn btn-sm btn-danger' onclick='supprimerNotification(" + Number(n.id) + ")' title='Supprimer'><i class='bi bi-trash'></i> Supprimer</button>";
        html += "</div>";
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

