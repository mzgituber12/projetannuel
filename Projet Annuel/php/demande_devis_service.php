<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nom = isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : '';
$description = isset($_GET['description']) ? htmlspecialchars($_GET['description']) : '';
$tarif = isset($_GET['tarif']) ? htmlspecialchars($_GET['tarif']) : '';
$image = isset($_GET['image']) ? trim($_GET['image']) : '';
$imageUrl = $image !== '' ? htmlspecialchars($image) : '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Demande de devis service</title>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-4 mb-4">
    <?php if ($id > 0) : ?>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="h4 card-title mb-3">Demande de devis : <?= $nom ?></h2>
                        <?php if ($imageUrl !== '') : ?>
                            <img src="<?= $imageUrl ?>" alt="Image du service" class="img-fluid rounded mb-3" style="max-width: 100%; max-height: 350px; object-fit: cover;">
                        <?php endif; ?>

                        <p class="card-text"><strong data-i18n>Description :</strong></p>
                        <p class="card-text"><?= $description ?></p>
                        <p class="card-text mb-4"><strong data-i18n>Tarif indicatif :</strong> <span class="text-primary fw-bold"><?= $tarif ?> EUR</span></p>

                        <div class="alert alert-info" role="alert" data-i18n>
                            Cette demande est une estimation de prix uniquement. Aucun rendez-vous ne sera reserve automatiquement.
                        </div>

                        <div id="quoteMessage" class="alert d-none" role="alert"></div>

                        <div class="d-flex gap-2 flex-wrap mt-3">
                            <button id="askQuoteService" class="btn btn-primary btn-lg" data-i18n>Envoyer la demande de devis</button>
                            <a class="btn btn-outline-secondary btn-lg" href="reservation.php?type=service&id=<?= $id ?>&nom=<?= urlencode($nom) ?>&description=<?= urlencode($description) ?>&tarif=<?= urlencode($tarif) ?>&image=<?= urlencode($image) ?>" data-i18n>Aller a la reservation</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const serviceId = <?= $id ?>;

            function showMessage(text, isError) {
                const node = document.getElementById('quoteMessage');
                node.classList.remove('d-none', 'alert-danger', 'alert-success');
                node.classList.add(isError ? 'alert-danger' : 'alert-success');
                node.textContent = text;
            }

            async function askQuoteService() {
                const token = localStorage.getItem('token');
                if (!token) {
                    alert('Vous devez etre connecte pour demander un devis.');
                    return;
                }

                const base = (window.API_BASE || 'http://localhost:9000');
                const resp = await fetch(base + '/creer_devis', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Token': token },
                    body: JSON.stringify({ id_service: serviceId })
                });

                const raw = await resp.text();
                let data = {};
                try { data = JSON.parse(raw); } catch (_) { data = { message: raw }; }

                if (!resp.ok) {
                    showMessage(data.message || 'Erreur lors de la creation du devis.', true);
                    return;
                }

                window.location.href = 'devis.php?message=' + encodeURIComponent(data.message || 'Votre demande de devis a ete envoyee avec succes.');
            }

            async function init() {
                const token = localStorage.getItem('token');
                if (!await loginUser('online', token)) return;
                document.getElementById('askQuoteService').addEventListener('click', askQuoteService);
            }

            init();
        </script>
    <?php else : ?>
        <div class="alert alert-warning" role="alert">
            <strong>Erreur :</strong> Informations de service manquantes ou invalides.
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
