<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des services</title>
</head>
<body>

<?php include 'includes/header.php'?>

<h1>Gestion des services</h1>
<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>
<h4>Entrer un nom de service pour avoir tout les informations !</h4>
<form onsubmit="search_service(event); return false;">
    <input id = "serv_name" placeholder="..." type="text">
    <button type = "submit">Rechercher</button>
</form>
<div id="resultat"></div>

<h4><a href="creer_service.php">Creer un service</a></h4>

<h2> Liste des services </h2>
<div id = "services"></div>
<?php include 'includes/footer.php'?>

<script>
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

    async function search_service(service) {
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
            document.getElementById("resultat").innerHTML = "Aucun service trouvé";
        }else {
            document.getElementById("resultat").innerHTML = 
            "<label>ID : " + data.id + "</label><br>" +
            "<label>Nom : " + data.nom + "</label><br>" +
            "<label>Description : " + data.description + "</label><br>" +
            "<label>Tarif : " + data.tarif + "</label><br>" +
            "<a href='modifier_service.php?id=" + data.id + "'>Modifier service</a>" +
            "<p><button onclick='supprimer_service(" + data.id + ", \"" + data.nom + "\")'>Supprimer le service</button></p>";
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
            service.innerHTML = service_list.message
        } else {
            let html = "<table border = 1><tr><th>Nom du service</th><th>Description</th><th>Tarif</th><th></th><th></th></tr>";
            service_list.service.forEach(serv => {
                click = "<a href='modifier_service.php?id=" + serv.id + "'>Modifier</a>" 
                click2 = `<button onclick="supprimer_service(${serv.id}, '${serv.nom}')">Supprimer</button>`;
                html += "<tr><td>" + serv.nom + "</td><td>" + serv.description + "</td><td>" + serv.tarif + "</td><td>" + click + "</td><td>" + click2 + "</td></tr>" 
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