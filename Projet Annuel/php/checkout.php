<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <style>
        .checkout-shell {
            max-width: 900px;
            margin: 1rem auto;
            padding: 1.2rem;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,.1);
            background: #f8fafc;
        }

        .checkout-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin: 0.4rem 0;
            padding-bottom: 0.4rem;
            border-bottom: 1px dashed #d1d5db;
        }

        .checkout-total {
            margin-top: 1rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827;
        }

        .checkout-actions {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 0.65rem 1rem;
            cursor: pointer;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-transfer { background: #0d9488; }
        .btn-transfer:hover { background: #0f766e; }

        .btn-stripe { background: #2563eb; }
        .btn-stripe:hover { background: #1d4ed8; }

        .btn-back { background: #6b7280; }
        .btn-back:hover { background: #4b5563; }

        .message {
            margin-top: 1rem;
            padding: 0.7rem;
            border-radius: 8px;
            background: #ecfeff;
            border: 1px solid #99f6e4;
            color: #134e4a;
            white-space: pre-wrap;
        }

        .message.error {
            background: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<h1>Checkout</h1>
<div class="checkout-shell">
    <div id="items"></div>
    <div id="total" class="checkout-total">Total: 0.00 EUR</div>

    <div class="checkout-actions">
        <button id="payTransfer" class="btn btn-transfer">Payer par virement</button>
        <button id="payStripe" class="btn btn-stripe">Payer par carte</button>
        <a class="btn btn-back" href="panier.php">Retour panier</a>
    </div>

    <div id="message"></div>
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
    m.innerHTML = '<div class="message ' + (isError ? 'error' : '') + '">' + esc(text) + '</div>';
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
        node.innerHTML = '<p>Votre panier est vide.</p>';
        document.getElementById('payTransfer').disabled = true;
        document.getElementById('payStripe').disabled = true;
        currentTotal = 0;
        document.getElementById('total').textContent = 'Total: 0.00 EUR';
        return false;
    }

    let html = '';
    let total = 0;
    data.article.forEach((a) => {
        total += Number(a.prix) || 0;
        html += '<div class="checkout-row"><span>' + esc(a.titre) + '</span><strong>' + esc(a.prix) + ' EUR</strong></div>';
    });

    currentTotal = total;
    node.innerHTML = html;
    document.getElementById('total').textContent = 'Total: ' + total.toFixed(2) + ' EUR';
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
