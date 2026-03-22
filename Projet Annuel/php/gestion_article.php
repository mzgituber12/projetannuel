<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des articles</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        a { color: #4CAF50; text-decoration: none; margin: 0 5px; }
        a:hover { text-decoration: underline; }
        .search-section { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
        .search-section input { padding: 8px; margin-right: 10px; width: 300px; }
        .search-section button { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .search-section button:hover { background-color: #45a049; }
        .btn-create { background-color: #008CBA; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-bottom: 15px; }
        .btn-create:hover { background-color: #007399; }
        #resultat { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<?php include 'includes/header.php'?>

<h1>Gestion des articles</h1>

<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>

<a href='creer_article.php' class='btn-create'>+ Créer un nouvel article</a>

<div class="search-section">
    <h4>Entrer un nom d'article pour avoir tous les informations !</h4>
    <form onsubmit="search_articles(event); return false;">
        <input id="article_nom" placeholder="Titre de l'article..." type="text">
        <button type="submit">Rechercher</button>
    </form>
</div>

<div id="resultat"></div>
<h2> Liste des Articles </h2>
<div id = "articles"></div> 
<?php include("includes/footer.php") ?>

<script>
    function escapeHtml(value) {
        return String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#39;");
    }

    function renderImageHtml(image, altText) {
        const file = String(image ?? "").trim();
        if (!file) return "<em>Pas d'image</em>";
        const src = file.startsWith("http://") || file.startsWith("https://") || file.startsWith("/")
            ? file
            : `upload/${encodeURIComponent(file)}`;
        return `<img src="${src}" alt="${escapeHtml(altText)}" style="max-width: 100px; max-height: 100px; border-radius: 5px;">`;
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
            document.getElementById("resultat").innerHTML = "<div class='error'>Aucun article trouvé</div>";
        }else {
            const imageHtml = renderImageHtml(data.image, "Image de l'article");
            document.getElementById("resultat").innerHTML = 
            "<div class='success'>" +
            "<label><strong>ID :</strong> " + escapeHtml(data.id) + "</label><br>" +
            "<label><strong>Titre :</strong> " + escapeHtml(data.titre) + "</label><br>" +
            "<label><strong>Description :</strong> " + escapeHtml(data.description) + "</label><br>" +
            "<label><strong>Prix :</strong> " + escapeHtml(data.prix) + "</label><br>" +
            "<label><strong>Image :</strong> " + imageHtml + "</label><br>" +
            "<a href='modifier_article.php?id=" + data.id + "'>Modifier l'article</a> | " +
            "<a href='#' onclick='supprimer_article(" + data.id + ", \"" + data.titre + "\"); return false;'>Supprimer</a>" +
            "</div>";
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
        article.innerHTML = "<p>" + article_list.message + "</p>"
    } else {
        let html = "<table><tr><th>Image</th><th>Titre de l'article</th><th>Description</th><th>Prix de l'article</th><th>Actions</th></tr>";
        article_list.article.forEach(article => {
            const actions = "<a href='modifier_article.php?id=" + article.id + "'>Modifier</a> | " +
                "<a href='#' onclick=\"supprimer_article(" + article.id + ", '" + article.titre.replaceAll("'", "\\'") + "'); return false;\">Supprimer</a>";
            const imageHtml = renderImageHtml(article.image, `Image de ${article.titre}`);
            const desc = (article.description || '').length > 100 ? escapeHtml(article.description).slice(0, 100) + "..." : escapeHtml(article.description);
            html += "<tr><td>" + imageHtml + "</td><td>" + escapeHtml(article.titre) + "</td><td>" + desc + "</td><td>" + escapeHtml(article.prix) + "</td><td>" + actions + "</td></tr>";
        });
        html += "</table>";
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