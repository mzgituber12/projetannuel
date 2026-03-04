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
    async function search_user() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_user_id/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "GET",
        });
        const data = await response.json();

        if(document.getElementById("page_title").innerHTML != "Accès refusé"){
            document.getElementById("page_title").innerHTML = "Modifier l'utilisateur " + data.email;
            document.getElementById('admin_title').innerHTML = "Modification de l'utilisateur " + data.email;
            if(data.id == 0) {
                document.getElementById("resultat").innerHTML = "Aucun utilisateur trouvé";
            } else {
                document.getElementById("resultat").innerHTML = `
                <label>ID :</label>
                <input type="text" name="id" id="user_id" value="${data.id}" readonly><br><br>
                <label>Nom :</label>
                <input type="text" name="nom" id="user_nom" value="${data.nom}"><br><br>
                <label>Prénom :</label>
                <input type="text" name="prenom" id="user_prenom" value="${data.prenom}"><br><br>
                <label>Âge :</label>
                <input type="number" name="age" id="user_age" value="${data.age}"><br><br>
                <label>Email :</label>
                <input type="email" name="email" id="user_email" value="${data.email}"><br><br>
                <label>Role :</label>
                <input type="text" name="role" id="user_role" value="${data.role}"><br><br>
                <label>Langue :</label>
                <input type="text" name="langue" id="user_langue" value="${data.langue}"><br><br>
                <a href="modifier_user_traitement.php?id=${data.id}" id="submit_link">Confirmer les modifications</a>
                `;
            }
        }
    }
    search_user();
</script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title id="page_title"></title>
</head>
<body>

<?php include 'header/header.php'?>
<div id ="admin">

<h1 id="admin_title"></h1>

<div id="resultat"></div>
</div>
<?php include 'footer/footer.php'?>
</body>
</html>