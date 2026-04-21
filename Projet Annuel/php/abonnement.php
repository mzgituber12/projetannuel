<?php
include 'includes/api_config.php';
include 'includes/header.php'?>

<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta charset="UTF-8">
    <title data-i18n>Nos Abonnements</title>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center mb-4" data-i18n>Nos Plans d'Abonnement</h1>

    <div id="error-msg" class="alert alert-danger d-none"></div>
    <div id="success-msg" class="alert alert-success d-none"></div>
    <div id="already-subscribed" class="alert alert-info d-none text-center">
        <span data-i18n>Vous avez deja un abonnement actif.</span> <a href="mon_abonnement.php" class="alert-link" data-i18n>Voir mon abonnement</a>
    </div>

    <div class="d-flex justify-content-center gap-2 mb-4">
        <button class="btn btn-primary" id="btn-mois" onclick="setPeriod('mois', this)" data-i18n>Paiement Mensuel</button>
        <button class="btn btn-outline-primary" id="btn-an" onclick="setPeriod('an', this)" data-i18n>Paiement Annuel</button>
    </div>

    <div id="loading" class="text-center d-none">
        <p data-i18n>Chargement des abonnements...</p>
    </div>

    <div class="d-flex justify-content-center gap-4 flex-wrap" id="abonnements-container">
    </div>
</div>


<?php include 'includes/footer.php'?>

<script>
    const token = localStorage.getItem('token');
    const base = (window.API_BASE || 'http://localhost:9000');
    let currentPeriod = 'mois';
    let abonnements = [];
    let dejaabonnee = false;

    async function checkExistingSubscription() {
        if (!token) return;
        try {
            const res = await fetch(base + '/mon-abonnement', {
                method: 'GET',
                headers: { 'Token': token }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.souscription && data.souscription.validite) {
                    dejaabonnee = true;
                    document.getElementById('already-subscribed').classList.remove('d-none');
                    document.getElementById('btn-mois').disabled = true;
                    document.getElementById('btn-an').disabled = true;
                }
            }
        } catch (_) {}
    }

    async function loadAbonnements() {
        document.getElementById('loading').classList.remove('d-none');
        try {
            const response = await fetch(base + '/abonnement', { method: 'GET' });
            if (!response.ok) throw new Error('Erreur ' + response.status);
            const data = await response.json();

            if (Array.isArray(data)) {
                abonnements = data;
            } else if (Array.isArray(data.abonnement)) {
                abonnements = data.abonnement;
            } else {
                abonnements = [];
            }
            displayAbonnements();
        } catch (error) {
            showError('Impossible de charger les abonnements. Veuillez reessayer.');
        } finally {
            document.getElementById('loading').classList.add('d-none');
        }
    }

    function displayAbonnements() {
        const container = document.getElementById('abonnements-container');
        container.innerHTML = '';

        if (!abonnements || abonnements.length == 0) {
            container.innerHTML = '<p class="text-center" data-i18n>Aucun abonnement disponible</p>';
            return;
        }

        abonnements.forEach(abo => {
            const prix = currentPeriod == 'mois' ? abo.prix_mois.toFixed(2) + 'EUR/mois' : abo.prix_an.toFixed(2) + 'EUR/an';

            const card = document.createElement('div');
            card.className = 'card';
            card.style.width = '12rem';
            card.innerHTML = `
                <div class="card-body">
                    <h5 class="card-title text-center">${abo.type}</h5>
                    <p class="card-text text-secondary" data-i18n>A partir de</p>
                    <p class="card-text text-primary">${prix}</p>
                    <div class="text-center mb-3">
                        <button class="btn btn-danger shadow rounded" ${dejaabonnee ? 'disabled' : ''} onclick="subscribePlan(${abo.id})" data-i18n>
                            Choisir
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function setPeriod(period, btn) {
        currentPeriod = period;
        document.getElementById('btn-mois').className = 'btn btn-outline-primary';
        document.getElementById('btn-an').className = 'btn btn-outline-primary';
        btn.className = 'btn btn-primary';
        displayAbonnements();
    }

    async function subscribePlan(abonnementId) {
        if (!token) { window.location.href = 'connexion.php'; return; }
        if (dejaabonnee) { showError('Vous avez deja un abonnement actif.'); return; }

        try {
            const response = await fetch(base + '/subscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Token': token },
                body: JSON.stringify({ id_abonnement: abonnementId, type_paiement: currentPeriod })
            });
            const data = await response.json();
            if (!response.ok) { showError(data.message || 'Erreur lors de la souscription'); return; }
            if (data.checkout_url) {
                showSuccess('Redirection vers Stripe Checkout...');
                setTimeout(() => { window.location.href = data.checkout_url; }, 500);
                return;
            }
            showSuccess('Souscription creee ! Redirection...');
            setTimeout(() => { window.location.href = 'mon_abonnement.php'; }, 1500);
        } catch (error) {
            showError('Erreur reseau: ' + error.message);
        }
    }

    function showError(message) {
        const el = document.getElementById('error-msg');
        el.textContent = message;
        el.classList.remove('d-none');
        setTimeout(() => el.classList.add('d-none'), 5000);
    }

    function showSuccess(message) {
        const el = document.getElementById('success-msg');
        el.textContent = message;
        el.classList.remove('d-none');
    }

    window.onload = async function() {
        await checkExistingSubscription();
        await loadAbonnements();
    };
</script>
</body>
</html>