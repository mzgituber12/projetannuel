<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mon panier</title>
    <style>
        .cart-shell {
            max-width: 1100px;
            margin: 1rem auto;
            padding: 1rem;
            border: 1px solid rgba(0,0,0,.1);
            border-radius: 12px;
            background: #f4f8ff;
        }

        .cart-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .cart-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .cart-total {
            font-weight: 700;
            color: #1f2937;
        }

        .cart-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .cart-img {
            height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff 0%, #dbeafe 100%);
            color: #6b7280;
            font-weight: 700;
        }

        .cart-body {
            padding: 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .cart-title {
            margin: 0;
            font-weight: 700;
            color: #111827;
        }

        .cart-desc {
            margin: 0;
            color: #374151;
            line-height: 1.35;
        }

        .cart-price {
            color: #1d4ed8;
            font-weight: 700;
        }

        .cart-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.2rem;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-detail {
            background: #2563eb;
            color: #fff;
        }

        .btn-detail:hover {
            background: #1d4ed8;
        }

        .btn-remove {
            background: #dc2626;
            color: #fff;
        }

        .btn-remove:hover {
            background: #b91c1c;
        }

        .btn-checkout {
            background: #059669;
            color: #fff;
        }

        .btn-checkout:hover {
            background: #047857;
        }

        .message {
            margin: 1rem 0;
            padding: 0.7rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .message.err {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        @media (max-width: 1000px) {
            .cart-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 640px) {
            .cart-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php' ?>

<h1>Mon panier</h1>
<div class="cart-shell">
    <div class="cart-top">
        <div id="cartTotal" class="cart-total">Total: 0 €</div>
        <a class="btn btn-checkout" href="checkout.php">Passer au paiement</a>
    </div>
    <div id="cartMessage"></div>
    <div id="cartList" class="cart-grid"></div>
</div>

<?php include 'includes/footer.php';?>

<script>
function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

async function toggleCart(articleId) {
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
        showMessage(raw || 'Erreur serveur.', true);
        return;
    }

    if (!response.ok) {
        showMessage(data.message || 'Erreur panier.', true);
        return;
    }

    showMessage(data.message || 'Panier mis à jour.', false);
    loadPanier();
}

function showMessage(text, isError) {
    const node = document.getElementById('cartMessage');
    if (!text) {
        node.innerHTML = '';
        return;
    }

    if (isError) {
        node.innerHTML = '<div class="message err">' + escapeHtml(text) + '</div>';
    } else {
        node.innerHTML = '<div class="message">' + escapeHtml(text) + '</div>';
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
        showMessage(text || 'Erreur de lecture du panier.', true);
        list.innerHTML = '';
        totalNode.textContent = 'Total: 0 €';
        return;
    }

    const data = await response.json();

    if (data.message) {
        showMessage(data.message, false);
        list.innerHTML = '';
        totalNode.textContent = 'Total: 0 €';
        return;
    }

    showMessage('', false);

    let html = '';
    let total = 0;
    data.article.forEach(a => {
        total += Number(a.prix) || 0;
        const desc = (a.description || '').length > 100
            ? (a.description || '').slice(0, 100) + '...'
            : (a.description || '');

        html += `
            <article class="cart-card">
                <div class="cart-img">Produit</div>
                <div class="cart-body">
                    <h3 class="cart-title">${escapeHtml(a.nom)}</h3>
                    <p class="cart-desc">${escapeHtml(desc)}</p>
                    <span class="cart-price">${escapeHtml(a.prix)} €</span>
                    <div class="cart-actions">
                        <a class="btn btn-detail" href="article_detail.php?id=${encodeURIComponent(a.id)}">Voir détail</a>
                        <button class="btn btn-remove" onclick="toggleCart(${Number(a.id)})">Retirer du panier</button>
                    </div>
                </div>
            </article>
        `;
    });

    list.innerHTML = html;
    totalNode.textContent = 'Total: ' + total.toFixed(2) + ' €';
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
