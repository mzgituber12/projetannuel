<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("online", localStorage.getItem('token')); 
</script>

<script>
async function listCatalogue(token) {
    const base = (window.API_BASE || 'http://localhost:9000');

    const evenements_r = await fetch(base + "/evenements", {
        method: "GET",
        headers: {"Token": token}
    });

    if (!evenements_r.ok) {
        const html = await evenements_r.text();
        document.getElementById("error").innerHTML = "<h1>Erreur " + evenements_r.status + "</h1>" + html;
        return
    }

    const evenement_list = await evenements_r.json();
    const evenement  = document.getElementById("evenements")

    if (evenement_list.message){
        evenement.innerHTML = evenement_list.message
    } else {
        let html = "<table border = 1><tr><th>Nom de l'événement</th><th>Description</th><th>Date de l'événement</th><th></th></tr>";
        evenement_list.evenement.forEach(evenement => {
            if (evenement.rejoindre == "Rejoindre") {
                rej = "<a href='etat.php?type=evenements&state=join&id=" + evenement.id + "'>" + evenement.rejoindre + "</a>"
            } else {
                rej = "<a href='etat.php?type=evenements&state=leave&id=" + evenement.id + "'>" + evenement.rejoindre + "</a>"
            }
            html += "<tr><td>" + evenement.nom + "</td><td>" + evenement.description + "</td><td>" + evenement.date + "</td><td>" + rej + "</td></tr>"
        });
        html += "</table>";
        evenement.innerHTML = html;
    }

    const services_r = await fetch(base + "/services", {
        method: "GET",
        headers: {"Token": token}
    });

    if (!services_r.ok) {
        const html = await services_r.text();
        document.getElementById("error").innerHTML = "<h1>Erreur " + services_r.status + "</h1>" + html;
        return
    }

    const service_list = await services_r.json();
    const service  = document.getElementById("services")
    
    if (service_list.message){
        service.innerHTML = service_list.message
    } else {
        let html = "<table border = 1><tr><th>Nom du service</th><th>Description</th><th>Tarif</th><th></th></tr>";
        service_list.service.forEach(service => {
            if (service.rejoindre == "Rejoindre") {
                rej = "<a href='etat.php?type=services&state=join&id=" + service.id + "'>" + service.rejoindre + "</a>"
            } else if (service.rejoindre == "Quitter") {
                rej = "<a href='etat.php?type=services&state=leave&id=" + service.id + "'>" + service.rejoindre + "</a>"
            } else {
                rej = service.rejoindre
            }
            html += "<tr><td>" + service.nom + "</td><td>" + service.description + "</td><td>" + service.tarif + " €</td><td>" + rej + "</td></tr>"
        });
        html += "</table>";
        service.innerHTML = html;
    }

    const articles_r = await fetch(base + "/articles", {
        method: "GET",
    });

    if (!articles_r.ok) {
        const html = await articles_r.text();
        document.getElementById("error").innerHTML = "<h1>Erreur " + articles_r.status + "</h1>" + html;
        return
    }

    const article_list = await articles_r.json();
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
listCatalogue(localStorage.getItem('token'));
</script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conseil</title>
</head>
<body>

<?php include 'header/header.php';

echo "<h1> Catalogue </h1>";

if (isset($_GET['message']) && isset($_SESSION['state'])) {
    echo "<h2>" . $_GET['message'] . "</h2>";
    unset($_SESSION['state']);
} ?>

<h2> Evenements </h2>
<div id = "evenements"></div>

<h2> Services </h2>
<div id = "services"></div>

<h2> Article </h2>
<div id = "articles"></div>

<div id = "error"></div>
<br>

</body>
<?php include 'footer/footer.php'; ?>
</html>
