<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conseil</title>
</head>
<body>

<?php include 'header/header.php' ?>

<h1> Catalogue </h1>

<?php if (isset($_SESSION['state']) && isset($_GET['message'])) { 
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}
?>

<h2> Evenements </h2>
<div id = "evenements"></div>

<h2> Services </h2>
<div id = "services"></div>

<h2> Article </h2>
<div id = "articles"></div>

<?php include 'footer/footer.php'; ?>

<script>
async function listCatalogue(token) {
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

    const response2 = await fetch(base + "/services", {
        method: "GET",
        headers: {"Token": token}
    });

    if (!response2.ok) {
        const text = await response2.text();
        alert(text)
        window.location.href = "erreur.php?code=" + response2.status
        return
    }

    const response3 = await fetch(base + "/articles", {
        method: "GET",
    });

    if (!response3.ok) {
        const text = await response3.text();
        alert(text)
        window.location.href = "erreur.php?code=" + response3.status
        return
    }

    const evenement_list = await response.json();
    const evenement  = document.getElementById("evenements")

    if (evenement_list.message){
        evenement.innerHTML = evenement_list.message
    } else {
        let html = "<table border = 1><tr><th>Nom de l'événement</th><th>Description</th><th>Date de l'événement</th><th></th></tr>";
        evenement_list.evenement.forEach(evenement => {
            if (evenement.rejoindre == "Rejoindre") {
                click = "<a onclick=\"updateUserEvent('" + localStorage.getItem('token') + "', 'evenements', 'join', " + evenement.id + ")\">" + evenement.rejoindre + "</a>"
            } else {
                click = "<a onclick=\"updateUserEvent('" + localStorage.getItem('token') + "', 'evenements', 'leave'," + evenement.id + ")\">" + evenement.rejoindre + "</a>"
            }
            rej = '<button>' + click + '</button>'
            html += "<tr><td>" + evenement.nom + "</td><td>" + evenement.description + "</td><td>" + evenement.date + "</td><td>" + rej + "</td></tr>"
        });
        html += "</table>";
        evenement.innerHTML = html;
    }

    const service_list = await response2.json();
    const service  = document.getElementById("services")
    
    if (service_list.message){
        service.innerHTML = service_list.message
    } else {
        let html = "<table border = 1><tr><th>Nom du service</th><th>Description</th><th>Tarif</th><th></th></tr>";
        service_list.service.forEach(service => {
            if (service.rejoindre == "Rejoindre") {
                click = "<a onclick=\"updateUserEvent('" + localStorage.getItem('token') + "', 'services', 'join'," + service.id + ")\">" + service.rejoindre + "</a>"
                rej = '<button>' + click + '</button>'
            } else if (service.rejoindre == "Quitter") {
                click = "<a onclick=\"updateUserEvent('" + localStorage.getItem('token') + "', 'services', 'leave'," + service.id + ")\">" + service.rejoindre + "</a>"
                rej = '<button>' + click + '</button>'
            } else {
                rej = service.rejoindre
            }
            html += "<tr><td>" + service.nom + "</td><td>" + service.description + "</td><td>" + service.tarif + " €</td><td>" + rej + "</td></tr>"
        });
        html += "</table>";
        service.innerHTML = html;
    }

    const article_list = await response3.json();
    const article  = document.getElementById("articles")

    if (article_list.message){
        article.innerHTML = article_list.message
    } else {
        let html = "<table border = 1><tr><th>Nom de l'article</th><th>Description</th><th>Prix</td></tr>";
        article_list.article.forEach(article => {
            html += "<tr><td>" + article.nom + "</td><td>" + article.description + "</td><td>" + article.prix + " €</td></tr>"
        });
        html += "</table>";
        article.innerHTML = html;
    }
}

async function updateUserEvent(token, type, state, id) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/" + type + "/" + id, {
        method: "POST",
        headers: {"Content-Type": "application/json", "Token": token},
        body: JSON.stringify({state: state})
    });

    if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
    const data = await response.json();
    await fetch("ajouter_session_state.php", {method: "POST"});
    const type2 = type == "evenements" ? "Evenement" : "Service"
    const state2 = state == "join" ? " rejoint" : " quitté"
    window.location.search = "?message=" + type2 + state2 + " avec succes"
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        listCatalogue(token);
    }

init()
</script>
</html>
