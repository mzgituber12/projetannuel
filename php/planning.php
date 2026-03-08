<?php include 'api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planning</title>
</head>
<body>

<?php include 'header/header.php' ?>

<h1> Planning </h1>

<h2> Vos Evenements </h2>
<div id = "event"></div>

<h2> Vos Services </h2>
<div id = "service"></div>

<?php include 'footer/footer.php'; ?>

<script>
async function listplanning(token) {
    
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/planning_evenements", {
        method: "GET",
        headers: {"Token": token},
    });

    if (!response.ok) {
        const text = await response.text();
        alert(text)
        window.location.href = "erreur.php?code=" + response.status
        return
    }

    const response2 = await fetch(base + "/planning_services", {
        method: "GET",
        headers: {"Token": token},
    });

    if (!response2.ok) {
        const text = await response2.text();
        alert(text)
        window.location.href = "erreur.php?code=" + response2.status
        return
    }

    const data = await response.json();
    const tab_event = document.getElementById("event")
    if (data.message){
        tab_event.innerHTML = data.message
    } else {
        let html = "<table border = 1><tr><th>Nom de l'événement</th><th>Date</th><th>Description</th><th>Tarif</th></tr>";
        data.evenement.forEach(evenements => {
            html += "<tr><td>" + evenements.nom + "</td><td>" + evenements.date + "</td><td>" + evenements.description + "</td><td>" + evenements.tarif + "</td></tr>"
        });
        html += "</table>";
        tab_event.innerHTML = html;
    }

    const data2 = await response2.json();
    const tab_service = document.getElementById("service")
    if (data2.message){
        tab_service.innerHTML = data2.message
    } else {
        let html = "<table border = 1><tr><th>Nom du service</th><th>Description</th><th>Tarif</th></tr>";
        data2.service.forEach(services => {
            html += "<tr><td>" + services.nom + "</td><td>" + services.description + "</td><td>" + services.tarif + "</td></tr>"
        });
        html += "</table>";
        tab_service.innerHTML = html;
    }
}

async function init(){
    const token = localStorage.getItem("token")
    if (!await loginUser("online", token)) return
    listplanning(token)
}

init()

</script>

</body>
</html>