<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Boutique</title>
    <style>
        .shop-shell {
            max-width: 1100px;
            margin: 1rem auto;
            padding: 1rem;
            border: 1px solid rgba(0,0,0,.1);
            border-radius: 12px;
            background: #f4f8ff;
        }

        .shop-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .shop-card {
            display: flex;
            flex-direction: column;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        .shop-img {
            height: 150px;
            background: linear-gradient(135deg, #eef2ff 0%, #dbeafe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-weight: 700;
        }

        .shop-body {
            padding: 0.9rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .shop-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
        }

        .shop-desc {
            margin: 0;
            color: #374151;
            line-height: 1.35;
            min-height: 56px;
        }

        .shop-price {
            color: #1d4ed8;
            font-weight: 700;
        }

        .shop-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #2563eb;
            color: #fff;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-weight: 600;
        }

        .shop-link:hover {
            background: #1d4ed8;
        }

        @media (max-width: 1100px) {
            .shop-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 800px) {
            .shop-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 560px) {
            .shop-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php' ?>

<h1>Boutique</h1>
<div class="shop-shell">
    <div id="articles" class="shop-grid"></div>
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

async function listArticles() {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/articles', { method: 'GET' });

    if (!response.ok) {
        const text = await response.text();
        alert(text);
        window.location.href = 'erreur.php?code=' + response.status;
        return;
    }

    const data = await response.json();
    const container = document.getElementById('articles');

    if (data.message) {
        container.innerHTML = '<p>' + escapeHtml(data.message) + '</p>';
        return;
    }

    let html = '';
    data.article.forEach(a => {
        const desc = (a.description || '').length > 120
            ? (a.description || '').slice(0, 120) + '...'
            : (a.description || '');

        html += `
            <article class="shop-card">
                <div class="shop-img">Article</div>
                <div class="shop-body">
                    <h3 class="shop-title">${escapeHtml(a.nom)}</h3>
                    <p class="shop-desc">${escapeHtml(desc)}</p>
                    <span class="shop-price">${escapeHtml(a.prix)} €</span>
                    <a class="shop-link" href="article_detail.php?id=${encodeURIComponent(a.id)}">Voir le produit</a>
                </div>
            </article>
        `;
    });

    container.innerHTML = html;
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;
    listArticles();
}

init();
</script>
</body>
</html>
