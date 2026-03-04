<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("offline", localStorage.getItem('token')); 
</script>

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

<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>

<form method="post" action="inscription_traitement.php">
    <div class="mb-3">  
        <label for="email" class="form-label">Adresse Email</label>
        <input type="email" class="form-control" name="email" placeholder="Email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="for-label">Mot de passe</label>
        <input type="password" class="form-control bg-white" name="password" placeholder="Mot de passe" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
</form>
<p class="mt-3 text-center">Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

</body>
<?php include 'footer/footer.php';?>
</html>