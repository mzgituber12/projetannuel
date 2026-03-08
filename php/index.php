<?php session_start(); include 'api_config.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
</head>
<body>

<?php
include 'header/header.php';

echo "<div class='text-center mt-2 ms-4'><h1>Accueil</div></h1>";
if (isset($_SESSION['state']) && isset($_GET['message'])) { 
    echo "<h3>" . htmlspecialchars($_GET['message']) . "</h3>";
    unset($_SESSION['state']);
}
?>
<h2>Bienvenue sur notre site</h2>
<p><div id=content></div></p>

<?php include 'footer/footer.php'?>

<script>
async function onlineUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Content-Type": "application/json", "Token": token},
    });

    if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
    const data = await response.json();
    if (data.message == "Identifié"){
        document.getElementById("content").innerHTML = "Vous êtes connecté";
        if (data.tutoriel == "1"){
            document.getElementById("content").innerHTML += "</p><p>C'est votre 1ere experience sur le site ? Voici le tutoriel pour vous aider";
        }
    } else if (data.message == "Pas identifié"){
        document.getElementById("content").innerHTML = "Veuillez vous connecter pour poursuivre";
    }
}

async function init(){
        const token = localStorage.getItem("token")
        onlineUser(token)
    }

init()
</script>

</body>
</html>