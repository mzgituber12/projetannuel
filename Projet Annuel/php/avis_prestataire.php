<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title data-i18n>Mes avis clients</title>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-4 mb-5">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h1 class="mb-0" data-i18n>Mes avis clients</h1>
        <a href="suivis.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i><span data-i18n>Retour aux prestations</span>
        </a>
    </div>

    <div id="pageAlert"></div>

    <div id="statsGlobales" class="row g-3 mb-4" style="display:none!important"></div>

    <div id="servicesList">
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
            <span data-i18n>Chargement en cours...</span>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

<script>
function esc(v) {
    return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function affichage_etoile(note) {
    let s = '';
    for (let i = 1; i <= 5; i++) {
        s += i <= note
            ? '<i class="bi bi-star-fill text-warning"></i>'
            : '<i class="bi bi-star text-secondary"></i>';
    }
    return s;
}

function metNotif(msg, type) {
    document.getElementById('pageAlert').innerHTML =
        '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
        esc(msg) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button></div>';
}

async function chargerAvis() {
    const token = localStorage.getItem('token');
    const base  = (window.API_BASE || 'http://localhost:9000');

    let data;
    try {
        const resp = await fetch(base + '/prestataire/evaluations', {
            headers: { 'Token': token }
        });
        if (!resp.ok) {
            const err = await resp.json().catch(() => ({}));
            metNotif(err.message || 'Erreur lors du chargement des avis.', 'danger');
            document.getElementById('servicesList').innerHTML = '';
            return;
        }
        data = await resp.json();
    } catch (e) {
        metNotif('Impossible de contacter le serveur.', 'danger');
        document.getElementById('servicesList').innerHTML = '';
        return;
    }

    if (!Array.isArray(data) || data.length == 0) {
        document.getElementById('servicesList').innerHTML =
            '<div class="alert alert-info" data-i18n>Aucun service trouvé. Vos avis clients apparaîtront ici.</div>';
        return;
    }

    let totalAvis = 0;
    for (const service of data) {
        totalAvis += service.total_reviews;
    }

    const totalServices = data.length;

    let poidTotalNote = 0;
    for (const service of data) {
        poidTotalNote += service.average_rating * service.total_reviews;
    }

    let moyenneGlob = '—';
    if (totalAvis > 0) {
        moyenneGlob = (poidTotalNote / totalAvis).toFixed(1);
    }

    const statsEl = document.getElementById('statsGlobales');
    statsEl.style.removeProperty('display');
    statsEl.innerHTML = `
        <div class="col-6 col-md-4">
            <div class="card text-center border-0 bg-light">
                <div class="card-body py-2">
                    <div class="fs-4 fw-bold text-secondary">${totalServices}</div>
                    <div class="small text-muted" data-i18n>Services</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card text-center border-0 bg-warning bg-opacity-10">
                <div class="card-body py-2">
                    <div class="fs-4 fw-bold text-warning">${totalAvis}</div>
                    <div class="small text-muted" data-i18n>Avis reçus</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card text-center border-0 bg-success bg-opacity-10">
                <div class="card-body py-2">
                    <div class="fs-4 fw-bold text-success">${moyenneGlob !== '—' ? moyenneGlob + ' / 5' : '—'}</div>
                    <div class="small text-muted" data-i18n>Note moyenne globale</div>
                </div>
            </div>
        </div>`;

    let html = '';
    for (const svc of data) {
        const avgDisplay = svc.total_reviews > 0
            ? parseFloat(svc.average_rating).toFixed(1) + ' / 5'
            : '—';
        const starsDisplay = svc.total_reviews > 0
            ? affichage_etoile(Math.round(svc.average_rating))
            : '<span class="text-muted small" data-i18n>Aucune note</span>';

        let reviewsHtml = '';
        if (!svc.reviews || svc.reviews.length == 0) {
            reviewsHtml = '<p class="text-muted small mb-0" data-i18n>Aucun avis pour ce service.</p>';
        } else {
            for (const r of svc.reviews) {
                reviewsHtml += `
                <div class="border rounded p-3 mb-2 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-semibold">${esc(r.nom_auteur.trim() || 'Anonyme')}</span>
                        <span class="text-muted small">${esc(r.date || '')}</span>
                    </div>
                    <div class="mb-1"><span class="badge text-bg-light border">Service : ${esc(svc.nom_service || '')}</span></div>
                    <div class="mb-1">${affichage_etoile(r.note)}</div>
                    ${r.commentaire ? '<p class="mb-0 text-secondary small">' + esc(r.commentaire) + '</p>' : ''}
                </div>`;
            }
        }

        html += `
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                <h5 class="mb-0">${esc(svc.nom_service)}</h5>
                <div class="d-flex align-items-center gap-2">
                    ${starsDisplay}
                    <span class="text-muted small">${avgDisplay}</span>
                    <span class="badge bg-secondary">${svc.total_reviews} avis</span>
                </div>
            </div>
            <div class="card-body">
                ${reviewsHtml}
            </div>
        </div>`;
    }

    document.getElementById('servicesList').innerHTML = html;
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;

    const base = (window.API_BASE || 'http://localhost:9000');
    const resp = await fetch(base + '/enligne', { headers: { 'Token': token } });
    if (!resp.ok) return;
    const user = await resp.json();

    if (user.role !== 'prestataire') {
        document.getElementById('servicesList').innerHTML =
            '<div class="alert alert-danger" data-i18n>Accès réservé aux prestataires.</div>';
        return;
    }

    await chargerAvis();
}

init();
</script>

</body>
</html>
