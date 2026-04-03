<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Détail du devis</title>
</head>
<body>

<?php include 'includes/header.php' ?>

<div class="container mt-4">
    <a href="devis.php" class="btn btn-outline-secondary mb-3" data-i18n>← Retour à mes devis</a>

    <div id="devisDetail">
        <p data-i18n>Chargement en cours…</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
const devisId = <?= intval($_GET['id'] ?? 0) ?>;

async function changerStatut(statut) {
    const token = localStorage.getItem('token');
    const base = (window.API_BASE || 'http://localhost:9000');

    const btn = document.getElementById('btn-' + statut);
    if (btn) btn.disabled = true;

    const response = await fetch(`${base}/devis/${devisId}/statut`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'Token': token },
        body: JSON.stringify({ status: statut })
    });

    if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        alert(data.message || 'Erreur lors de la mise à jour du devis.');
        if (btn) btn.disabled = false;
        return;
    }

    chargerDetail();
}

async function chargerDetail() {
    if (!devisId) {
        document.getElementById('devisDetail').innerHTML = '<p class="text-danger">Identifiant de devis invalide.</p>';
        return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'connexion.php';
        return;
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(`${base}/devis/${devisId}`, {
        method: 'GET',
        headers: { 'Token': token }
    });

    const container = document.getElementById('devisDetail');

    if (!response.ok) {
        container.innerHTML = '<p class="text-danger">Devis introuvable ou accès refusé.</p>';
        return;
    }

    const d = await response.json();

    const statutBadge = (statut) => {
        const map = { 'en_attente': 'warning', 'accepté': 'success', 'refusé': 'danger' };
        const couleur = map[statut] || 'secondary';
        return `<span class="badge bg-${couleur} fs-6">${statut}</span>`;
    };

    const tarif     = d.tarif > 0  ? Number(d.tarif).toFixed(2) + ' €'           : 'Non renseigné';

    let actionsHtml = '';
    if (d.can_modify) {
        actionsHtml = `
            <div class="d-flex gap-2 mt-4">
                <button id="btn-accepté" class="btn btn-success" onclick="changerStatut('accepté')">
                    <i class="bi bi-check-circle me-1"></i><span data-i18n>Accepter le devis</span>
                </button>
                <button id="btn-refusé" class="btn btn-danger" onclick="changerStatut('refusé')">
                    <i class="bi bi-x-circle me-1"></i><span data-i18n>Refuser le devis</span>
                </button>
            </div>`;
    }

    let infoHtml = '';
    if (d.status === 'en_attente' && !d.can_modify) {
        infoHtml = `
            <div class="alert alert-info mt-4">
                <strong>En attente de réponse.</strong> Le prestataire examinera votre demande et pourra ajuster le tarif proposé.
            </div>`;
    } else if (d.status === 'accepté') {
        infoHtml = `<div class="alert alert-success mt-4"><strong>Devis accepté.</strong> Le tarif négocié est prêt pour une future réservation.</div>`;
    } else if (d.status === 'refusé') {
        infoHtml = `<div class="alert alert-danger mt-4"><strong>Devis refusé.</strong> Cette estimation n'a pas été validée.</div>`;
    }

    container.innerHTML = `
        <h2>Devis #${d.id}</h2>
        <div class="card mt-3">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3" data-i18n>Service</dt>
                    <dd class="col-sm-9">${d.nom_service || '—'}</dd>

                    <dt class="col-sm-3" data-i18n>Prestataire</dt>
                    <dd class="col-sm-9">${d.nom_prestataire || '—'}</dd>

                    <dt class="col-sm-3" data-i18n>Tarif estimé</dt>
                    <dd class="col-sm-9">${tarif}</dd>

                    <dt class="col-sm-3" data-i18n>Statut</dt>
                    <dd class="col-sm-9">${statutBadge(d.status)}</dd>
                </dl>
            </div>
        </div>
        ${actionsHtml}
        ${infoHtml}`;
}

chargerDetail();
</script>
</body>
</html>
