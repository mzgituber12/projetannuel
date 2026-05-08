<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Mon panier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="police.css">
    <style>
        .cart-image-placeholder {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff 0%, #dbeafe 100%);
            color: #6b7280;
            font-weight: 700;
            overflow: hidden;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php' ?>

<div class="container-fluid mt-4">
    <h1 class="mb-4" data-i18n>Mon panier</h1>

    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h5 id="cartTotal" class="text-primary fs-5"><i class="bi bi-bag"></i> <span data-i18n>Total</span>: 0 €</h5>
        </div>
        <div class="col-md-4 text-end">
            <a class="btn btn-success" href="checkout.php"><i class="bi bi-credit-card"></i> <span data-i18n>Passer au paiement</span></a>
        </div>
    </div>

    <div id="cartMessage"></div>
    <div id="cartList" class="row g-3"></div>
</div>

<?php include 'includes/footer.php';?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
function resolveImageUrl(image) {
    const raw = String(image ?? "").trim();
    if (!raw) return "";
    if (raw.startsWith("http://") || raw.startsWith("https://") || raw.startsWith("/")) {
        return raw;
    }
    return `upload/${encodeURIComponent(raw)}`;
}

function renderCartImage(image, altText) {
    const imageUrl = resolveImageUrl(image);
    if (!imageUrl) return "Produit";
    return `<img src="${imageUrl}" alt="${String(altText)}" style="width:100%;height:100%;object-fit:cover;">`;
}

async function basculerPanier(articleId) {
    const token = localStorage.getItem('token') || '';
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + '/panier_article_toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Token': token
        },
        body: JSON.stringify({ id_article: articleId })
    });

    const raw = await response.text();
    let data = null;
    try {
        data = JSON.parse(raw);
    } catch {
        afficherMessagePanier(raw || 'Erreur serveur.', true);
        return;
    }

    if (!response.ok) {
        afficherMessagePanier(data.message || 'Erreur panier.', true);
        return;
    }

    afficherMessagePanier(data.message || 'Panier mis à jour.', false);
    loadPanier();
}

function afficherMessagePanier(text, isError) {
    const node = document.getElementById('cartMessage');
    if (!text) {
        node.innerHTML = '';
        return;
    }

    if (isError) {
        node.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle"></i> ' + String(text) + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    } else {
        node.innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle"></i> ' + String(text) + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}

async function loadPanier() {
    const token = localStorage.getItem('token') || '';
    const base = (window.API_BASE || 'http://localhost:9000');
    const list = document.getElementById('cartList');
    const totalNode = document.getElementById('cartTotal');

    const response = await fetch(base + '/panier_articles', {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (!response.ok) {
        const text = await response.text();
        afficherMessagePanier(text || 'Erreur de lecture du panier.', true);
        list.innerHTML = '';
        totalNode.innerHTML = '<i class="bi bi-bag"></i> <span data-i18n>Total</span>: 0 €';
        return;
    }

    const data = await response.json();

    if (data.message) {
        afficherMessagePanier(data.message, false);
        list.innerHTML = '';
        totalNode.innerHTML = '<i class="bi bi-bag"></i> <span data-i18n>Total</span>: 0 €';
        return;
    }

    afficherMessagePanier('', false);

    let html = '';
    let total = 0;
    data.article.forEach(a => {
        total += Number(a.prix) || 0;
        const desc = (a.description || '').length > 40
            ? (a.description || '').slice(0, 40) + '...'
            : (a.description || '');

        html += `
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card h-100 shadow-sm">
                    <div class="cart-image-placeholder">
                        ${renderCartImage(a.image, `Image de ${a.titre || "cet article"}`)}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${String(a.titre)}</h5>
                        <p class="card-text text-muted small flex-grow-1">${String(desc)}</p>
                        <p class="card-text text-primary fw-bold fs-5">${String(a.prix)} €</p>
                        <div class="d-grid gap-2 d-sm-flex">
                            <a class="btn btn-sm btn-info flex-grow-1" href="article_detail.php?id=${encodeURIComponent(a.id)}"><i class="bi bi-eye"></i> <span data-i18n>Détail</span></a>
                            <button class="btn btn-sm btn-danger" onclick="basculerPanier(${Number(a.id)})"><i class="bi bi-trash"></i> <span data-i18n>Retirer</span></button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    list.innerHTML = html;
    totalNode.innerHTML = '<i class="bi bi-bag"></i> <span data-i18n>Total</span>: ' + total.toFixed(2) + ' €';
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;
    loadPanier();
}

init();
</script>
</body>
</html>

