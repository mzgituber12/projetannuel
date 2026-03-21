<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("online", localStorage.getItem('token')); 
</script>
<script src="admin.js"></script>
<script>
adminUser(localStorage.getItem('token')); 
</script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des conseils</title>
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

<h1>Gestion des conseils</h1>

<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>

<a href='modifier_conseil.php?id=new' class='btn-create'>+ Créer un nouveau conseil</a>

<div class="search-section">
    <h4>Entrer un titre de conseil pour avoir toutes les informations !</h4>
    <form onsubmit="search_conseil(event); return false;">
        <input id="conseil_titre" placeholder="Titre du conseil..." type="text">
        <button type="submit">Rechercher</button>
    </form>
</div>

<div id="resultat"></div>

<h2>Liste des conseils</h2>
<div id="conseils"></div>

<script>
    async function search_conseil(event) {
        event.preventDefault();
        const titre = document.getElementById("conseil_titre").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_conseil/" + titre, {
            method: "GET",
        });
        const data = await response.json();

        if(data.id == 0 || !data.titre) {
            document.getElementById("resultat").innerHTML = "<div class='error'>Aucun conseil trouvé</div>";
        } else {
            let imageHTML = data.image ? `<img src="../upload/${data.image}" style="max-width: 100px; max-height: 100px; border-radius: 5px;">` : '<em>Pas d\'image</em>';
            document.getElementById("resultat").innerHTML = 
            "<div class='success'>" +
            "<label><strong>ID :</strong> " + data.id + "</label><br>" +
            "<label><strong>Titre :</strong> " + data.titre + "</label><br>" +
            "<label><strong>Contenu :</strong> " + data.contenu + "</label><br>" +
            "<label><strong>Image :</strong> " + imageHTML + "</label><br>" +
            "<label><strong>Date :</strong> " + data.date + "</label><br>" +
            "<a href='modifier_conseil.php?id=" + data.id + "'>Modifier conseil</a> | " +
            "<a href='#' onclick='deleteConseils(" + data.id + "); return false;'>Supprimer</a>" +
            "</div>";
        }
    }

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    async function listConseils(token) {
        const base = (window.API_BASE || 'http://localhost:9000');

        const response = await fetch(base + "/gestion_conseils", {
            method: "GET",
            headers: {"Token": token}
        });

        if (!response.ok) {
            const text = await response.text();
            if (response.status !== 404) {
                alert(text)
                window.location.href = "erreur.php?code=" + response.status
            }
            return
        }
        const conseil_list = await response.json();
        const conseil = document.getElementById("conseils")

        if (conseil_list.message){
            conseil.innerHTML = "<p>" + conseil_list.message + "</p>"
        } else {
            let html = "<table><tr><th>Image</th><th>Titre du conseil</th><th>Contenu</th><th>Date de publication</th><th>Actions</th></tr>";
            conseil_list.conseil.forEach(cons => {
                const actions = "<a href='modifier_conseil.php?id=" + cons.id + "'>Modifier</a> | " +
                               "<a href='#' onclick='deleteConseils(" + cons.id + "); return false;'>Supprimer</a>";
                const imageHTML = cons.image ? `<img src="../upload/${cons.image}" style="max-width: 80px; max-height: 80px; border-radius: 5px;">` : '<em>-</em>';
                html += "<tr><td>" + imageHTML + "</td><td>" + cons.titre + "</td><td>" + cons.contenu.substring(0, 100) + "...</td><td>" + cons.date + "</td><td>" + actions + "</td></tr>";
            });
            html += "</table>";
            conseil.innerHTML = html;
        }
    }

    async function deleteConseils(id) {
        if (!confirm("Êtes-vous sûr de vouloir supprimer ce conseil ?")) {
            return;
        }

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/supprimer_conseil/" + id, {
            method: "DELETE",
            headers: {"Token": localStorage.getItem('token')}
        });

        if (!response.ok) {
            const text = await response.text();
            alert("Erreur : " + text);
            return;
        }

        const data = await response.json();
        if (data.value == 1) {
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = "gestion_conseil.php?message=" + data.message;
        } else {
            alert("Erreur : " + data.message);
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        adminUser(token)
        listConseils(token);
    }
    init()
</script>
<?php include 'includes/footer.php'?>
</body>
</html>
