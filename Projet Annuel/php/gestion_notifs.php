<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des notifications</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .form-shell { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; margin-bottom: 18px; }
        .form-grid { display: grid; gap: 10px; }
        input, textarea, select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px; }
        button { border: none; border-radius: 6px; padding: 8px 12px; cursor: pointer; font-weight: 700; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-muted { background: #e5e7eb; color: #111827; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #1f2937; color: white; }
        tr:hover { background-color: #f8fafc; }
        .msg { margin: 10px 0; padding: 8px; border-radius: 6px; }
        .ok { background: #dcfce7; color: #166534; }
        .err { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<h1>Gestion des notifications</h1>

<div class="form-shell">
    <h3>Créer une notification</h3>
    <div class="form-grid">
        <label>Mode d'envoi</label>
        <select id="mode_envoi" onchange="changerModeEnvoi()">
            <option value="user">Un utilisateur précis</option>
            <option value="role">Par rôle</option>
            <option value="all">Tous les utilisateurs</option>
        </select>

        <div id="bloc_user">
            <label>ID destinataire</label>
            <input id="id_destinataire" type="number" min="1" placeholder="Ex: 1">
        </div>

        <div id="bloc_role" style="display:none;">
            <label>Rôle cible</label>
            <select id="role_cible">
                <option value="adherant">Adhérant</option>
                <option value="prestataire">Prestataire</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <label>Titre</label>
        <input id="notif_titre" type="text" maxlength="50" placeholder="Titre de la notification">

        <label>Contenu</label>
        <textarea id="notif_contenu" rows="4" maxlength="1000" placeholder="Message..."></textarea>

        <div>
            <button class="btn-primary" onclick="creerNotification()">Envoyer</button>
            <button class="btn-muted" onclick="chargerNotifications()">Actualiser</button>
        </div>
    </div>
</div>

<div id="message"></div>

<hr style="margin:24px 0;">
<h2>Modèles de notification automatique</h2>
<p style="color:#6b7280;font-size:.95em;">Ces modèles sont utilisés pour les notifications envoyées automatiquement (réservation, commande, paiement…). Vous pouvez personnaliser leur titre et contenu. Les variables entre {accolades} sont remplacées dynamiquement.</p>
<div id="resultat_modeles"><em>Chargement des modèles…</em></div>

<hr style="margin:24px 0;">
<h2>Toutes les notifications</h2>
<div>
    <button class="btn-muted" onclick="chargerNotifications()">Actualiser</button>
</div>
<div id="resultat"></div>

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

function changerModeEnvoi() {
    const mode = document.getElementById("mode_envoi").value;
    document.getElementById("bloc_user").style.display = mode == "user" ? "block" : "none";
    document.getElementById("bloc_role").style.display = mode == "role" ? "block" : "none";
}

function afficherMessage(texte, ok) {
    const zone = document.getElementById("message");
    zone.className = "msg " + (ok ? "ok" : "err");
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
        zone.innerHTML = "<p>Aucune notification</p>";
        return;
    }

    let html = "<table><tr><th>ID</th><th>Titre</th><th>Contenu</th><th>Expéditeur</th><th>Destinataire</th><th>Date</th><th>Lu</th><th>Actions</th></tr>";
    data.notification.forEach(n => {
        html += "<tr>";
        html += "<td>" + Number(n.id) + "</td>";
        html += "<td>" + escapeHtml(n.titre) + "</td>";
        html += "<td>" + escapeHtml(n.contenu) + "</td>";
        html += "<td>" + escapeHtml(n.expediteur || "Système") + "</td>";
        html += "<td>" + escapeHtml(n.destinataire || "") + "</td>";
        html += "<td>" + escapeHtml(n.date_envoie || "") + "</td>";
        html += "<td>" + (Number(n.lu) == 1 ? "Oui" : "Non") + "</td>";
        html += "<td>";
        html += "<button class='btn-muted' onclick='modifierNotification(" + Number(n.id) + ", \"" + escapeHtml(n.titre).replaceAll('"', '&quot;') + "\", \"" + escapeHtml(n.contenu).replaceAll('"', '&quot;') + "\")'>Modifier</button> ";
        html += "<button class='btn-danger' onclick='supprimerNotification(" + Number(n.id) + ")'>Supprimer</button>";
        html += "</td></tr>";
    });
    html += "</table>";
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
        zone.innerHTML = "<p>Aucun modèle trouvé (vérifiez votre base de données).</p>";
        return;
    }
Toutes
    let html = "<table><tr><th>Clé</th><th>Titre actuel</th><th>Contenu actuel</th><th>Action</th></tr>";
    data.modele_notification.forEach(m => {
        const hint = escapeHtml(VARIABLES_MODELES[m.cle] || "");
        html += "<tr>";
        html += "<td><strong>" + escapeHtml(m.cle) + "</strong><br><small style='color:#6b7280'>" + hint + "</small></td>";
        html += "<td id='titre_modele_" + Number(m.id) + "'>" + escapeHtml(m.titre) + "</td>";
        html += "<td id='contenu_modele_" + Number(m.id) + "' style='white-space:pre-wrap'>" + escapeHtml(m.contenu) + "</td>";
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
        "<h4>Modifier le modèle « " + escapeHtml(cle) + " »</h4>" +
        (hint ? "<p style='color:#6b7280;font-size:.9em;margin:0 0 8px'>" + escapeHtml(hint) + "</p>" : "") +
        "<label>Titre <small>(max 50 cars.)</small></label>" +
        "<input id='edit_titre_" + id + "' type='text' maxlength='50' value='" + escapeHtml(titreEl.textContent) + "'>" +
        "<label style='margin-top:8px;display:block'>Contenu</label>" +
        "<textarea id='edit_contenu_" + id + "' rows='4'>" + escapeHtml(contenuEl.textContent) + "</textarea>" +
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
