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
    <title data-i18n>Mon Abonnement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5 mb-5" style="max-width: 800px;">
        <h1 class="mb-4" data-i18n>Mon Abonnement</h1>

        <div id="content">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2" data-i18n>Chargement...</p>
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
                        <h4 class="alert-heading" data-i18n>Vous n'avez pas encore d'abonnement actif</h4>
                        <p data-i18n>Découvrez nos plans d'abonnement et profitez de nos services premium.</p>
                        <a href="abonnement.php" class="btn btn-primary mt-3" data-i18n>Voir les plans</a>
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
                            <h2 class="card-title" data-i18n>${abo.type}</h2>
                            <span class="badge ${badgeClass}" data-i18n>${statusText}</span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-muted mb-1" data-i18n>Plan:</p>
                                <p class="fs-6" data-i18n>${typePaiement}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1" data-i18n>Prix:</p>
                                <p class="fs-6" data-i18n><strong>${sub.type_paiement === 'mois' ? prixMois + '€/mois' : prixAn + '€/an'}</strong></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="text-muted mb-1" data-i18n>Date de souscription:</p>
                                <p class="fs-6" data-i18n>${dateStart}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1" data-i18n>Expiration:</p>
                                <p class="fs-6" data-i18n>${dateExpiration}</p>
                            </div>
                        </div>

                        ${(sub.validite || sub.stripe_subscription_id) ? `
                            <div class="d-grid gap-2">
                                <button class="btn btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir annuler votre abonnement ?')) cancelSubscription()" data-i18n>
                                    Annuler l'abonnement
                                </button>
                            </div>
                        ` : `
                            <div class="text-center">
                                <a href="abonnement.php" class="btn btn-primary btn-lg" data-i18n>
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
                    <h4 class="alert-heading" data-i18n>Erreur</h4>
                    <p data-i18n>${error.message || 'Une erreur est survenue lors du chargement de votre abonnement.'}</p>
                </div>
            `;
        });

        function initActivationPush() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('checkout') !== 'success') return;

            let pushed = false;
            let shouldRetryOnSubscribed = false;
            const sendActivationPush = async () => {
                if (pushed) return;
                pushed = true;
                const token = localStorage.getItem('token');
                if (!token) return;
                try {
                    const res = await fetch(base + '/abonnement/notif-push-bienvenue', {
                        method: 'POST',
                        headers: { 'Token': token }
                    });
                    const data = await res.json().catch(() => ({}));
                    shouldRetryOnSubscribed = Number(data.value || 0) !== 1;
                } catch (_) {
                    shouldRetryOnSubscribed = true;
                }
            };

            sendActivationPush();

            window.addEventListener('onesignal:subscribed', function() {
                if (!shouldRetryOnSubscribed) return;
                pushed = false;
                sendActivationPush();
            }, { once: true });
        }

        initActivationPush();

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
