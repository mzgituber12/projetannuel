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
    async function updateService(event) {
        event.preventDefault();
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/modifier_service/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "PATCH",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                id: parseInt(document.getElementById("service_id").value, 10),
                nom: document.getElementById('service_nom').value,
                description: document.getElementById('service_description').value,
                tarif: parseFloat(document.getElementById('service_tarif').value)
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
            window.location.href = "gestion_service.php?message=" + data.message;
        } else {
            document.getElementById("admin_err").innerHTML = data.message;
        }
    }   

    async function search_service() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_service_id/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "GET",
        });
        const data = await response.json();

        document.getElementById("page_title").innerHTML = "Modifier le service " + data.nom;
        document.getElementById('admin_title').innerHTML = "Modification du service " + data.nom;
        if(data.id == 0 || !data.id) {
            document.getElementById("resultat").innerHTML = "Aucun service trouvÃ©";
        } else {
            document.getElementById("resultat").innerHTML = `
            <form onsubmit="updateService(event)">
            <label>ID :</label>
            <input type="number" name="id" id="service_id" value="${data.id}" readonly> Pas modifiable <br><br>
            <label>Nom :</label>
            <input type="text" name="nom" id="service_nom" value="${data.nom}"><br><br>
            <label>Description :</label>
            <textarea name="description" id="service_description">${data.description}</textarea><br><br>
            <label>Tarif :</label>
            <input type="number" name="tarif" id="service_tarif" value="${data.tarif}" step="0.01"><br><br>
            <button type = "submit">Confirmer les modifications</button>
            </form>
            `;
            }
        }
    search_service();
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