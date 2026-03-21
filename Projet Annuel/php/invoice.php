<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Facture</title>
    <style>
        .invoice-shell {
            max-width: 900px;
            margin: 1rem auto;
            padding: 1.2rem;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,.1);
            background: #ffffff;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem 1rem;
            margin-bottom: 1rem;
        }

        .line {
            display: flex;
            justify-content: space-between;
            padding: 0.55rem 0;
            border-bottom: 1px dashed #d1d5db;
            gap: 1rem;
        }

        .total {
            margin-top: 1rem;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

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

        @media (max-width: 700px) {
            .meta { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<h1>Facture</h1>
<div class="invoice-shell">
    <div id="invoiceContent">Chargement de la facture...</div>
    <div class="actions">
        <a class="btn" href="boutique.php">Retour boutique</a>
        <a class="btn secondary" href="panier.php">Retour panier</a>
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

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) {
        return;
    }

    const id = new URLSearchParams(window.location.search).get('id');
    const content = document.getElementById('invoiceContent');
    if (!id) {
        content.textContent = 'Facture introuvable.';
        return;
    }

    const base = window.API_BASE || 'http://localhost:9000';
    const response = await fetch(base + '/invoice/' + encodeURIComponent(id), {
        method: 'GET',
        headers: { 'Token': token || '' }
    });

    if (!response.ok) {
        const text = await response.text();
        content.textContent = text || 'Impossible de charger la facture.';
        return;
    }

    const data = await response.json();
    const items = Array.isArray(data.items) ? data.items : [];

    let html = '';
    html += '<div class="meta">';
    html += '<div><strong>Commande:</strong> #' + esc(data.order_id) + '</div>';
    html += '<div><strong>Date:</strong> ' + esc(data.date) + '</div>';
    html += '<div><strong>Mode de paiement:</strong> ' + esc(data.mode || '-') + '</div>';
    html += '<div><strong>Statut:</strong> ' + esc(data.status || '-') + '</div>';
    html += '</div>';

    if (items.length === 0) {
        html += '<p>Aucun article associe.</p>';
    } else {
        items.forEach((item) => {
            html += '<div class="line"><span>' + esc(item.nom) + '</span><strong>' + esc(item.prix) + ' EUR</strong></div>';
        });
    }

    html += '<div class="total">Total: ' + Number(data.total || 0).toFixed(2) + ' EUR</div>';
    content.innerHTML = html;
}

init();
</script>
</body>
</html>
