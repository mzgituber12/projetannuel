<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Contact</title>
    <link rel="stylesheet" href="police.css">
    <style>
        .mb-custom {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <h1 class="text-center mb-5" data-i18n> Contactez Nous </h1>
    
    <h2 id="geturl"></h2>
    <p data-i18n class="mb-5 fs-4">Formuler votre demande juste en dessous, nous vous répondrons par mail le plus vite possible !</p>

    <form onsubmit="submit_ask(event, localStorage.getItem('token'))">
        <p id="nom">Votre Prenom et Nom : </p>
        <p id ="email">Votre Adresse Email : </p>
        <p>Votre Message : </p>
        <textarea 
        class="form-control mb-custom" 
        name="text" 
        id="demand" 
        placeholder="..." 
        rows="5"
        required
        ></textarea>

    <button type="submit" class="btn btn-primary" data-i18n>Envoyer</button>
    </form>
</div>

<?php include 'includes/footer.php';?>

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

async function add_mailanduser(token){
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/mon_profil", {
        method: "GET",
        headers: {
            "Content-Type": "application/json", 
            "Token": token
        },
    });

    if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
    }

    const data = await response.json();
    document.getElementById("nom").innerHTML += data.prenom + " " + data.nom
    document.getElementById("email").innerHTML += data.email
}

async function init(){
        const token = localStorage.getItem("token")
        loginUser("online", token); 
        add_mailanduser(token)
    }

init()

</script>

</body>
</html>
