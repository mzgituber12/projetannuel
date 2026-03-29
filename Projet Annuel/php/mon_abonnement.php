<?php
session_start();
 include 'includes/api_config.php';
 include 'includes/header.php'
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Abonnement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5 mb-5" style="max-width: 800px;">
        <h1 class="mb-4">Mon Abonnement</h1>

        <div id="content">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2">Chargement...</p>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php") ?>

    <script>
        const token = localStorage.getItem('token');
        const base = (window.API_BASE || 'http://localhost:9000');

        if (!token) {
            window.location.href = 'connexion.php';
        }

        fetch(base + '/mon-abonnement', {
            method: 'GET',
            headers: {
                'Token': token
            }
        })
        .then(async response => {
            if (response.status === 404) {
                return { no_subscription: true };
            }
            if (!response.ok) {
                let message = 'Erreur lors du chargement';
                try {
                    const errData = await response.json();
                    if (errData && errData.message) {
                        message = errData.message;
                    }
                } catch (_) {}
                throw new Error(message + ' (HTTP ' + response.status + ')');
            }
            return response.json();
        })
        .then(data => {
            const content = document.getElementById('content');

            if (data.no_subscription) {
                content.innerHTML = `
                    <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">Vous n'avez pas encore d'abonnement actif</h4>
                        <p>Découvrez nos plans d'abonnement et profitez de nos services premium.</p>
                        <a href="abonnement.php" class="btn btn-primary mt-3">Voir les plans</a>
                    </div>
                `;
                return;
            }

            const sub = data.souscription;
            const abo = data.abonnement;

            const dateStart = new Date(sub.date_souscription).toLocaleDateString('fr-FR');
            const dateExpiration = sub.date_expiration ? new Date(sub.date_expiration).toLocaleDateString('fr-FR') : 'Illimité';
            const prixMois = abo.prix_mois.toFixed(2);
            const prixAn = abo.prix_an.toFixed(2);
            const typePaiement = sub.type_paiement == 'mois' ? 'Mensuel' : 'Annuel';

            let badgeClass, statusText;
            if (sub.validite) {
                badgeClass = 'bg-success';
                statusText = '✓ Actif';
            } else if (!sub.stripe_subscription_id || sub.stripe_subscription_id == '') {
                badgeClass = 'bg-warning';
                statusText = '⏳ En attente de confirmation paiement';
            } else {
                badgeClass = 'bg-danger';
                statusText = '✗ Inactif';
            }

            content.innerHTML = `
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h2 class="card-title">${abo.type}</h2>
                            <span class="badge ${badgeClass}">${statusText}</span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Plan:</p>
                                <p class="fs-6">${typePaiement}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Prix:</p>
                                <p class="fs-6"><strong>${sub.type_paiement === 'mois' ? prixMois + '€/mois' : prixAn + '€/an'}</strong></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Date de souscription:</p>
                                <p class="fs-6">${dateStart}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Expiration:</p>
                                <p class="fs-6">${dateExpiration}</p>
                            </div>
                        </div>

                        ${(sub.validite || sub.stripe_subscription_id) ? `
                            <div class="d-grid gap-2">
                                <button class="btn btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir annuler votre abonnement ?')) cancelSubscription()">
                                    Annuler l'abonnement
                                </button>
                            </div>
                        ` : `
                            <div class="text-center">
                                <a href="abonnement.php" class="btn btn-primary btn-lg">
                                    Souscrire à un plan
                                </a>
                            </div>
                        `}
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('content').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Erreur</h4>
                    <p>${error.message || 'Une erreur est survenue lors du chargement de votre abonnement.'}</p>
                </div>
            `;
        });

        function cancelSubscription() {
            fetch(base + '/cancel-subscription', {
                method: 'POST',
                headers: {
                    'Token': token,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'annulation');
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
