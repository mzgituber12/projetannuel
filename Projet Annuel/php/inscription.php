<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/header.php';?>
<div class="d-flex justify-content-center align-items-center mt-4 mb-4" style="min-height: 80vh;">
<div class="card p-4 shadow-sm w-100" style="max-width: 400px;">
<h2 class="text-center mb-4">Inscription</h2>
<h2 id="incorrect"></h2>

<form onsubmit="signupUser(event, document.getElementById('prenom').value, document.getElementById('nom').value, document.getElementById('date_naissance').value, document.getElementById('email').value, document.getElementById('password').value)">
    <div class="mb-3">  
        <label for="email" class="form-label">Prénom</label>
        <input type="text" class="form-control" id="prenom" placeholder="Prenom" required>
    </div>
    <div class="mb-3">  
        <label for="email" class="form-label">Nom</label>
        <input type="text" class="form-control" id="nom" placeholder="Nom" required>
    </div>


    <div class="mb-3">
        <label for="date" class="form-label">Date de naissance</label>
         <input type ="date" class="form-control" id="date_naissance" required>
    </div>



    <div class="mb-3">  
        <label for="email" class="form-label">Adresse Email</label>
        <input type="email" class="form-control" id="email" placeholder="Email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="for-label">Mot de passe</label>
        <input type="password" class="form-control bg-white" id="password" placeholder="Password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
</form>
<p class="mt-3 text-center">Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'includes/footer.php';?>

<script>
    async function signupUser(event, prenom, nom, date_naissance, email, password) {
        event.preventDefault();


        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/inscription", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({prenom: prenom, nom: nom, date_naissance: date_naissance, email: email, password: password})
        });

        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

        const data = await response.json();
        if (!data.token || data.token == "") {
        document.getElementById("incorrect").innerHTML = data.message;
        } else {
            localStorage.setItem('token', data.token);
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = "index.php?message=" + encodeURIComponent(data.message);
        }
    }

async function init(){
        const token = localStorage.getItem("token")
        loginUser("offline", token); 
    }

init()
</script>

<script>
const aujourdhui = new Date();
const anneeLimite = aujourdhui.getFullYear() - 18;
const mois = String(aujourdhui.getMonth() + 1).padStart(2, '0');
const jour = String(aujourdhui.getDate()).padStart(2, '0');

const dateMax = `${anneeLimite}-${mois}-${jour}`;

document.getElementById('date_naissance').setAttribute('max', dateMax);

window.onload
</script>

</body>
</html>
