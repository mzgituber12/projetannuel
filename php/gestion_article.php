<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des articles</title>
</head>
<body>

<?php include 'includes/header.php'?>

<h1>Gestion des articles</h1>

<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>

<h4>Entrer un nom d'article pour avoir tous les informations !</h4>
<form onsubmit="search_articles(); return false;">
    <input id = "article_nom" placeholder="..." type="text">
    <button type = "submit">Rechercher</button>
</form>

<div id="resultat"></div>

<h4><a href="creer_article.php">Creer un article</a></h4>
<h2> Liste des Articles </h2>
<div id = "articles"></div> 
<?php include("includes/footer.php") ?>

<script>
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

    async function search_articles() {

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
            document.getElementById("resultat").innerHTML = "Aucun article trouvée";
        }else {
            document.getElementById("resultat").innerHTML = 
            "<label>ID : " + data.id + "</label><br>" +
            "<label>Titre : " + data.titre + "</label><br>" +
            "<label>Description : " + data.description + "</label><br>" +
            "<label>Prix : " + data.prix + "</label><br>" +
            "<a href='modifier_article.php?id=" + data.id + "'>Modifier l'article</a>" +
            "<p><button onclick='supprimer_article(" + data.id + ", \"" + data.titre + "\")'>Supprimer l'article</button></p>";
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
        article.innerHTML = article_list.message
    } else {
        let html = "<table border = 1><tr><th>Titre de l'article</th><th>Description</th><th>Prix de l'article</th><th></th><th></th></tr>";
        article_list.article.forEach(article => {
            click = "<a href='modifier_article.php?id=" + article.id + "'>Modifier</a>" 
            click2 = `<button onclick="supprimer_article(${article.id}, '${article.titre}')">Supprimer</button>`
            html += "<tr><td>" + article.titre + "</td><td>" + article.description + "</td><td>" + article.prix + "</td><td>" + click + "</td><td>" + click2 + "</td>" 
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