<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Paiement reussi</title>
    <style>
        .box {
            max-width: 760px;
            margin: 2rem auto;
            padding: 1.2rem;
            border-radius: 12px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
        }

        .actions { margin-top: 1rem; display: flex; gap: 0.7rem; flex-wrap: wrap; }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            text-decoration: none;
            font-weight: 700;
            color: #fff;
            background: #16a34a;
        }

        .btn.secondary { background: #2563eb; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="box">
    <h1>Paiement confirme</h1>
    <p id="status">Validation du paiement en cours...</p>
    <div class="actions">
        <a id="invoiceLink" class="btn secondary" href="boutique.php">Voir la facture</a>
        <a class="btn" href="boutique.php">Retour boutique</a>
    </div>
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
        statusNode.textContent = 'Paiement enregistre. Votre commande est finalisee.';
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
