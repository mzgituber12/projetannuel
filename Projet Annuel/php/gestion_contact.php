<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des contacts</title>
</head>
<body>

<?php include 'header/header.php'?>

<h1>Gestion des contacts</h1>

<div id="resultat"></div>

<?php include 'footer/footer.php'?>

<script>
    async function list_msgcontact() {

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_contact",{
            method: "GET",
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();
        const liste = document.getElementById("resultat")

        if (data.message == "Personne n'a contacté les administrateurs pour le moment") {
            liste.innerHTML = data.message
        } else {
            let html = "<table border = 1><tr><th>Email de l'utilisateur</th><th>Contenu du message</th></tr>";
            data.contact.forEach(contacts => {
            html += "<tr><td>" + contacts.email + "</td><td>" + contacts.contenu + "</tr>"
            });
            html += "</table>";
            liste.innerHTML = html;
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        list_msgcontact()
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