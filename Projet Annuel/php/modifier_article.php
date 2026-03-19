<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title id ="page_title"></title>
</head>
<body>

<?php include 'includes/header.php'?>
<h1 id="admin_title"></h1>
<h2 id ="admin_err"></h2>

<div id="resultat"></div>
<?php include 'includes/footer.php'?>
</body>
</html>

<script>
    async function updateArticle() {
        event.preventDefault();
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/modifier_article/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "PATCH",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                id: parseInt(document.getElementById("article_id").value, 10),
                titre: document.getElementById('article_titre').value,
                description: document.getElementById('article_description').value,
                prix: parseFloat(document.getElementById('article_prix').value)
            })
        });
        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

        const data = await response.json();
        if (data.value == 1) {
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = "gestion_article.php?message=" + data.message;
        } else {
            document.getElementById("admin_error").innerHTML = data.message;
        }
    }   

    async function search_article() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_article_id/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "GET",
        });

        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

        const data = await response.json();

        document.getElementById("page_title").innerHTML = "Modifier l'article " + data.titre;
        document.getElementById('admin_title').innerHTML = "Modification de l'article " + data.titre;
        if(data.id == 0 || !data.id) {
            document.getElementById("resultat").innerHTML = "Aucun article trouvé";
        } else {
            document.getElementById("resultat").innerHTML = `
            <form onsubmit="updateArticle()">
            <label>ID :</label>
            <input type="number" name="id" id="article_id" value="${data.id}" readonly> Pas modifiable <br><br>
            <label>Titre :</label>
            <input type="text" name="titre" id="article_titre" value="${data.titre}" required><br><br>
            <label>Description :</label>
            <textarea name="description" id="article_description" required>${data.description}</textarea><br><br>
            <label>Tarif :</label>
            <input type="number" name="prix" id="article_prix" value="${data.prix}" step="0.01" required><br><br>
            <button type = "submit">Confirmer les modifications</button>
            </form>
            `;
            }
        }

    async function init(){
        const token = localStorage.getItem("token")
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        search_article();
    }

window.addEventListener('pageshow', function(event) {
if (event.persisted) {
    window.location.reload();
}
});
init()
    
</script>