<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Boutique</title>
    <style>
        .mb-custom{
            margin-bottom: 2rem
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php' ?>

<div class='container mt-5 mb-4'>
<h1 data-i18n class='text-center mb-custom' style="font-size:50px">Boutique</h1>
<p class="text-center mb-custom" data-i18n>
Explorez les articles proposés à la vente par les collaborateurs de Silver Happy.  
<br>Une grande variété d’objets peut être disponible, alors gardez l’œil ouvert pour ne rien manquer.
</p>
<div class="container-lg">
    <div id="articles" class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3"></div>
</div>
</div>

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
    return `<img src="${imageUrl || "noimage.avif"}" alt="${String(altText)}" style="width:100%;height:100%;object-fit:cover;">`;
}

async function listerArticles() {
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
        container.innerHTML = '<p>' + String(data.message) + '</p>';
        return;
    }

    let html = '';
    data.article.forEach(a => {
        const desc = (a.description || '').length > 40
            ? (a.description || '').slice(0, 40) + '...'
            : (a.description || '');

        html += `
            <div class="col">
                <article class="card h-100 border-0 shadow-sm">
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:230px;">
                        ${renduBoutiqueImage(a.image, `Image de ${a.titre || "cet article"}`)}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${String(a.titre)}</h5>
                        <p class="card-text text-muted small flex-grow-1">${String(desc)}</p>
                        <p class="card-text text-primary fw-bold mb-2">${String(a.prix)} €</p>
                        <a class="btn btn-primary" href="article_detail.php?id=${encodeURIComponent(a.id)}" data-i18n>Voir le produit</a>
                    </div>
                </article>
            </div>
        `;
    });

    container.innerHTML = html;
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;
    listerArticles();
}

init();
</script>
</body>
</html>

