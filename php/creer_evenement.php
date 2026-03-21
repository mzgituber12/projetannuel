<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Creer un evenement</title>
</head>
<body>

<?php include 'includes/header.php'?>

<h1>Creer un evenement</h1>
<h2 id = "admin_err"></h2>

<form onsubmit="createEvenement()">
            <label>Nom :</label>
            <input type="text" name="nom" id="event_nom" placeholder="Nom" required><br><br>
            <label>Date :</label>
            <input type="datetime-local" step="60" name="date" id="event_date" required><br><br>
            <label>Description :</label>
            <input type="text" name="description" id="event_description" placeholder="Description" required><br><br>
            <label>Tarif :</label>
            <input type="number" name="tarif" id="event_tarif" placeholder="Tarif" required><br><br>
            <button type = "submit">Creer l'evenement</button>
</form>

<?php include 'includes/footer.php'?>

<script>
    async function createEvenement() {
        event.preventDefault();

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/creer_evenement", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                nom: document.getElementById('event_nom').value,
                date: document.getElementById('event_date').value,
                description: document.getElementById('event_description').value,
                tarif: parseInt(document.getElementById('event_tarif').value, 10),
            })
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }

        const data = await response.json();
        if (data.value == 1) {
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = "gestion_evenement.php?message=" + data.message;
        } else {
            document.getElementById("admin_err").innerHTML = data.message;
        }
    }

    async function init(){
        const token = localStorage.getItem("token")
        if (!await loginUser("online", token)) return
        adminUser(token)
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