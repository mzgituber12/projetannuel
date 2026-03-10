<?php include 'api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contrats</title>
</head>
<body>

<?php include 'header/header.php' ?>

<h1> Contrats </h1>
<h2> Vos Contrats </h2>
<div id = "contrat"></div>

<?php include 'footer/footer.php';?>

<script>
async function listcontrats(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/contrats", {
        method: "GET",
        headers: {"Token": token},
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

    const data = await response.json();
    const tab_contrat = document.getElementById("contrat")
    if (data.message){
        tab_contrat.innerHTML = data.message
    } else {
        let html = "<table border = 1><tr><th>Nom du contrat</th></tr>";
        data.contrat.forEach(contrats => {
            html += "<tr><td>" + contrats.nom + "</td></tr>"
        });
        html += "</table>";
        tab_contrat.innerHTML = html;
    }
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        listcontrats(token);
    }

init()
</script>

</body>
</html>
