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

<h4>Entrer un nom d'evenements pour avoir tout les informations !</h4>

<form onsubmit="search_event(event)">
    <input id = "event_name" placeholder="..." type="text">
    <button type = "submit">Rechercher</button>
</form>

<div id="resultat"></div>
</div>

<script>
    async function search_event(event) {
        event.preventDefault();
        const name = document.getElementById("event_name").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_evenement_nom/" + name, {
            method: "GET",
        });
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
</script>
<?php include 'footer/footer.php'?>
</body>
</html>