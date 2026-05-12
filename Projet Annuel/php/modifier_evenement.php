<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title id ="page_title"></title>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class='container mt-5'>
<h1 id="admin_title" data-i18n class='mb-custom text-center ms-4' style='font-size:50px'></h1>
<h2 id ="admin_err" class="mt-4"></h2>

<div id="resultat" class="mt-custom p-3 pb-1 border rounded bg-light"></div>
</div>

<div class="mt-4"></div>
<?php include 'includes/footer.php'?>

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
                lieu: document.getElementById('event_lieu').value,
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
            <label data-i18n>ID :</label>
            <input type="number" name="id" id="event_id" value="${data.id}" readonly> <span data-i18n>Pas modifiable</span> <br><br>
            <label data-i18n>Nom :</label>
            <input type="text" name="nom" id="event_nom" value="${data.nom}" required><br><br>
            <label data-i18n>Date :</label>
            <input type="datetime-local" step="60" name="date" id="event_date" value="${data.date}"><br><br>
            <div class="mb-3">
            <label for="event_description" class="form-label">Description</label>
            <textarea class="form-control" id="event_description" rows="4" required>${data.description}</textarea>
            </div>
            <label data-i18n>Lieu :</label>
            <input type="text" name="lieu" id="event_lieu" value="${data.lieu}" required><br><br>
            <label data-i18n>Tarif :</label>
            <input type="number" name="tarif" id="event_tarif" value="${data.tarif}" required><br><br>
            <button type="submit" class="btn btn-primary w-100">
                Confirmer les modifications
            </button>
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
