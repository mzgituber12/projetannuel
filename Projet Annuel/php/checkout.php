<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
<?php include 'includes/header.php'; ?>

<h1 class="mt-5 mb-4" data-i18n>Checkout</h1>
<div class="container" style="max-width:600px;">
    <div class="card shadow">
        <div class="card-body">
            <div id="items"></div>
            <div id="total" class="h5 mt-3 pt-3 border-top"><span data-i18n>Total</span>: <span id="totalAmount">0.00 EUR</span></div>

            <div class="d-flex gap-2 flex-wrap mt-4">
                <button id="payTransfer" class="btn btn-success" data-i18n>Payer par virement</button>
                <button id="payStripe" class="btn btn-primary" data-i18n>Payer par carte</button>
                <a class="btn btn-outline-secondary" href="panier.php" data-i18n>Retour panier</a>
            </div>

            <div id="message"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function esc(v) {
    return String(v ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

const API = window.API_BASE || 'http://localhost:9000';
let currentTotal = 0;

function afficherMessage(text, isError) {
    const m = document.getElementById('message');
    if (!text) {
        m.innerHTML = '';
        return;
    }
    const alertClass = isError ? 'alert-danger' : 'alert-success';
    m.innerHTML = '<div class="alert ' + alertClass + ' mt-3" style="white-space:pre-wrap;">' + esc(text) + '</div>';
}

async function chargerPanier() {
    const token = localStorage.getItem('token') || '';
    const response = await fetch(API + '/panier_articles', {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (!response.ok) {
        afficherMessage(await response.text() || 'Impossible de charger le panier.', true);
        return false;
    }

    const data = await response.json();
    const node = document.getElementById('items');

    if (data.message || !Array.isArray(data.article) || data.article.length === 0) {
        node.innerHTML = '<p data-i18n>Votre panier est vide.</p>';
        document.getElementById('payTransfer').disabled = true;
        document.getElementById('payStripe').disabled = true;
        currentTotal = 0;
        document.getElementById('totalAmount').textContent = '0.00 EUR';
        return false;
    }

    let html = '';
    let total = 0;
    data.article.forEach((a) => {
        total += Number(a.prix) || 0;
        html += '<div class="d-flex justify-content-between align-items-center py-2 border-bottom"><span>' + esc(a.titre) + '</span><strong>' + esc(a.prix) + ' EUR</strong></div>';
    });

    currentTotal = total;
    node.innerHTML = html;
    document.getElementById('totalAmount').textContent = total.toFixed(2) + ' EUR';
    return true;
}

async function creerCommande(paymentMethod) {
    const token = localStorage.getItem('token') || '';
    const response = await fetch(API + '/create-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Token': token
        },
        body: JSON.stringify({ payment_method: paymentMethod })
    });

    const contenue = await response.text();
    let data = null;
    try { data = JSON.parse(contenue); } catch { data = { message: contenue }; }

    if (!response.ok) {
        afficherMessage(data.message || 'Erreur creation commande.', true);
        return;
    }

    const idCommande = data.id_commande ?? data.order_id;
    const montantTotal = data.montant_total ?? data.total ?? currentTotal;
    const infosVirement = data.virement || data.transfer || {};

    if (paymentMethod === 'transfer') {
        const ref = infosVirement.reference ? infosVirement.reference : ('CMD-' + idCommande);
        const iban = infosVirement.iban ? infosVirement.iban : 'IBAN indisponible';
        afficherMessage('Commande creee.\nReference: ' + ref + '\nIBAN: ' + iban + '\nMontant: ' + Number(montantTotal).toFixed(2) + ' EUR', false);
        window.location.href = 'invoice.php?id=' + encodeURIComponent(idCommande);
        return;
    }

    data.id_commande = idCommande;
    return data;
}

async function creerSessionPaiement(orderId) {
    const token = localStorage.getItem('token') || '';
    const response = await fetch(API + '/create-checkout-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Token': token
        },
        body: JSON.stringify({ order_id: orderId })
    });

    const contenue = await response.text();
    let data = null;
    try { data = JSON.parse(contenue); } catch { data = { message: contenue }; }

    if (!response.ok) {
        afficherMessage(data.message || 'Erreur session Stripe.', true);
        return;
    }

    if (!data.url) {
        afficherMessage('URL Stripe manquante.', true);
        return;
    }

    window.location.href = data.url;
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) {
        return;
    }
    
    await chargerPanier();

    document.getElementById('payTransfer').addEventListener('click', async function () {
        afficherMessage('', false);
        await creerCommande('transfer');
    });

    document.getElementById('payStripe').addEventListener('click', async function () {
        afficherMessage('', false);
        const order = await creerCommande('stripe');
        const idCommande = order ? (order.id_commande ?? order.order_id) : null;
        if (!idCommande) {
            return;
        }

        await creerSessionPaiement(idCommande);
    });
}

init();
</script>
</body>
</html>
