<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Paiement annule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="police.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container mt-5" style="max-width:600px;">
    <div class="alert alert-danger" role="alert">
        <h2 class="alert-heading" data-i18n>Paiement annulé</h2>
        <p id="status" data-i18n>Votre paiement a été annulé. Aucun débit n'a été effectué.</p>
        <hr>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-primary" href="checkout.php" data-i18n>Revenir au checkout</a>
            <a class="btn btn-outline-secondary" href="panier.php" data-i18n>Retour panier</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) {
        return;
    }
}

init();
</script>
</body>
</html>
