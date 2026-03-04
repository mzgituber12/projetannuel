<?php include 'api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("online", localStorage.getItem('token')); 
</script>

<script>
async function listconseils(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/conseils", {
        method: "GET",
    });

    if (!response.ok) {
        const html = await response.text();
        document.getElementById("error").innerHTML = "<h1>Erreur " + response.status + "</h1>" + html;
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
listconseils(localStorage.getItem('token'));
</script>

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

<div id = "error"></div>

</body>
<?php include 'footer/footer.php';?>
</html>
