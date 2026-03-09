<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
</head>
<body>

<?php include 'header/header.php'?>

<h1>Gestion des utilisateurs</h1>
<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>
<h4>Entrer un email pour avoir toutes les informations !</h4>
<form onsubmit="search_user(event)">
    <input id = "user_email" placeholder="..." type="text">
    <button type = "submit">Rechercher</button>
</form>
<div id="resultat"></div>

<?php include 'footer/footer.php'?>

<script>
    async function search_user(event) {
        event.preventDefault();
        const email = document.getElementById("user_email").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_user_email/" + email, {
            method: "GET",
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();

        if(data.id == 0) {
            document.getElementById("resultat").innerHTML = "Aucun utilisateur trouvé";
        }else {
            document.getElementById("resultat").innerHTML = 
            "<label>ID : " + data.id + "</label><br>" +
            "<label>Nom : " + data.nom + "</label><br>" +
            "<label>Prénom : " + data.prenom + "</label><br>" +
            "<label>Âge : " + data.age + "</label><br>" +
            "<label>Email : " + data.email + "</label><br>" +
            "<label>Role : " + data.role + "</label><br>" +
            "<label>Langue : " + data.langue + "</label><br>" +
            "<a href='modifier_user.php?id=" + data.id + "'>Modifier l'utilisateur</a>";
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
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