<?php session_start(); include 'api_config.php'; ?>
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
    <title>Gestion des services</title>
</head>
<body>

<?php include 'header/header.php'?>

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

<h2> Services </h2>
<div id = "services"></div>

<script>
    async function search_service(service) {
        service.preventDefault();
        const name = document.getElementById("serv_name").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_service/" + name, {
            method: "GET",
        });
        const data = await response.json();

        if(data.id == 0) {
            document.getElementById("resultat").innerHTML = "Aucun service trouvé";
        }else {
            document.getElementById("resultat").innerHTML = 
            "<label>ID : " + data.id + "</label><br>" +
            "<label>Nom : " + data.nom + "</label><br>" +
            "<label>Description : " + data.description + "</label><br>" +
            "<label>Tarif : " + data.tarif + "</label><br>" +
            "<a href='modifier_service.php?id=" + data.id + "'>Modifier service</a>";
        }
    }

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    async function listService(token) {
        const base = (window.API_BASE || 'http://localhost:9000');

        const response = await fetch(base + "/services", {
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
            let html = "<table border = 1><tr><th>Nom du service</th><th>Description</th><th>Tarif</th><th></th></tr>";
            service_list.service.forEach(serv => {
                click = "<td><a href='modifier_service.php?id=" + serv.id + "'>Modifier</a></td>" 
                html += "<tr><td>" + serv.nom + "</td><td>" + serv.description + "</td><td>" + serv.tarif + "</td><td>" + click + "</td>" 
            });
            html += "</table>";
            service.innerHTML = html;
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        adminUser(token)
        listService(token);
    }
    init()
</script>
<?php include 'footer/footer.php'?>
</body>
</html>