<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contrats</title>
</head>
<body>

<?php include 'includes/header.php' ?>

<div class="container mt-4">
    <h1>Mes contrats</h1>
    <p class="text-muted">Retrouvez ici vos contrats lies aux abonnements et prestations.</p>

    <div id="contrat">
        <p>Chargement en cours...</p>
    </div>
</div>

<?php include 'includes/footer.php';?>

<script>
async function listcontrats(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/contrats", {
        method: "GET",
        headers: {"Token": token},
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

    const data = await response.json();
    const tab_contrat = document.getElementById("contrat")
    if (data.message){
        tab_contrat.innerHTML = "<div class='alert alert-info'>" + data.message + "</div>";
    } else {
        const formatDate = (value) => {
            if (!value) return "—";
            return String(value).slice(0, 10);
        };

        const badgeType = (type) => {
            if (String(type || '').toLowerCase() === 'site') return "<span class='badge bg-primary'>Site</span>";
            return "<span class='badge bg-secondary'>Presta</span>";
        };

        const paiementLabel = (mode) => {
            const normalized = String(mode || '').toLowerCase();
            if (normalized === 'an') return "Annuel";
            if (normalized === 'mois') return "Mensuel";
            return mode || '—';
        };

        let html = "<div class='table-responsive'><table class='table table-hover table-bordered align-middle'>";
        html += "<thead class='table-light'><tr>";
        html += "<th>#</th><th>Nom du contrat</th><th>Type</th><th>Paiement</th><th>Date debut</th><th>Date fin</th>";
        html += "</tr></thead><tbody>";

        data.contrat.forEach(c => {
            html += "<tr>";
            html += "<td>" + Number(c.id || 0) + "</td>";
            html += "<td>" + String(c.nom || '—') + "</td>";
            html += "<td>" + badgeType(c.type_contrat) + "</td>";
            html += "<td>" + paiementLabel(c.type_paiement) + "</td>";
            html += "<td>" + formatDate(c.date_debut) + "</td>";
            html += "<td>" + formatDate(c.date_fin) + "</td>";
            html += "</tr>";
        });

        html += "</tbody></table></div>";
        tab_contrat.innerHTML = html;
    }
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        listcontrats(token);
    }

init()
</script>

</body>
</html>

