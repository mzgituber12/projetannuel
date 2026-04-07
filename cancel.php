<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Paiement annule</title>
    <style>
        .box {
            max-width: 760px;
            margin: 2rem auto;
            padding: 1.2rem;
            border-radius: 12px;
            border: 1px solid #fecaca;
            background: #fef2f2;
        }

        .actions { margin-top: 1rem; display: flex; gap: 0.7rem; flex-wrap: wrap; }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            text-decoration: none;
            font-weight: 700;
            color: #fff;
            background: #2563eb;
        }

        .btn.secondary { background: #6b7280; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="box">
    <h1>Paiement annule</h1>
    <p id="status">Votre paiement a ete annule. Aucun debit n'a ete effectue.</p>
    <div class="actions">
        <a class="btn" href="checkout.php">Revenir au checkout</a>
        <a class="btn secondary" href="panier.php">Retour panier</a>
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
    if (!orderId) {
        return;
    }

    await fetch(API + '/webhook', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId, status: 'canceled' })
    });
}

init();
</script>
</body>
</html>
