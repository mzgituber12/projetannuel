<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Détail produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

<?php include 'includes/header.php' ?>

<h1 class="mt-5 mb-4" data-i18n>Détail produit</h1>
<div class="container-lg" id="productDetail" style="min-height: auto;">Chargement...</div>

<?php include 'includes/footer.php';?>

<script>
function modifImageUrl(image) {
    const contenue = String(image ?? "").trim();
    if (!contenue) return "";
    if (contenue.startsWith("http://") || contenue.startsWith("https://") || contenue.startsWith("/")) {
        return contenue;
    }
    return `upload/${encodeURIComponent(contenue)}`;
}

function renduBoutiqueImage(image, altText) {
    const imageUrl = modifImageUrl(image);
    if (!imageUrl) return "Produit";
    return `<img src="${imageUrl}" alt="${String(altText)}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`;
}

function mettreAJourBoutonPanier(estDansPanier) {
    const btn = document.getElementById('cartToggleButton');
    if (!btn) return;

    if (estDansPanier) {
        btn.textContent = 'Retirer du panier';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-danger');
    } else {
        btn.textContent = 'Ajouter au panier';
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-success');
    }
}

async function rafraichirEtatPanier(articleId) {
    const token = localStorage.getItem('token') || '';
    const base = (window.API_BASE || 'http://localhost:9000');
    if (!token) {
        mettreAJourBoutonPanier(false);
        return;
    }

    const response = await fetch(base + '/panier_article/' + encodeURIComponent(articleId), {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (!response.ok) {
        mettreAJourBoutonPanier(false);
        return;
    }

    const data = await response.json();
    mettreAJourBoutonPanier(data.value === 1);
}

async function basculerPanier(articleId) {
    const token = localStorage.getItem('token') || '';
    const base = (window.API_BASE || 'http://localhost:9000');
    const message = document.getElementById('cartMessage');

    if (!token) {
        message.className = 'alert alert-danger';
        message.classList.remove('d-none');
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
        message.className = 'alert alert-danger';
        message.classList.remove('d-none');
        message.textContent = raw || 'Erreur serveur.';
        return;
    }

    if (!response.ok) {
        message.className = 'alert alert-danger';
        message.classList.remove('d-none');
        message.textContent = data.message || 'Erreur panier.';
        return;
    }

    message.className = 'alert alert-success';
    message.classList.remove('d-none');
    message.textContent = data.message || 'Panier mis à jour.';
    mettreAJourBoutonPanier(data.value === 1);
}

async function chargerArticle() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    const container = document.getElementById('productDetail');

    if (!id) {
        container.innerHTML = '<div class="message err">ID article manquant.</div>';
        return;
    }

    try {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + '/articles/' + encodeURIComponent(id), {
            method: 'GET'
        });

        if (!response.ok) {
            const text = await response.text();
            container.innerHTML = '<div class="message err">' + String(text || 'Article introuvable.') + '</div>';
            return;
        }

        const article = await response.json();

        container.innerHTML = `
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="bg-light rounded p-4 mb-4 text-center" style="min-height:400px;display:flex;align-items:center;justify-content:center;">
                        ${renduBoutiqueImage(article.image, `Image de ${article.titre || 'cet article'}`)}
                    </div>
                    <h2 class="h3 mb-2"><strong>${String(article.titre)}</strong></h2>
                    <p class="text-primary fw-bold fs-5 mb-3">${String(article.prix)} €</p>
                    <div class="text-secondary mb-4" style="white-space:pre-wrap;word-break:break-word;">${String(article.description || '')}</div>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <button id="cartToggleButton" class="btn btn-success" onclick="basculerPanier(${Number(article.id)})" data-i18n>Ajouter au panier</button>
                        <a class="btn btn-primary" href="panier.php" data-i18n>Voir mon panier</a>
                        <a class="btn btn-outline-secondary" href="boutique.php" data-i18n>Retour à la boutique</a>
                    </div>
                    <div id="cartMessage" class="alert alert-info d-none"></div>
                </div>
            </div>
        `;

        rafraichirEtatPanier(article.id);
    } catch (error) {
        container.innerHTML = '<div class="alert alert-danger">Erreur de chargement du produit.</div>';
    }
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;
    chargerArticle();
}

init();
</script>
</body>
</html>

