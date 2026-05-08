<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Mes devis</title>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php' ?>

<div class="container mt-4">
    <h1 data-i18n>Mes devis</h1>

    <?php if (isset($_GET['message'])) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
    <?php endif; ?>

    <div id="devisContainer">
        <p data-i18n>Chargement en cours…</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
async function chargerDevis() {
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

    const container = document.getElementById('devisContainer');

    if (!response.ok) {
        container.innerHTML = '<p class="text-danger">Impossible de charger vos devis.</p>';
        return;
    }

    const data = await response.json();
    const liste = data.devis;

    if (!liste || liste.length === 0) {
        container.innerHTML = '<p class="text-muted">Vous n\'avez aucun devis pour le moment.</p>';
        return;
    }

    const statutBadge = (statut) => {
        const map = {
            'en_attente': 'warning',
            'accepté': 'success',
            'refusé': 'danger'
        };
        const couleur = map[statut] || 'secondary';
        return `<span class="badge bg-${couleur}">${statut}</span>`;
    };

    let html = `
        <table class="table table-hover table-bordered">
            <thead class="table-light">
                <tr>
                    <th data-i18n>#</th>
                    <th data-i18n>Service</th>
                    <th data-i18n>Prestataire</th>
                    <th data-i18n>Tarif estimé</th>
                    <th data-i18n>Statut</th>
                    <th data-i18n>Détail</th>
                </tr>
            </thead>
            <tbody>`;

    liste.forEach(d => {
        const tarif = d.tarif > 0 ? Number(d.tarif).toFixed(2) + ' €' : '—';
        html += `
            <tr>
                <td>${d.id}</td>
                <td>${d.nom_service || '—'}</td>
                <td>${d.nom_prestataire || '—'}</td>
                <td>${tarif}</td>
                <td>${statutBadge(d.status)}</td>
                <td><a href="devis_detail.php?id=${d.id}" class="btn btn-sm btn-outline-primary" data-i18n>Voir</a></td>
            </tr>`;
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}

chargerDevis();
</script>
</body>
</html>
