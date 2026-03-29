<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Facture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container mt-5 mb-5" style="max-width: 900px;">
    <h1 class="mb-4">Facture</h1>
    <div class="card">
        <div class="card-body">
            <div id="invoiceContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement de la facture...</p>
                </div>
            </div>
        </div>
        <div class="card-footer d-grid gap-2 d-sm-flex justify-content-sm-end">
            <a class="btn btn-primary" href="boutique.php"><i class="bi bi-shop"></i> Retour boutique</a>
            <a class="btn btn-secondary" href="panier.php"><i class="bi bi-cart"></i> Retour panier</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

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
