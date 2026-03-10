<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des evenements</title>
</head>
<body>

<?php include 'header/header.php'?>

<h1>Gestion des evenements</h1>
<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>
<h4>Entrer un nom d'evenements pour avoir toutes les informations !</h4>
<form onsubmit="search_event(event)">
    <input id = "event_name" placeholder="..." type="text">
    <button type = "submit">Rechercher</button>
</form>
<div id="resultat"></div>

<h2> Evenements </h2>
<div id = "evenements"></div>
<?php include 'footer/footer.php'?>

<script>
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
            document.getElementById("resultat").innerHTML = "Aucun evenement trouvé";
        }else {
            document.getElementById("resultat").innerHTML = 
            "<label>ID : " + data.id + "</label><br>" +
            "<label>Nom : " + data.nom + "</label><br>" +
            "<label>Date : " + data.date + "</label><br>" +
            "<label>Description : " + data.description + "</label><br>" +
            "<label>Tarif : " + data.tarif + "</label><br>" +
            "<a href='modifier_evenement.php?id=" + data.id + "'>Modifier l'événement</a>";
        }
    }

    

window.addEventListener('pageshow', function(event) {
if (event.persisted) {
    window.location.reload();
}
});


async function listEvenement(token) {
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + "/evenements", {
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
        evenement.innerHTML = evenement_list.message
    } else {
        let html = "<table border = 1><tr><th>Nom de l'événement</th><th>Description</th><th>Date de l'événement</th><th></th></tr>";
        evenement_list.evenement.forEach(evenement => {
            click = "<td><a href='modifier_evenement.php?id=" + evenement.id + "'>Modifier</a></td>" 
            html += "<tr><td>" + evenement.nom + "</td><td>" + evenement.description + "</td><td>" + evenement.date + "</td><td>" + click + "</td>" 
        });
        html += "</table>";
        evenement.innerHTML = html;
    }
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        adminUser(token)
        listEvenement(token);
    }
init()
</script>

</body>
</html>