<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Détail produit</title>
    <style>
        .product-shell {
            max-width: 900px;
            width: min(900px, calc(100% - 2rem));
            margin: 1.2rem auto;
            padding: 1.2rem;
            border: 1px solid rgba(0,0,0,.1);
            border-radius: 12px;
            background: #f8fbff;
            box-sizing: border-box;
        }

        .product-image {
            width: 100%;
            height: 360px;
            border-radius: 10px;
            border: 1px solid #dbe3ff;
            background: linear-gradient(135deg, #eef2ff 0%, #dbeafe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .product-title {
            margin: 1rem 0 0.5rem 0;
            font-size: 1.8rem;
            font-weight: 800;
            color: #111827;
        }

        .product-price {
            color: #1d4ed8;
            font-weight: 800;
            font-size: 1.2rem;
        }

        .product-content {
            margin-top: 1rem;
            color: #1f2937;
            line-height: 1.7;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 0.65rem 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-cart {
            background: #16a34a;
            color: #fff;
        }

        .btn-cart:hover {
            background: #15803d;
        }

        .btn-back {
            background: #2563eb;
            color: #fff;
        }

        .btn-back:hover {
            background: #1d4ed8;
        }

        .message {
            margin-top: 1rem;
            padding: 0.65rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .message.ok {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
        }

        .message.err {
            color: #b91c1c;
            background: #fee2e2;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php' ?>

<h1>Détail produit</h1>
<div class="product-shell" id="productDetail">Chargement...</div>

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

function resolveImageUrl(image) {
    const raw = String(image ?? "").trim();
    if (!raw) return "";
    if (raw.startsWith("http://") || raw.startsWith("https://") || raw.startsWith("/")) {
        return raw;
    }
    return `upload/${encodeURIComponent(raw)}`;
}

function renderProductImage(image, altText) {
    const imageUrl = resolveImageUrl(image);
    if (!imageUrl) return "Produit";
    return `<img src="${imageUrl}" alt="${escapeHtml(altText)}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`;
}

function updateCartButton(isInCart) {
    const btn = document.getElementById('cartToggleButton');
    if (!btn) return;

    if (isInCart) {
        btn.textContent = 'Retirer du panier';
        btn.classList.remove('btn-cart');
        btn.classList.add('btn-back');
    } else {
        btn.textContent = 'Ajouter au panier';
        btn.classList.remove('btn-back');
        btn.classList.add('btn-cart');
    }
}

async function refreshCartState(articleId) {
    const token = localStorage.getItem('token') || '';
    const base = (window.API_BASE || 'http://localhost:9000');
    if (!token) {
        updateCartButton(false);
        return;
    }

    const response = await fetch(base + '/panier_article/' + encodeURIComponent(articleId), {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (!response.ok) {
        updateCartButton(false);
        return;
    }

    const data = await response.json();
    updateCartButton(data.value === 1);
}

async function toggleCart(articleId) {
    const token = localStorage.getItem('token') || '';
    const base = (window.API_BASE || 'http://localhost:9000');
    const message = document.getElementById('cartMessage');

    if (!token) {
        message.className = 'message err';
        message.textContent = 'Veuillez vous connecter pour gérer le panier.';
        return;
    }

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
        message.className = 'message err';
        message.textContent = raw || 'Erreur serveur.';
        return;
    }

    if (!response.ok) {
        message.className = 'message err';
        message.textContent = data.message || 'Erreur panier.';
        return;
    }

    message.className = 'message ok';
    message.textContent = data.message || 'Panier mis à jour.';
    updateCartButton(data.value === 1);
}

async function loadArticle() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    const container = document.getElementById('productDetail');

    if (!id) {
        container.innerHTML = '<div class="message err">ID article manquant.</div>';
        return;
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/articles/' + encodeURIComponent(id), {
        method: 'GET'
    });

    if (!response.ok) {
        const text = await response.text();
        container.innerHTML = '<div class="message err">' + escapeHtml(text || 'Article introuvable.') + '</div>';
        return;
    }

    const article = await response.json();

    container.innerHTML = `
        <div class="product-image">${renderProductImage(article.image, `Image de ${article.titre || 'cet article'}`)}</div>
        <h2 class="product-title"><strong>${escapeHtml(article.titre)}</strong></h2>
        <div class="product-price">${escapeHtml(article.prix)} €</div>
        <div class="product-content">${escapeHtml(article.description || '')}</div>
        <div class="actions">
            <button id="cartToggleButton" class="btn btn-cart" onclick="toggleCart(${Number(article.id)})">Ajouter au panier</button>
            <a class="btn btn-back" href="panier.php">Voir mon panier</a>
            <a class="btn btn-back" href="boutique.php">Retour à la boutique</a>
        </div>
        <div id="cartMessage" class="message" style="display:block;"></div>
    `;

    refreshCartState(article.id);
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;
    loadArticle();
}

init();
</script>
</body>
</html>
