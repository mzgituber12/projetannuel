<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Detail rendez-vous</title>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-4 mb-5">
    <a href="rendez_vous.php" class="btn btn-outline-secondary btn-sm mb-3" data-i18n>Retour a mes rendez-vous</a>
    <h1 data-i18n>Detail du rendez-vous</h1>
    <div id="detailContainer">
        <p data-i18n>Chargement en cours...</p>
    </div>
    <div id="evaluationContainer" class="mt-4">
        <p class="text-muted" data-i18n>Chargement des evaluations...</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
const rdvId = <?= intval($_GET['id'] ?? 0) ?>;

function esc(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function formatDate(value) {
    if (!value) return '—';
    return String(value).replace('T', ' ').slice(0, 16);
}

function badgeStatut(status) {
    const normalized = String(status || '').toLowerCase();
    if (normalized === 'accepté' || normalized === 'accepte') {
        return '<span class="badge bg-success">accepte</span>';
    }
    if (normalized === 'refusé' || normalized === 'refuse') {
        return '<span class="badge bg-danger">refuse</span>';
    }
    if (normalized === 'en_attente') {
        return '<span class="badge bg-warning text-dark">en attente</span>';
    }
    return '<span class="badge bg-secondary">' + esc(status || 'inconnu') + '</span>';
}

function formatReviewDate(value) {
    if (!value) return '—';
    return esc(String(value).slice(0, 10));
}

function affichage_etoile(note) {
    const NoteEntier = Math.max(0, Math.min(5, Number(note || 0)));
    let etoiles = '';
    for (let i = 0; i < NoteEntier; i++) {
        etoiles += '★';
    }
    for (let i = 0; i < 5 - NoteEntier; i++) {
        etoiles += '☆';
    }
    return etoiles;
}

async function chargerEvaluations(serviceId) {
    const container = document.getElementById('evaluationContainer');
    if (!serviceId || serviceId <= 0) {
        container.innerHTML = '<div class="alert alert-secondary">Evaluation indisponible pour ce rendez-vous.</div>';
        return;
    }

    const token = localStorage.getItem('token');
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/evaluations/service/' + encodeURIComponent(serviceId), {
        method: 'GET',
        headers: { 'Token': token || '' }
    });

    if (!response.ok) {
        container.innerHTML = '<div class="alert alert-danger">Impossible de charger les evaluations.</div>';
        return;
    }

    const data = await response.json();
    const utilisateurCommentaire = data.user_review || null;
    const commentaire = Array.isArray(data.reviews) ? data.reviews : [];
    const peutCommenter = Boolean(data.can_review);

    let html = '';
    html += '<div class="card shadow-sm">';
    html += '<div class="card-body">';
    html += '<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">';
    html += '<div><h4 class="card-title mb-1" data-i18n>Evaluation du service</h4><p class="text-muted mb-0">Moyenne : <strong>' + esc(Number(data.average_rating || 0).toFixed(1)) + '/5</strong> (' + esc(String(data.total_reviews || 0)) + ' avis)</p></div>';
    html += '<div class="fs-4 text-warning" aria-hidden="true">' + affichage_etoile(Math.round(Number(data.average_rating || 0))) + '</div>';
    html += '</div>';

    if (peutCommenter) {
        html += '<form id="evaluationForm" class="mb-4">';
        html += '<div class="mb-3"><label for="evaluationNote" class="form-label">Note</label><select id="evaluationNote" class="form-select" required>';
        html += '<option value="">Choisir une note</option>';
        for (let note = 5; note >= 1; note -= 1) {
            const selected = utilisateurCommentaire && Number(utilisateurCommentaire.note) === note ? ' selected' : '';
            html += '<option value="' + note + '"' + selected + '>' + note + '/5</option>';
        }
        html += '</select></div>';
        html += '<div class="mb-3"><label for="evaluationCommentaire" class="form-label">Commentaire</label><textarea id="evaluationCommentaire" class="form-control" rows="4" maxlength="1000" placeholder="Partagez votre retour sur la prestation...">' + esc(utilisateurCommentaire ? utilisateurCommentaire.commentaire || '' : '') + '</textarea></div>';
        html += '<button type="submit" class="btn btn-primary">' + (utilisateurCommentaire ? 'Mettre a jour mon evaluation' : 'Envoyer mon evaluation') + '</button>';
        html += '</form>';
    } else {
        html += '<div class="alert alert-info">Vous pourrez laisser une evaluation une fois la prestation terminee.</div>';
    }

    html += '<h5 class="mb-3">Avis recents</h5>';
    if (!commentaire.length) {
        html += '<p class="text-muted mb-0">Aucune evaluation pour le moment.</p>';
    } else {
        commentaire.forEach(function(com) {
            html += '<div class="border rounded p-3 mb-3">';
            html += '<div class="d-flex justify-content-between gap-3 flex-wrap">';
            html += '<strong>' + esc(com.nom_auteur || 'Utilisateur') + '</strong>';
            html += '<span class="text-warning">' + affichage_etoile(com.note) + '</span>';
            html += '</div>';
            html += '<div class="small text-muted mb-2">' + formatReviewDate(com.date) + '</div>';
            html += '<div>' + esc(com.commentaire || '') + '</div>';
            html += '</div>';
        });
    }

    html += '</div></div>';
    container.innerHTML = html;

    if (peutCommenter) {
        const form = document.getElementById('evaluationForm');
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            const note = Number(document.getElementById('evaluationNote').value || 0);
            const commentaire = document.getElementById('evaluationCommentaire').value || '';

            const saveResponse = await fetch(base + '/evaluations/service/' + encodeURIComponent(serviceId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Token': token || ''
                },
                body: JSON.stringify({ note: note, commentaire: commentaire })
            });

            if (!saveResponse.ok) {
                let message = 'Impossible d\'enregistrer votre evaluation.';
                try {
                    const errorPayload = await saveResponse.json();
                    if (errorPayload && errorPayload.message) {
                        message = String(errorPayload.message);
                    }
                } catch (error) {}
                alert(message);
                return;
            }

            await chargerEvaluations(serviceId);
        });
    }
}

async function chargerDetailRendezVous() {
    const container = document.getElementById('detailContainer');

    if (!rdvId || rdvId <= 0) {
        container.innerHTML = '<div class="alert alert-danger">Identifiant de rendez-vous invalide.</div>';
        return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'connexion.php';
        return;
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const detailResp = await fetch(base + '/devis/' + encodeURIComponent(rdvId), {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (!detailResp.ok) {
        container.innerHTML = '<div class="alert alert-danger">Rendez-vous introuvable ou acces refuse.</div>';
        return;
    }

    const d = await detailResp.json();

    let serviceDescription = 'Description non disponible.';
    let serviceImage = '';
    let serviceTarif = d.tarif ? Number(d.tarif).toFixed(2) + ' EUR' : 'Non renseigne';

    const serviceResp = await fetch(base + '/services', {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (serviceResp.ok) {
        const payload = await serviceResp.json();
        const services = Array.isArray(payload.service) ? payload.service : [];
        const service = services.find(function(s) {
            return String(s.nom || '').toLowerCase() === String(d.nom_service || '').toLowerCase();
        });
        if (service) {
            if (service.description) serviceDescription = String(service.description);
            if (service.image) serviceImage = String(service.image);
            if (Number(service.tarif || 0) > 0) {
                serviceTarif = Number(service.tarif).toFixed(2) + ' EUR';
            }
        }
    }

    const imageHtml = serviceImage
        ? '<img src="' + esc(serviceImage) + '" alt="Image du service" class="img-fluid rounded mb-3" style="max-height:260px; object-fit:cover;">'
        : '<p class="text-muted mb-3">Aucune image de service.</p>';

    container.innerHTML = '' +
        '<div class="row g-4">' +
            '<div class="col-lg-6">' +
                '<div class="card h-100 shadow-sm">' +
                    '<div class="card-body">' +
                        '<h4 class="card-title mb-3" data-i18n>Service inscrit</h4>' +
                        imageHtml +
                        '<p class="mb-2"><strong data-i18n>Nom :</strong> ' + esc(d.nom_service || '—') + '</p>' +
                        '<p class="mb-2"><strong data-i18n>Description :</strong> ' + esc(serviceDescription) + '</p>' +
                        '<p class="mb-0"><strong data-i18n>Tarif :</strong> ' + esc(serviceTarif) + '</p>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-lg-6">' +
                '<div class="card h-100 shadow-sm">' +
                    '<div class="card-body">' +
                        '<h4 class="card-title mb-3" data-i18n>Rendez-vous et intervention</h4>' +
                        '<p class="mb-2"><strong data-i18n>Prestataire :</strong> ' + esc(d.nom_prestataire || '—') + '</p>' +
                        '<p class="mb-2"><strong data-i18n>Debut :</strong> ' + esc(formatDate(d.date_debut)) + '</p>' +
                        '<p class="mb-2"><strong data-i18n>Fin :</strong> ' + esc(formatDate(d.date_fin)) + '</p>' +
                        '<p class="mb-2"><strong data-i18n>Statut intervention :</strong> ' + badgeStatut(d.status) + '</p>' +
                        '<p class="mb-0"><strong data-i18n>Montant intervention :</strong> ' + esc((Number(d.tarif || 0) > 0) ? Number(d.tarif).toFixed(2) + ' EUR' : 'Non renseigne') + '</p>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

    await chargerEvaluations(Number(d.id_service || 0));
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;
    await chargerDetailRendezVous();
}

init();
</script>
</body>
</html>
