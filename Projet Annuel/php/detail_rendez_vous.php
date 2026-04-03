<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detail rendez-vous</title>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-4 mb-5">
    <a href="rendez_vous.php" class="btn btn-outline-secondary btn-sm mb-3">Retour a mes rendez-vous</a>
    <h1>Detail du rendez-vous</h1>
    <div id="detailContainer">
        <p>Chargement en cours...</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
const rdvId = <?= intval($_GET['id'] ?? 0) ?>;

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
    if (normalized === 'accepté' || normalized === 'accepte') {
        return '<span class="badge bg-success">accepte</span>';
    }
    if (normalized === 'refusé' || normalized === 'refuse') {
        return '<span class="badge bg-danger">refuse</span>';
    }
    if (normalized === 'en_attente') {
        return '<span class="badge bg-warning text-dark">en attente</span>';
    }
    return '<span class="badge bg-secondary">' + esc(status || 'inconnu') + '</span>';
}

async function chargerDetailRendezVous() {
    const container = document.getElementById('detailContainer');

    if (!rdvId || rdvId <= 0) {
        container.innerHTML = '<div class="alert alert-danger">Identifiant de rendez-vous invalide.</div>';
        return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'connexion.php';
        return;
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const detailResp = await fetch(base + '/devis/' + encodeURIComponent(rdvId), {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (!detailResp.ok) {
        container.innerHTML = '<div class="alert alert-danger">Rendez-vous introuvable ou acces refuse.</div>';
        return;
    }

    const d = await detailResp.json();

    let serviceDescription = 'Description non disponible.';
    let serviceImage = '';
    let serviceTarif = d.tarif ? Number(d.tarif).toFixed(2) + ' EUR' : 'Non renseigne';

    const serviceResp = await fetch(base + '/services', {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (serviceResp.ok) {
        const payload = await serviceResp.json();
        const services = Array.isArray(payload.service) ? payload.service : [];
        const service = services.find(function(s) {
            return String(s.nom || '').toLowerCase() === String(d.nom_service || '').toLowerCase();
        });
        if (service) {
            if (service.description) serviceDescription = String(service.description);
            if (service.image) serviceImage = String(service.image);
            if (Number(service.tarif || 0) > 0) {
                serviceTarif = Number(service.tarif).toFixed(2) + ' EUR';
            }
        }
    }

    const imageHtml = serviceImage
        ? '<img src="' + esc(serviceImage) + '" alt="Image du service" class="img-fluid rounded mb-3" style="max-height:260px; object-fit:cover;">'
        : '<p class="text-muted mb-3">Aucune image de service.</p>';

    container.innerHTML = '' +
        '<div class="row g-4">' +
            '<div class="col-lg-6">' +
                '<div class="card h-100 shadow-sm">' +
                    '<div class="card-body">' +
                        '<h4 class="card-title mb-3">Service inscrit</h4>' +
                        imageHtml +
                        '<p class="mb-2"><strong>Nom :</strong> ' + esc(d.nom_service || '—') + '</p>' +
                        '<p class="mb-2"><strong>Description :</strong> ' + esc(serviceDescription) + '</p>' +
                        '<p class="mb-0"><strong>Tarif :</strong> ' + esc(serviceTarif) + '</p>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-lg-6">' +
                '<div class="card h-100 shadow-sm">' +
                    '<div class="card-body">' +
                        '<h4 class="card-title mb-3">Rendez-vous et intervention</h4>' +
                        '<p class="mb-2"><strong>Prestataire :</strong> ' + esc(d.nom_prestataire || '—') + '</p>' +
                        '<p class="mb-2"><strong>Debut :</strong> ' + esc(formatDate(d.date_debut)) + '</p>' +
                        '<p class="mb-2"><strong>Fin :</strong> ' + esc(formatDate(d.date_fin)) + '</p>' +
                        '<p class="mb-2"><strong>Statut intervention :</strong> ' + badgeStatut(d.status) + '</p>' +
                        '<p class="mb-0"><strong>Montant intervention :</strong> ' + esc((Number(d.tarif || 0) > 0) ? Number(d.tarif).toFixed(2) + ' EUR' : 'Non renseigne') + '</p>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;
    await chargerDetailRendezVous();
}

init();
</script>
</body>
</html>
