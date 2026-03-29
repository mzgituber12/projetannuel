<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Paiement reussi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container mt-5 mb-5" style="max-width: 760px;">
    <div class="alert alert-success border-success-subtle" role="alert">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
            <h1 class="ms-3 mb-0">Paiement confirmé</h1>
        </div>
        <p id="status" class="text-muted mb-3">Validation du paiement en cours...</p>
        <div class="d-flex gap-2 flex-wrap">
            <a id="invoiceLink" class="btn btn-primary" href="boutique.php"><i class="bi bi-file-earmark-pdf"></i> Voir la facture</a>
            <a class="btn btn-success" href="boutique.php"><i class="bi bi-shop"></i> Retour boutique</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
const API = window.API_BASE || 'http://localhost:9000';

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const orderId = Number(params.get('order_id') || 0);
    const statusNode = document.getElementById('status');
    const invoiceLink = document.getElementById('invoiceLink');

    if (!orderId) {
        statusNode.textContent = 'Commande introuvable.';
        invoiceLink.href = 'boutique.php';
        return;
    }

    invoiceLink.href = 'invoice.php?id=' + encodeURIComponent(orderId);

    const response = await fetch(API + '/invoice/' + encodeURIComponent(orderId), {
        method: 'GET',
        headers: { 'Token': token || '' }
    });

    if (!response.ok) {
        statusNode.textContent = 'Paiement en attente de confirmation. Recharge la page dans quelques secondes.';
        return;
    }

    const data = await response.json();
    const status = String(data.status || '').toLowerCase();

    if (status === 'paid') {
        statusNode.textContent = 'Paiement enregistré. Votre commande est finalisée.';
        return;
    }

    if (status === 'pending' || status === 'pending_stripe') {
        statusNode.textContent = 'Paiement en cours de confirmation par Stripe. Recharge la page dans quelques secondes.';
        return;
    }

    statusNode.textContent = 'Statut actuel: ' + status + '. Vous pouvez ouvrir la facture.';
}

init();
</script>
</body>
</html>
