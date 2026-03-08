<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contact</title>
</head>
<body>

<?php include 'header/header.php'; ?>

<h1> Contactez Nous </h1>
<h2 id="geturl"></h2>
<p>Formuler votre demande juste en dessous, nous vous répondrons par mail le plus vite possible !</p>

<form onsubmit="submit_ask(event, localStorage.getItem('token'))">
    <input class="form-control" name="text" id="demand" placeholder="..." type="text" required>
    <button type="submit">Envoyer</button>
</form>

<?php include 'footer/footer.php';?>

<script> async function submit_ask(event, token) {
    event.preventDefault();

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/nous_contacter", {
        method: "POST",
        headers: {
            "Content-Type": "application/json", 
            "Token": token
        },
        body: JSON.stringify({
            "message": document.getElementById("demand").value,
        })
    });

    if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
    const data = await response.json();
    if (data.message == "Message envoyé avec succès, nous vous répondrons dans les plus brefs délais.") {
        await fetch("ajouter_session_state.php", {method: "POST"});
        window.location.href = "index.php?message=" + data.message;
    } else {
        document.getElementById("geturl").innerHTML = data.message;
    }
}

async function init(){
        const token = localStorage.getItem("token")
        loginUser("online", token); 
    }

init()

</script>

</body>
</html>