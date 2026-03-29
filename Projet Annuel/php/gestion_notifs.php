<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">Gestion des notifications</h1>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Créer une notification</h5>
        </div>
        <div class="card-body">
            <form class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mode d'envoi</label>
                    <select id="mode_envoi" onchange="changerModeEnvoi()" class="form-select">
                        <option value="user">Un utilisateur précis</option>
                        <option value="role">Par rôle</option>
                        <option value="all">Tous les utilisateurs</option>
                    </select>
                </div>

                <div id="bloc_user" class="col-md-6">
                    <label class="form-label">ID destinataire</label>
                    <input id="id_destinataire" type="number" min="1" placeholder="Ex: 1" class="form-control">
                </div>

                <div id="bloc_role" style="display:none;" class="col-md-6">
                    <label class="form-label">Rôle cible</label>
                    <select id="role_cible" class="form-select">
                        <option value="adherant">Adhérant</option>
                        <option value="prestataire">Prestataire</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Titre</label>
                    <input id="notif_titre" type="text" maxlength="50" placeholder="Titre de la notification" class="form-control">
                </div>

                <div class="col-12">
                    <label class="form-label">Contenu</label>
                    <textarea id="notif_contenu" rows="4" maxlength="1000" placeholder="Message..." class="form-control"></textarea>
                </div>

                <div class="col-12">
                    <button type="button" class="btn btn-primary" onclick="creerNotification()"><i class="bi bi-send"></i> Envoyer</button>
                    <button type="button" class="btn btn-secondary" onclick="chargerNotifications()"><i class="bi bi-arrow-clockwise"></i> Actualiser</button>
                </div>
            </form>
        </div>
    </div>

    <div id="message"></div>

    <div class="card mt-4 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Modèles de notification automatique</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Ces modèles sont utilisés pour les notifications envoyées automatiquement (réservation, commande, paiement...). Vous pouvez personnaliser leur titre et contenu. Les variables entre {accolades} sont remplacées dynamiquement.</p>
            <div id="resultat_modeles"><em>Chargement des modèles...</em></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Toutes les notifications</h5>
            <button type="button" class="btn btn-sm btn-light" onclick="chargerNotifications()"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
        <div class="card-body">
            <div id="resultat"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
function changerModeEnvoi() {
    const mode = document.getElementById("mode_envoi").value;
    document.getElementById("bloc_user").style.display = mode == "user" ? "block" : "none";
    document.getElementById("bloc_role").style.display = mode == "role" ? "block" : "none";
}

function afficherMessage(texte, ok) {
    const zone = document.getElementById("message");
    zone.className = ok ? "alert alert-success" : "alert alert-danger";
    zone.textContent = texte;
}

async function chargerNotifications() {
    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const response = await fetch(base + "/gestion_notifications", {
        method: "GET",
        headers: { "Token": token }
    });

    if (!response.ok) {
        const text = await response.text();
        afficherMessage(text || "Erreur de chargement", false);
        return;
    }

    const data = await response.json();
    const zone = document.getElementById("resultat");

    if (!Array.isArray(data.notification) || data.notification.length == 0) {
        zone.innerHTML = "<div class='alert alert-info'>Aucune notification</div>";
        return;
    }

    let html = "<div class='table-responsive'><table class='table table-hover'><thead class='table-dark'><tr><th>ID</th><th>Titre</th><th>Contenu</th><th>Expéditeur</th><th>Destinataire</th><th>Date</th><th>Lu</th><th>Actions</th></tr></thead><tbody>";
    data.notification.forEach(n => {
        html += "<tr>";
        html += "<td>" + Number(n.id) + "</td>";
        html += "<td>" + String(n.titre) + "</td>";
        html += "<td>" + String(n.contenu) + "</td>";
        html += "<td>" + String(n.expediteur || "Système") + "</td>";
        html += "<td>" + String(n.destinataire || "") + "</td>";
        html += "<td>" + String(n.date_envoie || "") + "</td>";
        html += "<td>" + (Number(n.lu) == 1 ? "<span class='badge bg-success'>Oui</span>" : "<span class='badge bg-warning'>Non</span>") + "</td>";
        html += "<td>";
        html += "<button class='btn btn-sm btn-warning' onclick='modifierNotification(" + Number(n.id) + ", \"" + String(n.titre).replaceAll('"', '&quot;') + "\", \"" + String(n.contenu).replaceAll('"', '&quot;') + "\")'>Modifier</button> ";
        html += "<button class='btn btn-sm btn-danger' onclick='supprimerNotification(" + Number(n.id) + ")'>Supprimer</button>";
        html += "</td></tr>";
    });
    html += "</tbody></table></div>";
    zone.innerHTML = html;
}

async function creerNotification() {
    const mode = document.getElementById("mode_envoi").value;
    const titre = document.getElementById("notif_titre").value.trim();
    const contenu = document.getElementById("notif_contenu").value.trim();
    const idDest = Number(document.getElementById("id_destinataire").value || 0);
    const roleCible = document.getElementById("role_cible").value;

    const payload = { titre: titre, contenu: contenu };
    if (mode === "user") payload.id_destinataire = idDest;
    if (mode === "role") payload.role_cible = roleCible;
    if (mode === "all") payload.role_cible = "all";

    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const response = await fetch(base + "/creer_notification", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Token": token
        },
        body: JSON.stringify(payload)
    });

    const contenu2 = await response.text();
    let data = {};
    try { data = JSON.parse(contenu2); } catch {}

    if (!response.ok) {
        afficherMessage(data.message || contenu2 || "Erreur d'envoi", false);
        return;
    }

    afficherMessage(data.message || "Notification envoyée", true);
    document.getElementById("notif_titre").value = "";
    document.getElementById("notif_contenu").value = "";
    chargerNotifications();
}

async function modifierNotification(id, titreActuel, contenuActuel) {
    const titre = prompt("Nouveau titre:", titreActuel || "");
    if (titre == null) return;
    const contenu = prompt("Nouveau contenu:", contenuActuel || "");
    if (contenu == null) return;

    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const response = await fetch(base + "/modifier_notification/" + encodeURIComponent(id), {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json",
            "Token": token
        },
        body: JSON.stringify({ titre: titre.trim(), contenu: contenu.trim() })
    });

    const text = await response.text();
    let data = {};
    try { data = JSON.parse(text); } catch {}

    if (!response.ok) {
        afficherMessage(data.message || text || "Erreur de modification", false);
        return;
    }

    afficherMessage(data.message || "Notification modifiée", true);
    chargerNotifications();
}

async function supprimerNotification(id) {
    if (!confirm("Supprimer cette notification ?")) return;

    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const response = await fetch(base + "/supprimer_notification/" + encodeURIComponent(id), {
        method: "DELETE",
        headers: { "Token": token }
    });

    const text = await response.text();
    let data = {};
    try { data = JSON.parse(text); } catch {}

    if (!response.ok) {
        afficherMessage(data.message || text || "Erreur de suppression", false);
        return;
    }

    afficherMessage(data.message || "Notification supprimée", true);
    chargerNotifications();
}

const VARIABLES_MODELES = {
    "reservation_service":    "Variables disponibles : {service} (nom du service), {date} (date et heure)",
    "reservation_evenement":  "Variables disponibles : {evenement} (nom de l'événement)",
    "commande_creee":         "Variables disponibles : {id} (numéro de commande), {mode} (mode de paiement)",
    "paiement_mise_a_jour":   "Variables disponibles : {id} (numéro de commande), {statut} (statut du paiement)",
    "contact_recu":           "Aucune variable disponible pour ce modèle."
};

async function chargerModeles() {
    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const response = await fetch(base + "/modeles_notifications", {
        method: "GET",
        headers: { "Token": token }
    });

    const zone = document.getElementById("resultat_modeles");

    if (!response.ok) {
        zone.innerHTML = "<p style='color:red'>Erreur de chargement des modèles</p>";
        return;
    }

    const data = await response.json();

    if (!Array.isArray(data.modele_notification) || data.modele_notification.length === 0) {
        zone.innerHTML = "<p>Aucun module trouvez (verifiez votre base de données).</p>";
        return;
    }
    let html = "<table><tr><th>Clé</th><th>Titre actuel</th><th>Contenu actuel</th><th>Action</th></tr>";
    data.modele_notification.forEach(m => {
        const hint = String(VARIABLES_MODELES[m.cle] || "");
        html += "<tr>";
        html += "<td><strong>" + String(m.cle) + "</strong><br><small style='color:#6b7280'>" + hint + "</small></td>";
        html += "<td id='titre_modele_" + Number(m.id) + "'>" + String(m.titre) + "</td>";
        html += "<td id='contenu_modele_" + Number(m.id) + "' style='white-space:pre-wrap'>" + String(m.contenu) + "</td>";
        html += "<td><button class='btn-primary' onclick='editerModele(" + Number(m.id) + ", " + JSON.stringify(m.cle) + ")'>Modifier</button></td>";
        html += "</tr>";
    });
    html += "</table>";
    zone.innerHTML = html;
}

function editerModele(id, cle) {
    const titreEl = document.getElementById("titre_modele_" + id);
    const contenuEl = document.getElementById("contenu_modele_" + id);
    if (!titreEl || !contenuEl) return;

    const hint = VARIABLES_MODELES[cle] || "";
    const zone = document.getElementById("resultat_modeles");

    const editDiv = document.createElement("div");
    editDiv.className = "form-shell";
    editDiv.style.marginTop = "10px";
    editDiv.innerHTML =
        "<h4>Modifier le modèle « " + String(cle) + " »</h4>" +
        (hint ? "<p style='color:#6b7280;font-size:.9em;margin:0 0 8px'>" + String(hint) + "</p>" : "") +
        "<label>Titre <small>(max 50 cars.)</small></label>" +
        "<input id='edit_titre_" + id + "' type='text' maxlength='50' value='" + String(titreEl.textContent) + "'>" +
        "<label style='margin-top:8px;display:block'>Contenu</label>" +
        "<textarea id='edit_contenu_" + id + "' rows='4'>" + String(contenuEl.textContent) + "</textarea>" +
        "<div style='margin-top:8px'>" +
        "<button class='btn-primary' onclick='sauvegarderModele(" + id + ")'>Enregistrer</button> " +
        "<button class='btn-muted' onclick='chargerModeles()'>Annuler</button>" +
        "</div>";

    titreEl.closest("table").after(editDiv);
}

async function sauvegarderModele(id) {
    const titreInput = document.getElementById("edit_titre_" + id);
    const contenuInput = document.getElementById("edit_contenu_" + id);
    if (!titreInput || !contenuInput) return;

    const titre = titreInput.value.trim();
    const contenu = contenuInput.value.trim();
    if (!titre || !contenu) {
        afficherMessage("Titre et contenu obligatoires", false);
        return;
    }

    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || "http://localhost:9000");
    const response = await fetch(base + "/modifier_modele/" + encodeURIComponent(id), {
        method: "PATCH",
        headers: { "Content-Type": "application/json", "Token": token },
        body: JSON.stringify({ titre: titre, contenu: contenu })
    });

    const text = await response.text();
    let data = {};
    try { data = JSON.parse(text); } catch {}

    if (!response.ok) {
        afficherMessage(data.message || text || "Erreur de modification", false);
        return;
    }

    afficherMessage(data.message || "Modèle mis à jour", true);
    chargerModeles();
}

async function init() {
    const token = localStorage.getItem("token") || "";
    if (!await loginUser("online", token)) return;
    if (!await adminUser(token)) return;
    changerModeEnvoi();
    chargerModeles();
    chargerNotifications();
}

window.addEventListener("pageshow", function(event) {
    if (event.persisted) window.location.reload();
});

init();
</script>
</body>
</html>

