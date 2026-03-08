<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'header/header.php'; ?>

<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4 shadow-sm w-100" style="max-width: 400px;">
        <h2 class="text-center mb-4">Connexion</h2>
        <h3 class='text-center text-danger mb-3' id = "incorrect"></h3>
            <form method="post" onsubmit="signinUser(document.getElementById('email').value, document.getElementById('password').value,)">
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse Email</label>
                    <input type="email" class="form-control" id="email" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control bg-white" id="password" placeholder="Mot de passe" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
        <p class="mt-3 text-center">
          Pas encore de compte ? <a href="inscription.php">Inscrivez-vous</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer/footer.php';?>

<script>
async function signinUser(email, password) {
    event.preventDefault();

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/connexion", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({email: email, password: password})
    })

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