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
    async function updateUser(event) {
        event.preventDefault();
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/modifier_user/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "PATCH",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                id: parseInt(document.getElementById("user_id").value, 10),
                nom: document.getElementById('user_nom').value,
                prenom: document.getElementById('user_prenom').value,
                age: parseInt(document.getElementById("user_age").value, 10),
                email: document.getElementById('user_email').value,
                role: document.getElementById('user_role').value,
                langue: document.getElementById('user_langue').value
            })
        });
        if (!response.ok){
            const text = await response.text();
            document.getElementById("err").innerHTML = text;
            return;
        }
        const data = await response.json();
        if (data.value == 1) {
            fetch("ajouter_session_state.php");
            window.location.href = "gestion_user.php?message=" + data.message;
        } else {
            document.getElementById("admin_err").innerHTML = data.message;
        }
    }   

    async function search_user() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_user_id/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "GET",
        });
        const data = await response.json();

        document.getElementById("page_title").innerHTML = "Modifier l'utilisateur " + data.email;
        document.getElementById('admin_title').innerHTML = "Modification de l'utilisateur " + data.email;
        if(data.id == 0) {
            document.getElementById("resultat").innerHTML = "Aucun utilisateur trouvé";
        } else {
            document.getElementById("resultat").innerHTML = `
            <form onsubmit="updateUser(event)">
            <label>ID :</label>
            <input type="number" name="id" id="user_id" value="${data.id}" readonly> Pas modifiable <br><br>
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
            <button type = "submit">Confirmer les modifications</button>
            </form>
            `;
            }
        }
    search_user();
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