<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>

<header>
<a class="navbar-brand" href="index.php">
            <i class="bi bi-house fs-2"></i>
        </a>
<nav><div id = "header">
</div></nav>
 </div>
</header>

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
    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Content-Type": "application/json", "Token": token},
    });
    if (!response.ok) {
            document.getElementById("header").innerHTML = "<a href='index.php'>Accueil</a>";
            return
    }
    const data = await response.json();

    if (data.message == "Identifié"){
        document.getElementById("header").innerHTML = "<a href='index.php'>Accueil</a> | <a href='deconnexion.php'>Déconnexion</a> | <a href='mon_profil.php'>Mon profil</a>";
        initOneSignalPush(token);
        if (data.role == "adherant"){
            document.getElementById("header").innerHTML += " | <a href='abonnement.php'>S'abonner</a> | <a href='contrats.php'>Contrats</a> | <a href='conseils.php'>Conseils</a>  | <a href='catalogue.php'>Catalogue</a> | <a href='devis.php'>Devis</a>  | <a href='planning.php'>Planning</a>  | <a href='rendez_vous.php'>Rendez Vous</a>  | <a href='messagerie.php'>Messagerie</a> | <a href='notifications.php'>Notifications</a>";
        } else if (data.role == "prestataire"){
            document.getElementById("header").innerHTML += " | <a href='suivi.php'>Suivi des prestations</a> | <a href='validations.php'>Validations</a>  | <a href='calendrier.php'>Calendrier</a> | <a href='interventions.php'>Interventions</a>  | <a href='factures.php'>Factures</a>  | <a href='rendez_vous.php'>Rendez Vous</a>  | <a href='messagerie.php'>Messagerie</a> | <a href='notifications.php'>Notifications</a>";
        } else if (data.role == "admin"){
            document.getElementById("header").innerHTML += " | <a href='notifications.php'>Notifications</a> | <a href='gestion_user.php'>Gestion des Utilisateur</a> | <a href='gestion_evenement.php'>Gestion des Evenements</a>  | <a href='gestion_service.php'>Gestion des Services</a> | <a href='gestion_intervention.php'>Gestion des Interventions</a> | <a href='gestion_article.php'>Gestion des Articles</a> | <a href='gestion_article.php'>Gestion du Catalogue</a> | <a href='gestion_conseil.php'>Gestion des Conseils</a>  | <a href='gestion_notifs.php'>Gestion des Notifications</a>  | <a href='gestion_finance.php'>Gestion Financiere</a> | <a href='gestion_contact.php'>Gestion des contacts</a>";
        }
    } else if (data.message == "Pas identifié"){
        document.getElementById("header").innerHTML = "<a href='index.php'>Accueil</a> | <a href='inscription.php'>Inscription</a> | <a href='connexion.php'>Connexion</a>";
    }
}
headerUser(localStorage.getItem('token'));
</script>