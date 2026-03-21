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
        <button id="payStripe" class="btn btn-stripe">Payer par carte (simule)</button>
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

function showMessage(text, isError) {
    const m = document.getElementById('message');
    if (!text) {
        m.innerHTML = '';
        return;
    }
    m.innerHTML = '<div class="message ' + (isError ? 'error' : '') + '">' + esc(text) + '</div>';
}

async function loadCart() {
    const token = localStorage.getItem('token') || '';
    const response = await fetch(API + '/panier_articles', {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (!response.ok) {
        showMessage(await response.text() || 'Impossible de charger le panier.', true);
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
        html += '<div class="checkout-row"><span>' + esc(a.nom) + '</span><strong>' + esc(a.prix) + ' EUR</strong></div>';
    });

    currentTotal = total;
    node.innerHTML = html;
    document.getElementById('total').textContent = 'Total: ' + total.toFixed(2) + ' EUR';
    return true;
}

async function createOrder(paymentMethod) {
    const token = localStorage.getItem('token') || '';
    const response = await fetch(API + '/create-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Token': token
        },
        body: JSON.stringify({ payment_method: paymentMethod })
    });

    const raw = await response.text();
    let data = null;
    try { data = JSON.parse(raw); } catch { data = { message: raw }; }

    if (!response.ok) {
        showMessage(data.message || 'Erreur creation commande.', true);
        return;
    }

    if (paymentMethod === 'transfer') {
        const ref = (data.transfer && data.transfer.reference) ? data.transfer.reference : ('CMD-' + data.order_id);
        const iban = (data.transfer && data.transfer.iban) ? data.transfer.iban : 'IBAN indisponible';
        showMessage('Commande creee.\nReference: ' + ref + '\nIBAN: ' + iban + '\nMontant: ' + Number(data.total || currentTotal).toFixed(2) + ' EUR', false);
        window.location.href = 'invoice.php?id=' + encodeURIComponent(data.order_id);
        return;
    }

    window.location.href = 'success.php?order_id=' + encodeURIComponent(data.order_id);
}

async function createCheckoutSession() {
    const token = localStorage.getItem('token') || '';
    const response = await fetch(API + '/create-checkout-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Token': token
        }
    });

    const raw = await response.text();
    let data = null;
    try { data = JSON.parse(raw); } catch { data = { message: raw }; }

    if (!response.ok) {
        showMessage(data.message || 'Erreur session Stripe.', true);
        return;
    }

    await createOrder('stripe');
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) {
        return;
    }

    await loadCart();

    document.getElementById('payTransfer').addEventListener('click', async function () {
        showMessage('', false);
        await createOrder('transfer');
    });

    document.getElementById('payStripe').addEventListener('click', async function () {
        showMessage('', false);
        await createCheckoutSession();
    });
}

init();
</script>
</body>
</html>
