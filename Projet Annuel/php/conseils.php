<?php include 'api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conseil</title>
</head>
<body>

<?php include 'header/header.php' ?>

<h1> Conseils </h1>
<div id = "conseil"></div>

<?php include 'footer/footer.php';?>

<script>
async function listconseils(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/conseils", {
        method: "GET",
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
    
    const data = await response.json();
    const tab_conseil = document.getElementById("conseil")
    if (data.message){
        tab_conseil.innerHTML = data.message
    } else {
        let html = "<table border = 1><tr><th>Titre du conseil</th><th>Contenu</th><th>Date de publication</td></tr>";
        data.conseil.forEach(conseils => {
            html += "<tr><td>" + conseils.titre + "</td><td>" + conseils.contenu + "</td><td>" + conseils.date + "</td></tr>"
        });
        html += "</table>";
        tab_conseil.innerHTML = html;
    }
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        listconseils(token);
    }

init()
</script>
</body>
</html>
