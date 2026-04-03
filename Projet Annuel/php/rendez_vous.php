<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Mes rendez-vous services</title>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h1 data-i18n>Mes rendez-vous (services)</h1>
    <p class="text-muted" data-i18n>Retrouvez ici tous vos rendez-vous lies a vos inscriptions de service.</p>

    <div id="rdvContainer">
        <p data-i18n>Chargement en cours...</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function esc(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function formatDate(value) {
    if (!value) return '—';
    return String(value).replace('T', ' ').slice(0, 16);
}

function badgeStatut(status) {
    const normalized = String(status || '').toLowerCase();
    if (normalized == 'accepté' || normalized == 'accepte') {
        return '<span class="badge bg-success">accepte</span>';
    }
    if (normalized == 'refusé' || normalized == 'refuse') {
        return '<span class="badge bg-danger">refuse</span>';
    }
    if (normalized == 'en_attente') {
        return '<span class="badge bg-warning text-dark">en attente</span>';
    }
    return '<span class="badge bg-secondary">' + esc(status || 'inconnu') + '</span>';
}

async function chargerRendezVous() {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'connexion.php';
        return;
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/mes_devis', {
        method: 'GET',
        headers: { 'Token': token }
    });

    const container = document.getElementById('rdvContainer');

    if (!response.ok) {
        container.innerHTML = '<div class="alert alert-danger">Impossible de charger vos rendez-vous.</div>';
        return;
    }

    const data = await response.json();
    const liste = Array.isArray(data.devis) ? data.devis : [];

    if (liste.length === 0) {
        container.innerHTML = '<div class="alert alert-info">Aucun rendez-vous service pour le moment.</div>';
        return;
    }

    let html = '';
    html += '<div class="table-responsive">';
    html += '<table class="table table-hover table-bordered align-middle">';
    html += '<thead class="table-light">';
    html += '<tr>';
    html += '<th data-i18n>Service</th>';
    html += '<th data-i18n>Prestataire</th>';
    html += '<th data-i18n>Debut</th>';
    html += '<th data-i18n>Fin</th>';
    html += '<th data-i18n>Intervention</th>';
    html += '<th data-i18n>Action</th>';
    html += '</tr>';
    html += '</thead><tbody>';

    liste.forEach(function(rdv) {
        html += '<tr>';
        html += '<td>' + esc(rdv.nom_service || '—') + '</td>';
        html += '<td>' + esc(rdv.nom_prestataire || '—') + '</td>';
        html += '<td>' + esc(formatDate(rdv.date_debut)) + '</td>';
        html += '<td>' + esc(formatDate(rdv.date_fin)) + '</td>';
        html += '<td>' + badgeStatut(rdv.status) + '</td>';
        html += '<td><a class="btn btn-sm btn-outline-primary" href="detail_rendez_vous.php?id=' + Number(rdv.id || 0) + '" data-i18n>Voir le detail</a></td>';
        html += '</tr>';
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;
    await chargerRendezVous();
}

init();
</script>
</body>
</html>
