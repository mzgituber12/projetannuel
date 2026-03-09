<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

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
<?php include 'footer/footer.php'?>

<script>
    async function updateEvenement(event) {
        event.preventDefault();

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/modifier_evenement/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "PATCH",
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

    async function search_evenement() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_evenement_id/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "GET",
        });

        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

        const data = await response.json();
        document.getElementById("page_title").innerHTML = "Modifier l'événement " + data.nom;
        document.getElementById('admin_title').innerHTML = "Modification de l'événement " + data.nom;
        if(data.id == 0) {
            document.getElementById("resultat").innerHTML = "Aucun événement trouvé";
        } else {
            document.getElementById("resultat").innerHTML = `
            <form onsubmit="updateEvenement(event)">
            <label>ID :</label>
            <input type="number" name="id" id="event_id" value="${data.id}" readonly> Pas modifiable <br><br>
            <label>Nom :</label>
            <input type="text" name="nom" id="event_nom" value="${data.nom}"><br><br>
            <label>Date :</label>
            <input type="datetime-local" step="60" name="date" id="event_date" value="${data.date}"><br><br>
            <label>Description :</label>
            <input type="text" name="description" id="event_description" value="${data.description}"><br><br>
            <label>Tarif :</label>
            <input type="number" name="tarif" id="event_tarif" value="${data.tarif}"><br><br>
            <button type = "submit">Confirmer les modifications</button>
            </form>
            `;
            }
        }

    async function init(){
        const token = localStorage.getItem("token")
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        search_evenement()
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