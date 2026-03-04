<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("offline", localStorage.getItem('token')); 
</script>

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
        <?php
        if (isset($_SESSION['state']) && isset($_GET['message'])) {
            echo "<h5 class='text-center text-danger mb-3'>" . htmlspecialchars($_GET['message']) . "</h5>";
            unset($_SESSION['state']);
            }
            ?>
            <form method="post" action="connexion_traitement.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse Email</label>
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control bg-white" name="password" placeholder="Mot de passe" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
        <p class="mt-3 text-center">
          Pas encore de compte ? <a href="inscription.php">Inscrivez-vous</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include 'footer/footer.php';?>
</html>