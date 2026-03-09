<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'header/header.php';?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
<div class="card p-4 shadow-sm w-100" style="max-width: 400px;">
<h2 class="text-center mb-4">Inscription</h2>
<h2 id="incorrect"></h2>

<form onsubmit="signupUser(document.getElementById('prenom').value, document.getElementById('nom').value, parseInt(document.getElementById('age').value, 10), document.getElementById('email').value, document.getElementById('password').value)">
    <div class="mb-3">  
        <label for="email" class="form-label">Prénom</label>
        <input type="text" class="form-control" id="prenom" placeholder="Prenom" required>
    </div>
    <div class="mb-3">  
        <label for="email" class="form-label">Nom</label>
        <input type="text" class="form-control" id="nom" placeholder="Nom" required>
    </div>
    <div class="mb-3">  
        <label for="email" class="form-label">Age</label>
        <input type="number" min="18" class="form-control" id="age" placeholder="70" required>
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

<?php include 'footer/footer.php';?>

<script>
    async function signupUser(prenom, nom, age, email, password) {
        event.preventDefault()

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/inscription", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({prenom: prenom, nom: nom, age: age, email: email, password: password})
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

</body>
</html>