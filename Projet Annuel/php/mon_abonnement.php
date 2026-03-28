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
    <style>
        .subscription-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .subscription-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .subscription-type {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .subscription-status {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .subscription-status.active {
            color: #4ade80;
        }

        .subscription-status.inactive {
            color: #f87171;
        }

        .subscription-details {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .detail-label {
            font-weight: 600;
        }

        .detail-value {
            color: #e0e7ff;
        }

        .subscription-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-manage {
            background: white;
            color: #667eea;
        }

        .btn-manage:hover {
            background: #f8f9fa;
        }

        .btn-cancel {
            background: #ef4444;
            color: white;
        }

        .btn-cancel:hover {
            background: #dc2626;
        }

        .no-subscription {
            text-align: center;
            padding: 50px;
            background: #f5f5f5;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .no-subscription h2 {
            color: #666;
            margin-bottom: 20px;
        }

        .btn-subscribe {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-subscribe:hover {
            background: #764ba2;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .loading {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="subscription-container">
        <h1>Mon Abonnement</h1>

        <div id="content">
            <div class="loading">
                <div class="spinner"></div>
                <p>Chargement...</p>
            </div>
        </div>
    </div>

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
                    <div class="no-subscription">
                        <h2>Vous n'avez pas encore d'abonnement actif</h2>
                        <p>Découvrez nos plans d'abonnement et profitez de nos services premium.</p>
                        <a href="abonnement.php" class="btn-subscribe">Voir les plans</a>
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

            let statusClass, statusText;
            if (sub.validite) {
                statusClass = 'active';
                statusText = '✓ Actif';
            } else if (!sub.stripe_subscription_id || sub.stripe_subscription_id == '') {
                statusClass = 'inactive';
                statusText = '⏳ En attente de confirmation paiement';
            } else {
                statusClass = 'inactive';
                statusText = '✗ Inactif';
            }

            content.innerHTML = `
                <div class="subscription-card">
                    <div class="subscription-type">${abo.type}</div>
                    <div class="subscription-status ${statusClass}">
                        ${statusText}
                    </div>

                    <div class="subscription-details">
                        <div class="detail-row">
                            <span class="detail-label">Plan:</span>
                            <span class="detail-value">${typePaiement}</span>
                        </div>

                        <div class="detail-row">
                            <span class="detail-label">Prix:</span>
                            <span class="detail-value">
                                ${sub.type_paiement === 'mois' ? prixMois + '€/mois' : prixAn + '€/an'}
                            </span>
                        </div>

                        <div class="detail-row">
                            <span class="detail-label">Date de souscription:</span>
                            <span class="detail-value">${dateStart}</span>
                        </div>

                        <div class="detail-row">
                            <span class="detail-label">Expiration:</span>
                            <span class="detail-value">${dateExpiration}</span>
                        </div>
                    </div>

                    ${(sub.validite || sub.stripe_subscription_id) ? `
                        <div class="subscription-actions">
                            <button class="btn btn-cancel" onclick="if(confirm('Êtes-vous sûr de vouloir annuler votre abonnement ?')) cancelSubscription()">
                                Annuler l'abonnement
                            </button>
                        </div>
                    ` : `
                        <div style="margin-top: 20px; text-align: center;">
                            <a href="abonnement.php" class="btn-subscribe" style="display:inline-block; padding:12px 30px; background:white; color:#667eea; border-radius:5px; text-decoration:none; font-weight:600;">
                                Souscrire à un plan
                            </a>
                        </div>
                    `}
                </div>
            `;
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('content').innerHTML = `
                <div class="error-message">
                    ${error.message || 'Une erreur est survenue lors du chargement de votre abonnement.'}
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
</body>
</html>
