<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Détail du conseil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

<?php include 'includes/header.php' ?>

<h1 class="mt-5 mb-4" data-i18n>Détail du conseil</h1>
<div class="container-lg" id="conseilDetail" data-i18n>Chargement...</div>

<?php include 'includes/footer.php';?>

<script>
async function loadConseilDetail() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");
    const container = document.getElementById("conseilDetail");

    if (!id) {
        container.innerHTML = '<div class="alert alert-danger">ID du conseil manquant.</div>';
        return;
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/conseils/' + encodeURIComponent(id), {
        method: 'GET'
    });

    if (!response.ok) {
        const text = await response.text();
        container.innerHTML = '<div class="alert alert-danger">' + String(text || 'Impossible de charger ce conseil.') + '</div>';
        return;
    }

    const conseil = await response.json();
    const imageHtml = conseil.image
        ? '<img class="img-fluid rounded mb-4" style="max-height:420px;object-fit:cover;" src="upload/' + encodeURIComponent(conseil.image) + '" alt="Image du conseil">'
        : '<div class="bg-light rounded mb-4 d-flex align-items-center justify-content-center text-muted" style="height:400px;">Pas d\'image</div>';

    container.innerHTML = `
        <div class="row">
            <div class="col-lg-8 mx-auto">
                ${imageHtml}
                <h2 class="h3 mb-2"><strong>${String(conseil.titre)}</strong></h2>
                <p class="text-muted small mb-3">Publié le ${String(conseil.date || '')}</p>
                <div class="text-secondary mb-4" style="white-space:pre-wrap;word-break:break-word;">${String(conseil.contenu || '')}</div>
                <a class="btn btn-primary" href="conseils.php" data-i18n>Retour à la page conseils</a>
            </div>
        </div>
    `;

    if a in table'{{conseil}}' && conseil.auteur_id {
        const authorResponse = await fetch(base + '/users/' + encodeURIComponent(conseil.auteur_id), {
            method: 'GET'
        });
        if (authorResponse.ok) {
            const author = await authorResponse.json();
            const authorInfo = document.createElement('p');
            authorInfo.className = 'text-muted small mb-3';
            authorInfo.textContent = 'Auteur : ' + String(author.name || 'Inconnu');
            container.querySelector('.h3').insertAdjacentElement('afterend', authorInfo);
        }
    }
}

window.addEventListener('DOMContentLoaded', loadConseilDetail);
</script>
</body>
</html>

