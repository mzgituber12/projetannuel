?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("online", localStorage.getItem('token')); 
</script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contact</title>
</head>
<body>

<?php include 'header/header.php'; ?>

<h1> Contactez Nous </h1>
<p>Formuler votre demande juste en dessous, nous vous répondrons par mail le plus vite possible !</p>

<div>
    <input type="text" class="form-control" name="text" placeholder="...." required><br><br>
</div>
<button type="submit">Envoyer</button>

</body>
<?php include 'footer/footer.php';?>
</html>