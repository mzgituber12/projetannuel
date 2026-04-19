<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<header>
<nav class="navbar bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><i class="bi bi-house fs-2"></i></a>
        <div id="controle_zoom" class="d-flex align-items-center gap-1 me-2">
            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="zoomOut()" aria-label="Reduire le zoom">-</button>
            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="zoomIn()" aria-label="Augmenter le zoom">+</button>
        </div>
        <div id="langue_index_badge" class="me-2"></div>
        <div id="non_connecter"></div>
        <ul class="navbar-nav ms-auto d-flex flex-row align-items-center">
            <div id="bouton_des_abonnement"></div>
            <li class="nav-item px-2">|</li>
            <div id="bouton_planning"></div>
            <li class="nav-item px-2">|</li>
            <div id="bouton_messagerie"></div>
            <li class="nav-item px-2">|</li>
            <div id="bouton_boutique"></div>
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
        <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-3">
            <span data-i18n>Loupe texte</span>
            <button id="loupe_toggle_btn" type="button" class="btn btn-sm btn-outline-secondary" aria-pressed="false" data-i18n>Activer</button>
        </div>
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
const _i18nCache = {};
let zoomPage = Number(localStorage.getItem('zoom_page')) || 100;
let loupeActive = localStorage.getItem('loupe_active') === '1';
let loupeTarget = null;

const LOUPE_EXCLUDED_TAGS = ['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEMPLATE', 'INPUT', 'TEXTAREA', 'SELECT', 'OPTION', 'CODE', 'PRE', 'HTML', 'BODY', 'HEADER', 'NAV'];
const LOUPE_ALLOWED_SELECTOR = 'p,span,a,li,h1,h2,h3,h4,h5,h6,label,strong,em,small,td,th,dt,dd,button';

function getLoupeElement() {
    let lens = document.getElementById('text_loupe_lens');
    if (lens) return lens;

    lens = document.createElement('div');
    lens.id = 'text_loupe_lens';
    lens.setAttribute('aria-hidden', 'true');
    lens.className = 'bg-white border border-primary rounded p-3 fs-5 fw-bold text-dark-emphasis';
    Object.assign(lens.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        zIndex: '9999',
        width: '300px',
        maxHeight: '220px',
        overflowY: 'auto',
        pointerEvents: 'none',
        boxShadow: '0 10px 20px rgba(0,0,0,0.16)',
        display: 'none',
        whiteSpace: 'normal'
    });
    document.body.appendChild(lens);
    return lens;
}

function syncLoupeButton() {
    const btn = document.getElementById('loupe_toggle_btn');
    if (!btn) return;
    btn.dataset.i18nSrc = loupeActive ? 'Desactiver' : 'Activer';
    btn.textContent = loupeActive ? 'Desactiver' : 'Activer';
    btn.setAttribute('aria-pressed', loupeActive ? 'true' : 'false');
    btn.classList.toggle('btn-outline-secondary', !loupeActive);
    btn.classList.toggle('btn-primary', loupeActive);
    if (window._lang && window._lang !== 'fr') {
        TraductionI18n(window._lang);
    }
}

function hideLoupe() {
    const lens = document.getElementById('text_loupe_lens');
    if (lens) lens.style.display = 'none';
}

function clearLoupeTarget() {
    if (loupeTarget) {
        loupeTarget.classList.remove('loupe-active-target');
    }
    loupeTarget = null;
}

function canUseLoupeElement(el) {
    if (!el || el.nodeType !== 1) return false;
    if (LOUPE_EXCLUDED_TAGS.includes(el.tagName)) return false;
    if (el.closest('header')) return false;
    const text = (el.innerText || '').trim();
    return text.length > 0;
}

function getLoupeTextElement(fromElement) {
    if (!fromElement) return null;
    if (fromElement.matches && fromElement.matches(LOUPE_ALLOWED_SELECTOR)) {
        return fromElement;
    }
    return fromElement.closest ? fromElement.closest(LOUPE_ALLOWED_SELECTOR) : null;
}

function sanitizeLoupeText(rawText) {
    const text = String(rawText || '').replace(/\s+/g, ' ').trim();
    if (!text) return '';
    
    return text.length > 100 ? text.slice(0, 100) + '…' : text;
}

function adaptLoupeSize(lens, element, text) {
    const rect = element.getBoundingClientRect();
    const textLength = String(text || '').trim().length;

    const minWidth = 260;
    const maxWidth = Math.max(320, Math.floor(window.innerWidth * 0.7));
    const rectBasedWidth = Math.ceil(rect.width * 1.40);
    const textBasedWidth = Math.min(maxWidth, 200 + Math.ceil(textLength * 1.5));
    const nextWidth = Math.min(maxWidth, Math.max(minWidth, rectBasedWidth, textBasedWidth));

    const minHeight = 120;
    const maxHeight = Math.max(180, Math.floor(window.innerHeight * 0.45));
    const rectBasedHeight = Math.ceil(rect.height * 1.7);
    const estimatedLineCount = Math.ceil(textLength / 45);
    const textBasedHeight = 70 + (estimatedLineCount * 32);
    const nextHeight = Math.min(maxHeight, Math.max(minHeight, rectBasedHeight, textBasedHeight));

    lens.style.width = nextWidth + 'px';
    lens.style.maxHeight = nextHeight + 'px';
}

function updateLoupeFromEvent(event) {
    if (!loupeActive) return;
    const lens = getLoupeElement();
    const hoveredElement = document.elementFromPoint(event.clientX, event.clientY);
    const element = getLoupeTextElement(hoveredElement);

    if (!canUseLoupeElement(element)) {
        clearLoupeTarget();
        hideLoupe();
        return;
    }

    if (loupeTarget !== element) {
        clearLoupeTarget();
        loupeTarget = element;
        loupeTarget.classList.add('loupe-active-target');
    }

    const source = (element.dataset && element.dataset.i18nSrc) ? element.dataset.i18nSrc : (element.innerText || element.textContent);
    const textToShow = sanitizeLoupeText(source);
    if (!textToShow) {
        clearLoupeTarget();
        hideLoupe();
        return;
    }
    lens.textContent = textToShow;
    adaptLoupeSize(lens, element, textToShow);
    lens.style.display = 'block';

    const offsetX = 20;
    const offsetY = 20;
    const maxX = window.innerWidth - lens.offsetWidth - 10;
    const maxY = window.innerHeight - lens.offsetHeight - 10;
    const x = Math.min(Math.max(10, event.clientX + offsetX), Math.max(10, maxX));
    const y = Math.min(Math.max(10, event.clientY + offsetY), Math.max(10, maxY));
    lens.style.left = x + 'px';
    lens.style.top = y + 'px';
}

function setLoupeActive(active) {
    loupeActive = !!active;
    localStorage.setItem('loupe_active', loupeActive ? '1' : '0');
    if (!loupeActive) {
        clearLoupeTarget();
        hideLoupe();
    }
    syncLoupeButton();
}

function toggleLoupePreference() {
    setLoupeActive(!loupeActive);
}

document.addEventListener('mousemove', updateLoupeFromEvent);
document.addEventListener('mouseleave', hideLoupe);
document.addEventListener('scroll', hideLoupe, true);

document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('loupe_toggle_btn');
    if (btn) {
        btn.addEventListener('click', toggleLoupePreference);
    }
    syncLoupeButton();
    getLoupeElement();
});

const loupeStyle = document.createElement('style');
loupeStyle.textContent = '.loupe-active-target{outline:2px dashed rgba(13,110,253,.55);outline-offset:2px;}';
document.head.appendChild(loupeStyle);

function appliquerZoomPage() {
    for (const element of document.body.children) {
        if (element.tagName === 'HEADER' || element.tagName === 'SCRIPT' || element.id === 'deconnexion_connecter') {
            continue;
        }
        element.style.zoom = zoomPage + '%';
    }
}

function changerZoom(delta) {
    zoomPage = Math.max(50, Math.min(200, zoomPage + delta));
    localStorage.setItem('zoom_page', String(zoomPage));
    appliquerZoomPage();
}

function zoomIn() {
    changerZoom(10);
}

function zoomOut() {
    changerZoom(-10);
}

document.addEventListener('DOMContentLoaded', appliquerZoomPage);

async function traduireText(text, lang) {
    if (!text || lang === 'fr') return text;
    const cleeCache = lang + '|' + text;
    if (_i18nCache[cleeCache] !== undefined) return _i18nCache[cleeCache];
    try {
        const baseTraduction = (window.LIBRETRANSLATE_URL || 'http://localhost:5000').replace(/\/$/, '');
        const resultat = await fetch(baseTraduction + '/translate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                q: text,
                source: 'fr',
                target: lang,
                format: 'text'
            })
        });

        if (!resultat.ok) {
            return text;
        }

        const tradFinal = await resultat.json();
        let traduction = text;
        if (tradFinal && tradFinal.translatedText) {
            traduction = tradFinal.translatedText;
        } else {
            traduction = text;
        }
        _i18nCache[cleeCache] = traduction;
        return traduction;
    } catch (_) {
        return text;
    }
}

async function TraductionI18n(lang) {
    if (lang == 'fr') return;
    const elements = document.querySelectorAll('[data-i18n]');
    const tache = [];
    elements.forEach(elem => {
        tache.push((async () => {
            elem.dataset.i18nSrc ??= elem.textContent.trim();
            elem.textContent = await traduireText(elem.dataset.i18nSrc, lang);
        })());
    });
    await Promise.all(tache);
}

window.OneSignalDeferred = window.OneSignalDeferred || [];

async function enregistrerPushSubscription(token, subscriptionId, actif) {
    if (!token || !subscriptionId) return;
    const base = (window.API_BASE || 'http://localhost:9000');
    try {
        const response = await fetch(base + "/push/subscription", {
            method: "POST",
            headers: {"Content-Type": "application/json", "Token": token},
            body: JSON.stringify({ subscription_id: subscriptionId, actif: !!actif })
        });
    } catch (err) {
        console.error("[OneSignal] Error registering subscription:", err);
    }
}

function initOneSignalPush(token) {
    const appId = (window.ONESIGNAL_APP_ID || "").trim();
    if (!appId || !token) {
        return;
    }

    window.OneSignalDeferred.push(async function(OneSignal) {
        try {
            const isPushSupported = OneSignal.Notifications.isPushSupported();
            if (!isPushSupported) {
                return;
            }

            await OneSignal.init({
                appId: appId,
                allowLocalhostAsSecureOrigin: true,
                serviceWorkerPath: "OneSignalSDKWorker.js",
                serviceWorkerUpdaterPath: "OneSignalSDKUpdaterWorker.js",
                serviceWorkerParam: { scope: "./" }
            });

            window._onesignalReady = true;
            window.dispatchEvent(new CustomEvent('onesignal:ready'));

            async function syncroniserAbonnementPush() {
                const pushSub = OneSignal.User && OneSignal.User.PushSubscription;
                if (!pushSub || !pushSub.id) {
                    return;
                }

                const subscriptionId = pushSub.id;
                const actif = !!pushSub.optedIn;
                if (subscriptionId) {
                    await enregistrerPushSubscription(token, subscriptionId, actif);
                    
                    window.dispatchEvent(new CustomEvent('onesignal:subscribed'));
                }
            }

            OneSignal.User.PushSubscription.addEventListener("change", async function(event) {
                const current = event && event.current ? event.current : null;
                if (current && current.id) {
                    await enregistrerPushSubscription(token, current.id, !!current.optedIn);
                }
            });

            const currentPermission = OneSignal.Notifications.permission;
            if (currentPermission !== "granted") {
                await OneSignal.Notifications.requestPermission();
            }

            await syncroniserAbonnementPush();
        } catch (error) {
            console.error("[OneSignal] Init error:", error);
        }
    });
}

function headerFallbackConnexion(nonConnecter, langueBadge) {
    if (langueBadge) langueBadge.innerHTML = "";
    if (nonConnecter) {
        nonConnecter.innerHTML = "<ul class='navbar-nav ms-auto d-flex flex-row gap-2 align-items-center'><li class='nav-item'><a class='nav-link active' href='inscription.php'>Inscription</a></li><li class='nav-item'><a class='nav-link active' href='connexion.php'>Connexion</a></li></ul>";
    }
}

async function headerUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const nonConnecter = document.getElementById("non_connecter");
    const langueBadge = document.getElementById("langue_index_badge");
    const boutonAbonnement = document.getElementById("bouton_des_abonnement");
    const boutonPlanning = document.getElementById("bouton_planning");
    const boutonMessagerie = document.getElementById("bouton_messagerie");
    const boutonBoutique = document.getElementById("bouton_boutique");
    const monCompte = document.getElementById("mon_compte");
    const autreBouton = document.getElementById("autre_bouton");
    const deconnexionBouton = document.getElementById("deconnexion_connecter_bouton");

    let response;
    try {
        response = await fetch(base + "/enligne", {
            method: "GET",
            headers: {"Content-Type": "application/json", "Token": token},
        });
    } catch (e) {
        console.error("[header] API injoignable (" + base + "). Le backend Go doit tourner (ex. docker compose up go, ou port 9000).", e);
        headerFallbackConnexion(nonConnecter, langueBadge);
        return;
    }

    if (!response.ok) {
        headerFallbackConnexion(nonConnecter, langueBadge);
        return;
    }

    let data;
    try {
        data = await response.json();
    } catch (e) {
        console.error("[header] Réponse /enligne invalide (JSON). Vérifier les logs du serveur Go.", e);
        headerFallbackConnexion(nonConnecter, langueBadge);
        return;
    }

    if (data.message == "Pas identifié") {
        headerFallbackConnexion(nonConnecter, langueBadge);
        return;
    }

    if (langueBadge) {
        if (data.langue) {
            langueBadge.innerHTML = "<span class='badge text-bg-light border'>Langue : " + String(data.langue).toUpperCase() + "</span>";
        } else {
            langueBadge.innerHTML = "";
        }
    }

    if (data.langue) TraductionI18n(data.langue);
    window._lang = data.langue;
    if (data.langue && data.langue !== 'fr') {
        let _rt;
        new MutationObserver(() => {
            clearTimeout(_rt);
            _rt = setTimeout(() => TraductionI18n(data.langue), 150);
        }).observe(document.body, { childList: true, subtree: true });
    }
    initOneSignalPush(token);

    monCompte.innerHTML = "<li class='nav-item'><a class='nav-link active' href='mon_profil.php'><i class='bi bi-person'></i> Mon profil</a></li>";
    deconnexionBouton.innerHTML = "<li class='nav-item'><a class='nav-link text-danger' href='deconnexion.php'><i class='bi bi-box-arrow-right'></i> Deconnexion</a></li>";

    if (data.role == "adherant") {
        boutonAbonnement.innerHTML = "<li class='nav-item'><a class='nav-link active' href='abonnement.php'>S'abonner</a></li>";
        boutonPlanning.innerHTML = "<li class='nav-item'><a class='nav-link active' href='planning.php'>Planning</a></li>";
        boutonMessagerie.innerHTML = "<li class='nav-item'><a class='nav-link active' href='messagerie.php'><i class='bi bi-chat-dots'></i> Messagerie</a></li>";
        boutonBoutique.innerHTML = "<li class='nav-item'><a class='nav-link active' href='boutique.php'><i class='bi bi-bag'></i> Boutique</a></li>";
        autreBouton.innerHTML = "<li class='nav-item'><a class='nav-link active' href='contrats.php'>Contrats</a></li><li class='nav-item'><a class='nav-link active' href='conseils.php'>Conseils</a></li><li class='nav-item'><a class='nav-link active' href='catalogue.php'>Catalogue</a></li><li class='nav-item'><a class='nav-link active' href='devis.php'>Devis</a></li><li class='nav-item'><a class='nav-link active' href='rendez_vous.php'>Rendez Vous</a></li><li class='nav-item'><a class='nav-link active' href='demande_presta.php'>Postuler</a></li><li class='nav-item'><a class='nav-link active' href='notifications.php'>Notifications</a></li>";
        return;
    }

    if (data.role == "prestataire") {
        boutonAbonnement.innerHTML = "<li class='nav-item'><a class='nav-link active' href='abonnement.php'>Nos abonnements</a></li>";
        boutonPlanning.innerHTML = "<li class='nav-item'><a class='nav-link active' href='avis_prestataire.php'><i class='bi bi-star'></i> Mes avis</a></li>";
        boutonMessagerie.innerHTML = "<li class='nav-item'><a class='nav-link active' href='messagerie.php'><i class='bi bi-chat-dots'></i> Messagerie</a></li>";
        boutonBoutique.innerHTML = "<li class='nav-item'><a class='nav-link active' href='boutique.php'><i class='bi bi-bag'></i> Boutique</a></li>";
        autreBouton.innerHTML = "<li class='nav-item'><a class='nav-link active' href='suivis.php'>Suivi des prestations</a></li><li class='nav-item'><a class='nav-link active' href='avis_prestataire.php'><i class='bi bi-star'></i> Mes avis</a></li><li class='nav-item'><a class='nav-link active' href='devis.php'>Devis</a></li><li class='nav-item'><a class='nav-link active' href='validations.php'>Validations</a></li><li class='nav-item'><a class='nav-link active' href='calendrier.php'>Calendrier</a></li><li class='nav-item'><a class='nav-link active' href='factures.php'>Factures</a></li><li class='nav-item'><a class='nav-link active' href='rendez_vous.php'>Rendez Vous</a></li><li class='nav-item'><a class='nav-link active' href='notifications.php'>Notifications</a></li>";
        return;
    }

    if (data.role == "admin") {
        boutonMessagerie.innerHTML = "<li class='nav-item'><a class='nav-link active' href='notifications.php'><i class='bi bi-bell'></i> Notifications</a></li>";
        boutonBoutique.innerHTML = "<li class='nav-item'><a class='nav-link active' href='boutique.php'><i class='bi bi-bag'></i> Boutique</a></li>";
        autreBouton.innerHTML = "<li class='nav-item'><a class='nav-link active' href='gestion_user.php'>Gestion des Utilisateur</a></li><li class='nav-item'><a class='nav-link active' href='gestion_evenement.php'>Gestion des Evenements</a></li><li class='nav-item'><a class='nav-link active' href='gestion_service.php'>Gestion des Services</a></li><li class='nav-item'><a class='nav-link active' href='gestion_intervention.php'>Gestion des Interventions</a></li><li class='nav-item'><a class='nav-link active' href='gestion_article.php'>Gestion des Articles</a></li><li class='nav-item'><a class='nav-link active' href='gestion_article.php'>Gestion du Catalogue</a></li><li class='nav-item'><a class='nav-link active' href='gestion_conseil.php'>Gestion des Conseils</a></li><li class='nav-item'><a class='nav-link active' href='gestion_notifs.php'>Gestion des Notifications</a></li><li class='nav-item'><a class='nav-link active' href='gestion_financier.php'>Gestion financier</a></li><li class='nav-item'><a class='nav-link active' href='gestion_contact.php'>Gestion des contacts</a></li><li class='nav-item'><a class='nav-link active' href='liste_abonnement_admin.php'>Liste des abonnements</a></li><li class='nav-item'><a class='nav-link active' href='add_abonnement.php'>Creer un abonnement</a></li><a class='nav-link active' href='attente_validation.php'>Liste demande Prestataire</a></li>";
    }
}
headerUser(localStorage.getItem('token'));
</script>