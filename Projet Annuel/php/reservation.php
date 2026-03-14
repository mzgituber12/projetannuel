<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservation </title>
</head>
<body>

<?php include 'header/header.php' ?>

<h1>Réservation</h1>

<?php if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}
?>

<?php
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nom = isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : '';
$date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : '';
$tarif = isset($_GET['tarif']) ? htmlspecialchars($_GET['tarif']) : '';
$description = isset($_GET['description']) ? htmlspecialchars($_GET['description']) : '';
?>

<?php if ($type === 'evenement' && $id > 0) : ?>
    <div>
        <h2>Événement : <?= $nom ?></h2>
        <p><strong>Date :</strong> <?= $date ?></p>
        <p><strong>Description :</strong></p>
        <p><?= $description?></p>
        <p><strong>Tarif sur place :</strong> <?= $tarif ?> €</p>

        <button id="joinButton">Rejoindre cet événement</button>
    </div>

    <script>
        async function joinEvenement() {
            const token = localStorage.getItem('token');
            if (!token) {
                alert('Vous devez être connecté pour réserver.');
                return;
            }

            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + '/reservation_evenement', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Token': token
                },
                body: JSON.stringify({
                    id_evenement: <?= $id ?>
                })
            });

            const text = await response.text();
            if (!response.ok) {
                alert(text);
                return;
            }

            alert(text);
            window.location.href = 'catalogue.php';
        }

        document.getElementById('joinButton').addEventListener('click', joinEvenement);
    </script>
<?php else : ?>
    <p>Informations d'événement manquantes ou invalides.</p>
<?php endif; ?>

</body>
<?php include 'footer/footer.php'; ?>