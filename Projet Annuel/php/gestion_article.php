<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Gestion des articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class="container-fluid mt-4">
    <h1 class="mb-4" data-i18n>Gestion des articles</h1>

    <?php
    if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_GET['message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['state']);
    }?>

    <div class="mb-4">
        <a href='creer_article.php' class='btn btn-primary'><i class="bi bi-plus-circle"></i> <span data-i18n>Créer un nouvel article</span></a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0" data-i18n>Rechercher un article</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search_articles(event); return false;" class="row g-3">
                <div class="col-md-8">
                    <input id="article_nom" placeholder="Titre de l'article..." type="text" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100" data-i18n>Rechercher</button>
                </div>
            </form>
        </div>
    </div>

    <div id="resultat"></div>
    
    <h2 class="mt-5 mb-3" data-i18n>Liste des Articles</h2>
    <div id="articles"></div> 
</div>

<?php include("includes/footer.php") ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
    function renderImageHtml(image, altText) {
        const file = String(image ?? "").trim();
        if (!file) return "<em>Pas d'image</em>";
        const src = file.startsWith("http://") || file.startsWith("https://") || file.startsWith("/")
            ? file
            : `upload/${encodeURIComponent(file)}`;
        return `<img src="${src}" alt="${String(altText)}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">`;
    }

    async function supprimer_article(id, titre){
        const confirmation = confirm("Êtes-vous sûr de vouloir supprimer l'article " + titre + " ?");
        if (!confirmation){
            return;
        } else {
            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + "/supprimer_article/" + id, {
                method: "DELETE",
            });
            if (!response.ok) {
                const text = await response.text();
                alert(text)
                window.location.href = "erreur.php?code=" + response.status
                return
            }
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = window.location.pathname + "?message=Article " + titre + " supprimé avec succes" ;
            }
    }

    async function search_articles(event) {
        event.preventDefault();
        const nom = document.getElementById("article_nom").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_article/" + nom, {
            method: "GET",
        });
        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
        const data = await response.json();

        if(data.id == 0 || !data.id) {
            document.getElementById("resultat").innerHTML = "<div class='alert alert-warning'>Aucun article trouvé</div>";
        }else {
            const imageHtml = renderImageHtml(data.image, "Image de l'article");
            document.getElementById("resultat").innerHTML = 
            "<div class='alert alert-success'>" +
            "<div class='row'><div class='col-md-8'>" +
            "<label><strong>ID :</strong> " + String(data.id) + "</label><br>" +
            "<label><strong>Titre :</strong> " + String(data.titre) + "</label><br>" +
            "<label><strong>Description :</strong> " + String(data.description) + "</label><br>" +
            "<label><strong>Prix :</strong> " + String(data.prix) + "€</label><br>" +
            "</div><div class='col-md-4'>" +
            "<label><strong>Image :</strong></label><br>" + imageHtml + 
            "</div></div>" +
            "<div class='mt-3'>" +
            "<a href='modifier_article.php?id=" + data.id + "' class='btn btn-sm btn-warning' data-i18n>Modifier</a> " +
            "<a href='#' onclick='supprimer_article(" + data.id + ", \"" + String(data.titre) + "\"); return false;' class='btn btn-sm btn-danger' data-i18n>Supprimer</a>" +
            "</div></div>";
        }
    }

    async function listArticle(token) {
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + "/list_articles", {
        method: "GET",
        headers: {"Token": token}
    });

    if (!response.ok) {
        const text = await response.text();
        alert(text)
        window.location.href = "erreur.php?code=" + response.status
        return
    }
    const article_list = await response.json();
    const article  = document.getElementById("articles")

    if (article_list.message){
        article.innerHTML = "<div class='alert alert-info'>" + article_list.message + "</div>"
    } else {
        let html = "<div class='table-responsive'><table class='table table-hover'><thead class='table-success'><tr><th data-i18n>Image</th><th data-i18n>Titre de l'article</th><th data-i18n>Description</th><th data-i18n>Prix</th><th data-i18n>Actions</th></tr></thead><tbody>";
        article_list.article.forEach(article => {
            const actions = "<a href='modifier_article.php?id=" + article.id + "' class='btn btn-sm btn-warning' data-i18n>Modifier</a> " +
                "<a href='#' onclick=\"supprimer_article(" + article.id + ", '" + article.titre.replaceAll("'", "\\'") + "'); return false;\" class='btn btn-sm btn-danger' data-i18n>Supprimer</a>";
            const imageHtml = renderImageHtml(article.image, `Image de ${article.titre}`);
            const desc = (article.description || '').length > 40 ? String(article.description).slice(0, 40) + "..." : String(article.description);
            html += "<tr><td>" + imageHtml + "</td><td>" + String(article.titre) + "</td><td>" + desc + "</td><td>" + String(article.prix) + "€</td><td>" + actions + "</td></tr>";
        });
        html += "</tbody></table></div>";
        article.innerHTML = html;
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        listArticle(token);
    }

    window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
    window.location.reload();
    }
    });
init()
</script>
</body>
</html>
