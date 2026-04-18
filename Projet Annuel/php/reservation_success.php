<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n>Réservation — paiement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>

<div class="container mt-5 mb-5" style="max-width: 760px;">
    <div class="alert alert-info border-0 shadow-sm" role="alert">
        <h1 class="h4 mb-3" data-i18n>Finalisation de la réservation</h1>
        <p id="status" class="text-muted mb-0" data-i18n>Vérification du paiement en cours...</p>
    </div>
    <a class="btn btn-outline-primary" href="catalogue.php" data-i18n>Retour au catalogue</a>
</div>

<?php include 'includes/footer.php'; ?>

<script>
const API = window.API_BASE || 'http://localhost:9000';

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const sessionId = params.get('session_id') || '';
    const statusNode = document.getElementById('status');

    if (!sessionId) {
        statusNode.textContent = 'Session de paiement introuvable.';
        return;
    }

    const response = await fetch(API + '/confirmation_reservation_stripe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Token': token || ''
        },
        body: JSON.stringify({ session_id: sessionId })
    });

    const text = await response.text();
    let data = null;
    try { data = JSON.parse(text); } catch { data = { message: text }; }

    if (!response.ok) {
        statusNode.textContent = data.message || text || 'Impossible de confirmer la réservation.';
        return;
    }

    if (data.reservation_done) {
        statusNode.textContent = data.message || 'Réservation confirmée.';
        await fetch('ajouter_session_state.php', { method: 'POST' });
        setTimeout(() => {
            window.location.href = 'catalogue.php?message=' + encodeURIComponent(data.message || 'Réservation confirmée');
        }, 1200);
        return;
    }

    statusNode.textContent = data.message || 'En attente de confirmation du paiement. Vous pouvez recharger la page.';
}

init();
</script>
</body>
</html>
