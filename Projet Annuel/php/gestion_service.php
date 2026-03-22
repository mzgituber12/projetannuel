<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des services</title>
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

<h1>Gestion des services</h1>
<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>
<a href='creer_service.php' class='btn-create'>+ Créer un nouveau service</a>

<div class="search-section">
    <h4>Entrer un nom de service pour avoir toutes les informations !</h4>
    <form onsubmit="search_service(event); return false;">
        <input id="serv_name" placeholder="Nom du service..." type="text">
        <button type="submit">Rechercher</button>
    </form>
</div>
<div id="resultat"></div>

<h2> Liste des services </h2>
<div id = "services"></div>
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

    async function supprimer_service(id, nom){
        const confirmation = confirm("Êtes-vous sûr de vouloir supprimer le service " + nom + " ?");
        if (!confirmation){
            return;
        } else {
            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + "/supprimer_service/" + id, {
                method: "DELETE",
            });
            if (!response.ok){
                const text = await response.text();
                alert(text)
                window.location.href = "erreur.php?code=" + response.status
                return;
            }
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = window.location.pathname + "?message=Service " + nom + " supprimé avec succes" ;
            }
    }

    async function search_service(event) {
        event.preventDefault();
        const name = document.getElementById("serv_name").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_service/" + name, {
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
            document.getElementById("resultat").innerHTML = "<div class='error'>Aucun service trouvé</div>";
        }else {
            const imageHtml = renderImageHtml(data.image, "Image du service");
            document.getElementById("resultat").innerHTML = 
            "<div class='success'>" +
            "<label><strong>ID :</strong> " + escapeHtml(data.id) + "</label><br>" +
            "<label><strong>Nom :</strong> " + escapeHtml(data.nom) + "</label><br>" +
            "<label><strong>Description :</strong> " + escapeHtml(data.description) + "</label><br>" +
            "<label><strong>Tarif :</strong> " + escapeHtml(data.tarif) + "</label><br>" +
            "<label><strong>Image :</strong> " + imageHtml + "</label><br>" +
            "<a href='modifier_service.php?id=" + data.id + "'>Modifier service</a> | " +
            "<a href='#' onclick='supprimer_service(" + data.id + ", \"" + data.nom + "\"); return false;'>Supprimer</a>" +
            "</div>";
        }
    }

    async function listService(token) {
        const base = (window.API_BASE || 'http://localhost:9000');

        const response = await fetch(base + "/list_services", {
            method: "GET",
            headers: {"Token": token}
        });

        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
        const service_list = await response.json();
        const service = document.getElementById("services")

        if (service_list.message){
            service.innerHTML = "<p>" + service_list.message + "</p>"
        } else {
            let html = "<table><tr><th>Image</th><th>Nom du service</th><th>Description</th><th>Tarif</th><th>Actions</th></tr>";
            service_list.service.forEach(serv => {
                const actions = "<a href='modifier_service.php?id=" + serv.id + "'>Modifier</a> | " +
                    "<a href='#' onclick=\"supprimer_service(" + serv.id + ", '" + serv.nom.replaceAll("'", "\\'") + "'); return false;\">Supprimer</a>";
                const imageHtml = renderImageHtml(serv.image, `Image de ${serv.nom}`);
                const desc = (serv.description || '').length > 100 ? escapeHtml(serv.description).slice(0, 100) + "..." : escapeHtml(serv.description);
                html += "<tr><td>" + imageHtml + "</td><td>" + escapeHtml(serv.nom) + "</td><td>" + desc + "</td><td>" + escapeHtml(serv.tarif) + "</td><td>" + actions + "</td></tr>";
            });
            html += "</table>";
            service.innerHTML = html;
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        listService(token);
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
