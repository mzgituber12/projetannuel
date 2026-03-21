<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Creer un article</title>
</head>
<body>

<?php include 'includes/header.php'?>

<h1>Creer un article</h1>
<h2 id = "admin_err"></h2>

<form onsubmit="createArticle()">
            <label>Titre</label>
            <input type="text" name="titre" id="article_titre" placeholder="Titre" required><br><br>
            <label>Description :</label>
            <input type="text" name="description" id="article_description" placeholder="Description" required><br><br>
            <label>Tarif :</label>
            <input type="number" name="prix" id="article_prix" placeholder="Prix" required><br><br>
            <button type = "submit">Creer l'article</button>
</form>

<?php include 'includes/footer.php'?>

<script>
    async function createArticle() {
        event.preventDefault();

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/creer_article", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                titre: document.getElementById('article_titre').value,
                description: document.getElementById('article_description').value,
                prix: parseInt(document.getElementById('article_prix').value, 10),
            })
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }

        const data = await response.json();
        if (data.value == 1) {
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = "gestion_article.php?message=" + data.message;
        } else {
            document.getElementById("admin_err").innerHTML = data.message;
        }
    }

    async function init(){
        const token = localStorage.getItem("token")
        if (!await loginUser("online", token)) return
        adminUser(token)
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