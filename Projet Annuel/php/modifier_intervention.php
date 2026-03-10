<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("online", localStorage.getItem('token')); 
</script>
<script src="admin.js"></script>
<script>
adminUser(localStorage.getItem('token')); 
</script>

<script>
    async function updateIntervention(event) {
        event.preventDefault();
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/modifier_intervention/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "PATCH",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                id: parseInt(document.getElementById("intervention_id").value, 10),
                id_service: parseInt(document.getElementById("intervention_service").value, 10),
                id_prestataire: parseInt(document.getElementById("intervention_prestataire").value, 10),
                id_utilisateur: parseInt(document.getElementById("intervention_utilisateur").value, 10),
                date: document.getElementById('intervention_date').value,
                statut: document.getElementById('intervention_statut').value,
                montant: parseFloat(document.getElementById('intervention_montant').value)
            })
        });
        if (!response.ok){
            const text = await response.text();
            document.getElementById("err").innerHTML = text;
            return;
        }
        const data = await response.json();
        if (data.value == 1) {
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = "gestion_intervention.php?message=" + data.message;
        } else {
            document.getElementById("admin_err").innerHTML = data.message;
        }
    }   

    async function search_intervention() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_intervention/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "GET",
        });
        const data = await response.json();

        document.getElementById("page_title").innerHTML = "Modifier l'intervention " + data.id;
        document.getElementById('admin_title').innerHTML = "Modification de l'intervention " + data.id;
        if(data.id == 0 || !data.id) {
            document.getElementById("resultat").innerHTML = "Aucune intervention trouvée";
        } else {
            document.getElementById("resultat").innerHTML = `
            <form onsubmit="updateIntervention(event)">
            <label>ID :</label>
            <input type="number" name="id" id="intervention_id" value="${data.id}" readonly> Pas modifiable <br><br>
            <label>ID Service :</label>
            <input type="number" name="id_service" id="intervention_service" value="${data.id_service}"><br><br>
            <label>ID Prestataire :</label>
            <input type="number" name="id_prestataire" id="intervention_prestataire" value="${data.id_prestataire}"><br><br>
            <label>ID Utilisateur :</label>
            <input type="number" name="id_utilisateur" id="intervention_utilisateur" value="${data.id_utilisateur}"><br><br>
            <label>Date :</label>
            <input type="datetime-local" name="date" id="intervention_date" value="${data.date.replace(' ', 'T')}"><br><br>
            <label>Statut :</label>
            <input type="text" name="statut" id="intervention_statut" value="${data.statut}"><br><br>
            <label>Montant :</label>
            <input type="number" name="montant" id="intervention_montant" value="${data.montant}" step="0.01"><br><br>
            <button type = "submit">Confirmer les modifications</button>
            </form>
            `;
            }
        }
    search_intervention();
</script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title id ="page_title"></title>
</head>
<body>

<?php include 'header/header.php'?>
<h1 id="admin_title"></h1>
<h2 id ="admin_err"></h2>

<div id="resultat"></div>
<div id="error"></div>
<?php include 'footer/footer.php'?>
</body>
</html>
