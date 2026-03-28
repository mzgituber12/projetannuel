<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<header>
<nav class="navbar bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><i class="bi bi-house fs-2"></i></a>
        <div id="non_connecter"></div>
        <ul class="navbar-nav ms-auto d-flex flex-row align-items-center">
            <div id="bouton_des_abonnement"></div>
            <li class="nav-item px-2">|</li>
            <div id="bouton_planning"></div>
            <li class="nav-item px-2">|</li>
            <div id="bouton_messagerie"></div>
            <li class="nav-item px-2">|</li>
            <li class="nav-item">
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasHeader" aria-controls="offcanvasHeader" aria-label="Ouvrir le menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </li>
        </ul>
    </div>
</nav>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasHeader" aria-labelledby="offcanvasHeaderLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasHeaderLabel">Parametres</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
            <div id="mon_compte"></div>
            <div id="autre_bouton"></div>
            <div id="deconnexion_connecter_bouton"></div>
        </ul>
    </div>
</div>
</header>

<div id="deconnexion_connecter"></div>

<script>
window.OneSignalDeferred = window.OneSignalDeferred || [];

async function enregistrerPushSubscription(token, subscriptionId, actif) {
    if (!token || !subscriptionId) return;
    const base = (window.API_BASE || 'http://localhost:9000');
    try {
        await fetch(base + "/push/subscription", {
            method: "POST",
            headers: {"Content-Type": "application/json", "Token": token},
            body: JSON.stringify({ subscription_id: subscriptionId, actif: !!actif })
        });
    } catch (_) {}
}

function initOneSignalPush(token) {
    const appId = (window.ONESIGNAL_APP_ID || "").trim();
    if (!appId || !token) return;

    window.OneSignalDeferred.push(async function(OneSignal) {
        try {
            if (!OneSignal.Notifications.isPushSupported()) {
                return;
            }

            await OneSignal.init({
                appId: appId,
                allowLocalhostAsSecureOrigin: true,
                serviceWorkerPath: "OneSignalSDKWorker.js",
                serviceWorkerParam: { scope: "/" }
            });

            async function syncSubscriptionState() {
                const pushSub = OneSignal.User && OneSignal.User.PushSubscription;
                if (!pushSub) return;

                const subscriptionId = pushSub.id;
                const actif = !!pushSub.optedIn;
                if (subscriptionId) {
                    await enregistrerPushSubscription(token, subscriptionId, actif);
                }
            }

            OneSignal.User.PushSubscription.addEventListener("change", async function(event) {
                const current = event && event.current ? event.current : null;
                if (current && current.id) {
                    await enregistrerPushSubscription(token, current.id, !!current.optedIn);
                }
            });

            if (!OneSignal.Notifications.permission) {
                await OneSignal.Notifications.requestPermission();
            }

            await syncSubscriptionState();
        } catch (_) {}
    });
}

async function headerUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const nonConnecter = document.getElementById("non_connecter");
    const boutonAbonnement = document.getElementById("bouton_des_abonnement");
    const boutonPlanning = document.getElementById("bouton_planning");
    const boutonMessagerie = document.getElementById("bouton_messagerie");
    const monCompte = document.getElementById("mon_compte");
    const autreBouton = document.getElementById("autre_bouton");
    const deconnexionBouton = document.getElementById("deconnexion_connecter_bouton");

    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Content-Type": "application/json", "Token": token},
    });

    if (!response.ok) {
        nonConnecter.innerHTML = "<ul class='navbar-nav ms-auto d-flex flex-row gap-2 align-items-center'><li class='nav-item'><a class='nav-link active' href='inscription.php'>Inscription</a></li><li class='nav-item'><a class='nav-link active' href='connexion.php'>Connexion</a></li></ul>";
        return;
    }

    const data = await response.json();

    if (data.message == "Pas identifié") {
        nonConnecter.innerHTML = "<ul class='navbar-nav ms-auto d-flex flex-row gap-2 align-items-center'><li class='nav-item'><a class='nav-link active' href='inscription.php'>Inscription</a></li><li class='nav-item'><a class='nav-link active' href='connexion.php'>Connexion</a></li></ul>";
        return;
    }

    initOneSignalPush(token);

    monCompte.innerHTML = "<li class='nav-item'><a class='nav-link active' href='mon_profil.php'><i class='bi bi-person'></i> Mon profil</a></li>";
    deconnexionBouton.innerHTML = "<li class='nav-item'><a class='nav-link text-danger' href='deconnexion.php'><i class='bi bi-box-arrow-right'></i> Deconnexion</a></li>";

    if (data.role == "adherant") {
        boutonAbonnement.innerHTML = "<li class='nav-item'><a class='nav-link active' href='abonnement.php'>S'abonner</a></li>";
        boutonPlanning.innerHTML = "<li class='nav-item'><a class='nav-link active' href='planning.php'>Planning</a></li>";
        boutonMessagerie.innerHTML = "<li class='nav-item'><a class='nav-link active' href='messagerie.php'><i class='bi bi-chat-dots'></i> Messagerie</a></li>";
        autreBouton.innerHTML = "<li class='nav-item'><a class='nav-link active' href='contrats.php'>Contrats</a></li><li class='nav-item'><a class='nav-link active' href='conseils.php'>Conseils</a></li><li class='nav-item'><a class='nav-link active' href='catalogue.php'>Catalogue</a></li><li class='nav-item'><a class='nav-link active' href='devis.php'>Devis</a></li><li class='nav-item'><a class='nav-link active' href='rendez_vous.php'>Rendez Vous</a></li><li class='nav-item'><a class='nav-link active' href='demande_presta.php'>Postuler</a></li><li class='nav-item'><a class='nav-link active' href='notifications.php'>Notifications</a></li>";
        return;
    }

    if (data.role == "prestataire") {
        boutonAbonnement.innerHTML = "<li class='nav-item'><a class='nav-link active' href='abonnement.php'>Nos abonnements</a></li>";
        boutonPlanning.innerHTML = "<li class='nav-item'><a class='nav-link active' href='planning.php'>Planning</a></li>";
        boutonMessagerie.innerHTML = "<li class='nav-item'><a class='nav-link active' href='messagerie.php'><i class='bi bi-chat-dots'></i> Messagerie</a></li>";
        autreBouton.innerHTML = "<li class='nav-item'><a class='nav-link active' href='suivi.php'>Suivi des prestations</a></li><li class='nav-item'><a class='nav-link active' href='validations.php'>Validations</a></li><li class='nav-item'><a class='nav-link active' href='calendrier.php'>Calendrier</a></li><li class='nav-item'><a class='nav-link active' href='interventions.php'>Interventions</a></li><li class='nav-item'><a class='nav-link active' href='factures.php'>Factures</a></li><li class='nav-item'><a class='nav-link active' href='rendez_vous.php'>Rendez Vous</a></li><li class='nav-item'><a class='nav-link active' href='notifications.php'>Notifications</a></li>";
        return;
    }

    if (data.role == "admin") {
        boutonMessagerie.innerHTML = "<li class='nav-item'><a class='nav-link active' href='notifications.php'><i class='bi bi-bell'></i> Notifications</a></li>";
        autreBouton.innerHTML = "<li class='nav-item'><a class='nav-link active' href='gestion_user.php'>Gestion des Utilisateur</a></li><li class='nav-item'><a class='nav-link active' href='gestion_evenement.php'>Gestion des Evenements</a></li><li class='nav-item'><a class='nav-link active' href='gestion_service.php'>Gestion des Services</a></li><li class='nav-item'><a class='nav-link active' href='gestion_intervention.php'>Gestion des Interventions</a></li><li class='nav-item'><a class='nav-link active' href='gestion_article.php'>Gestion des Articles</a></li><li class='nav-item'><a class='nav-link active' href='gestion_article.php'>Gestion du Catalogue</a></li><li class='nav-item'><a class='nav-link active' href='gestion_conseil.php'>Gestion des Conseils</a></li><li class='nav-item'><a class='nav-link active' href='gestion_notifs.php'>Gestion des Notifications</a></li><li class='nav-item'><a class='nav-link active' href='gestion_finance.php'>Gestion Financiere</a></li><li class='nav-item'><a class='nav-link active' href='gestion_contact.php'>Gestion des contacts</a></li><li class='nav-item'><a class='nav-link active' href='add_abonnement.php'>Creer des abonnements</a></li><li class='nav-item'><a class='nav-link active' href='abonnement_all.php'>Voir tous les abonnements</a></li><li class='nav-item'><a class='nav-link active' href='liste_abonnement_admin.php'>Liste abonnements admin</a></li>";
    }
}
headerUser(localStorage.getItem('token'));
</script>