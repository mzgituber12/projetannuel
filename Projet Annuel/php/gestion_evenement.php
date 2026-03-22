<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des evenements</title>
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

<h1>Gestion des evenements</h1>
<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>
<a href='creer_evenement.php' class='btn-create'>+ Créer un nouvel evenement</a>

<div class="search-section">
    <h4>Entrer un nom d'evenement pour avoir toutes les informations !</h4>
    <form onsubmit="search_event(event)">
        <input id="event_name" placeholder="Nom de l'evenement..." type="text">
        <button type="submit">Rechercher</button>
    </form>
</div>
<div id="resultat"></div>

<h2> Liste des evenements </h2>
<div id = "evenements"></div>
<?php include 'includes/footer.php'?>

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

    async function supprimer_evenement(id, nom){
        const confirmation = confirm("Êtes-vous sûr de vouloir supprimer l'evenement " + nom + " ?");
        if (!confirmation){
            return;
        } else {
            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + "/supprimer_evenement/" + id, {
                method: "DELETE",
            });
            if (!response.ok){
                const text = await response.text();
                alert(text)
                window.location.href = "erreur.php?code=" + response.status
                return;
            }
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = window.location.pathname + "?message=Evenement " + nom + " supprimé avec succes" ;
            }
    }

    async function search_event(event) {
        event.preventDefault();
        const name = document.getElementById("event_name").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_evenement_nom/" + name, {
            method: "GET",
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();

        if(data.id == 0) {
            document.getElementById("resultat").innerHTML = "<div class='error'>Aucun evenement trouvé</div>";
        }else {
            const imageHtml = renderImageHtml(data.image, "Image de l'evenement");
            document.getElementById("resultat").innerHTML = 
            "<div class='success'>" +
            "<label><strong>ID :</strong> " + escapeHtml(data.id) + "</label><br>" +
            "<label><strong>Nom :</strong> " + escapeHtml(data.nom) + "</label><br>" +
            "<label><strong>Date :</strong> " + escapeHtml(data.date) + "</label><br>" +
            "<label><strong>Description :</strong> " + escapeHtml(data.description) + "</label><br>" +
            "<label><strong>Tarif :</strong> " + escapeHtml(data.tarif) + "</label><br>" +
            "<label><strong>Image :</strong> " + imageHtml + "</label><br>" +
            "<a href='modifier_evenement.php?id=" + data.id + "'>Modifier l'événement</a> | " +
            "<a href='#' onclick='supprimer_evenement(" + data.id + ", \"" + data.nom + "\"); return false;'>Supprimer</a>" +
            "</div>";
        }
    }

async function listEvenements(token) {
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + "/list_evenements", {
        method: "GET",
        headers: {"Token": token}
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
    }
    const evenement_list = await response.json();
    const evenement  = document.getElementById("evenements")

    if (evenement_list.message){
        evenement.innerHTML = "<p>" + evenement_list.message + "</p>"
    } else {
        let html = "<table><tr><th>Image</th><th>Nom de l'événement</th><th>Description</th><th>Date de l'événement</th><th>Actions</th></tr>";
        evenement_list.evenement.forEach(evenement => {
            const actions = "<a href='modifier_evenement.php?id=" + evenement.id + "'>Modifier</a> | " +
                "<a href='#' onclick=\"supprimer_evenement(" + evenement.id + ", '" + evenement.nom.replaceAll("'", "\\'") + "'); return false;\">Supprimer</a>";
            const imageHtml = renderImageHtml(evenement.image, `Image de ${evenement.nom}`);
            const desc = (evenement.description || '').length > 100 ? escapeHtml(evenement.description).slice(0, 100) + "..." : escapeHtml(evenement.description);
            html += "<tr><td>" + imageHtml + "</td><td>" + escapeHtml(evenement.nom) + "</td><td>" + desc + "</td><td>" + escapeHtml(evenement.date) + "</td><td>" + actions + "</td></tr>";
        });
        html += "</table>";
        evenement.innerHTML = html;
    }
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        listEvenements(token);
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
