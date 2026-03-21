<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des interventions</title>
</head>
<body>

<?php include 'includes/header.php'?>

<h1>Gestion des interventions</h1>

<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>

<h4>Entrer un ID d'intervention pour avoir tous les informations !</h4>

<form onsubmit="search_intervention(); return false;">
    <input id = "intervention_id" placeholder="..." type="text">
    <button type = "submit">Rechercher</button>
</form>

<div id="resultat"></div>

<?php include 'includes/footer.php'?>

<script>
    async function search_intervention() {
        event.preventDefault();
        const id = document.getElementById("intervention_id").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_intervention/" + id, {
            method: "GET",
        });
        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
        const data = await response.json();

        if(data.id == 0 || !data.id) {
            document.getElementById("resultat").innerHTML = "Aucune intervention trouvée";
        }else {
            document.getElementById("resultat").innerHTML = 
            "<label>ID : " + data.id + "</label><br>" +
            "<label>ID Service : " + data.id_service + "</label><br>" +
            "<label>ID Prestataire : " + data.id_prestataire + "</label><br>" +
            "<label>ID Utilisateur : " + data.id_utilisateur + "</label><br>" +
            "<label>Date : " + data.date + "</label><br>" +
            "<label>Statut : " + data.statut + "</label><br>" +
            "<label>Montant : " + data.montant + "</label><br>" +
            "<a href='modifier_intervention.php?id=" + data.id + "'>Modifier l'intervention</a>";
        }
    }

    async function listIntervention(token) {
        //Pas de code pour le moment
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        listIntervention(token);
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