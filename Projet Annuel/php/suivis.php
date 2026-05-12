<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title data-i18n>Suivi de mes prestations</title>
    <style>
    .mb-custom{
            margin-bottom: 2rem
        }
    </style>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <h1 class='text-center ms-4 mb-custom' style='font-size:50px' data-i18n>Suivi de mes prestations</h1>
   <p class="text-center" data-i18n>
    Retrouvez ici l’ensemble de vos prestations, ainsi que leur état d’avancement et les informations liées à leur déroulement.
    </p>
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-1">
        <a href="calendrier.php" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-calendar3 me-1"></i><span data-i18n>Mon calendrier</span>
        </a>
    </div>

    <div id="pageAlert"></div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label fw-semibold" for="filtreStatut" data-i18n>Filtrer par statut</label>
                    <select id="filtreStatut" class="form-select" onchange="appliquerFiltre()">
                        <option value="" data-i18n>Tous les statuts</option>
                        <option value="en_attente" data-i18n>En attente</option>
                        <option value="en_cours" data-i18n>En cours</option>
                        <option value="terminé" data-i18n>Terminé</option>
                        <option value="annulé" data-i18n>Annulé</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label fw-semibold" for="filtreRecherche" data-i18n>Rechercher</label>
                    <input id="filtreRecherche" type="text" class="form-control" placeholder="Service, client, type..." oninput="appliquerFiltre()">
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <button class="btn btn-outline-secondary btn-sm" onclick="reinitialiserFiltres()" data-i18n>Réinitialiser</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="statsRow">
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-light">
                <div class="card-body py-2">
                    <div class="fs-4 fw-bold text-secondary" id="cntTotal">—</div>
                    <div class="small text-muted" data-i18n>Total</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-warning bg-opacity-10">
                <div class="card-body py-2">
                    <div class="fs-4 fw-bold text-warning" id="cntAttente">—</div>
                    <div class="small text-muted" data-i18n>En attente</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-success bg-opacity-10">
                <div class="card-body py-2">
                    <div class="fs-4 fw-bold text-success" id="cntTermine">—</div>
                    <div class="small text-muted" data-i18n>Terminées</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-primary bg-opacity-10">
                <div class="card-body py-2">
                    <div class="fs-4 fw-bold text-primary" id="cntEnCours">—</div>
                    <div class="small text-muted" data-i18n>En cours</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div id="tableContainer">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                    <span data-i18n>Chargement en cours...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIntervention" tabindex="-1" aria-labelledby="modalInterventionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInterventionLabel" data-i18n>Détail de l'intervention</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4" id="modalBody"></div>
            </div>
            <div class="modal-footer flex-column flex-sm-row align-items-stretch gap-2">
                <div class="d-flex align-items-center gap-2 me-auto">
                    <label for="modalStatutSelect" class="form-label mb-0 fw-semibold text-nowrap" data-i18n>Changer le statut :</label>
                    <select id="modalStatutSelect" class="form-select form-select-sm" style="min-width:160px">
                        <option value="en_attente" data-i18n>En attente</option>
                        <option value="en_cours" data-i18n>En cours</option>
                        <option value="terminé" data-i18n>Terminé</option>
                        <option value="annulé" data-i18n>Annulé</option>
                    </select>
                    <button class="btn btn-primary btn-sm text-nowrap" onclick="changerStatut()" data-i18n>Valider</button>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal" data-i18n>Fermer</button>
            </div>
        </div>
    </div>
</div>

<div class="mb-4"></div>

<?php include 'includes/footer.php'; ?>

<script>
let interventions = [];
let modalInterventionId = null;
let bsModal = null;

function esc(v) {
    return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function formatDate(v) {
    if (!v) return '—';
    return String(v).replace('T', ' ').replace('Z', ' ').slice(0, 16);
}

function badgeStatut(s) {
    switch (String(s).toLowerCase()) {
        case 'en_attente': return '<span class="badge bg-warning text-dark">En attente</span>';
        case 'en_cours':   return '<span class="badge bg-primary">En cours</span>';
        case 'terminé':    return '<span class="badge bg-success">Terminé</span>';
        case 'annulé':     return '<span class="badge bg-danger">Annulé</span>';
        default:           return '<span class="badge bg-secondary">' + esc(s) + '</span>';
    }
}

function setAlert(msg, type) {
    document.getElementById('pageAlert').innerHTML =
        '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
        esc(msg) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button></div>';
}

function clearAlert() {
    document.getElementById('pageAlert').innerHTML = '';
}

function mettreAJourCompteurs(liste) {
    var total = liste.length;
    var attente = 0;
    var termine = 0;
    var enCours = 0;

    for (var idx = 0; idx < liste.length; idx++) {
        var statut = liste[idx].statut;
        if (statut === 'en_attente') {
            attente++;
        } else if (statut === 'terminé') {
            termine++;
        } else if (statut === 'en_cours') {
            enCours++;
        }
    }

    document.getElementById('cntTotal').textContent = total;
    document.getElementById('cntAttente').textContent = attente;
    document.getElementById('cntTermine').textContent = termine;
    document.getElementById('cntEnCours').textContent = enCours;
}

function renderTable(liste) {
    const container = document.getElementById('tableContainer');

    if (liste.length === 0) {
        container.innerHTML = '<div class="text-center py-5 text-muted">Aucune intervention trouvée.</div>';
        return;
    }

    var lignes = '';
    for (var idx = 0; idx < liste.length; idx++) {
        var intervention = liste[idx];
        lignes += '<tr role="button" class="align-middle" onclick="ouvrirModal(' + intervention.id + ')" style="cursor:pointer">' +
            '<td class="ps-3"><span class="fw-semibold">#' + esc(intervention.id) + '</span></td>' +
            '<td>' + esc(intervention.nom_service || '—') + '</td>' +
            '<td>' + esc(intervention.prenom_utilisateur + ' ' + intervention.nom_utilisateur).trim() + '</td>' +
            '<td class="d-none d-md-table-cell">' + esc(intervention.type_rdv || '—') + '</td>' +
            '<td class="d-none d-sm-table-cell">' + esc(formatDate(intervention.date_debut)) + '</td>' +
            '<td class="d-none d-sm-table-cell">' + esc(formatDate(intervention.date_fin)) + '</td>' +
            '<td>' + badgeStatut(intervention.statut) + '</td>' +
            '<td class="text-end pe-3">' + (intervention.montant ? Number(intervention.montant).toFixed(2) + ' €' : '—') + '</td>' +
            '</tr>';
    }

    container.innerHTML =
        '<div class="table-responsive">' +
        '<table class="table table-hover mb-0">' +
        '<thead class="table-light">' +
        '<tr>' +
        '<th class="ps-3" scope="col">ID</th>' +
        '<th scope="col">Service</th>' +
        '<th scope="col">Client</th>' +
        '<th class="d-none d-md-table-cell" scope="col">Type</th>' +
        '<th class="d-none d-sm-table-cell" scope="col">Début</th>' +
        '<th class="d-none d-sm-table-cell" scope="col">Fin</th>' +
        '<th scope="col">Statut</th>' +
        '<th class="text-end pe-3" scope="col">Montant</th>' +
        '</tr>' +
        '</thead>' +
        '<tbody>' + lignes + '</tbody>' +
        '</table>' +
        '</div>';
}

function appliquerFiltre() {
    const filtreStatut = document.getElementById('filtreStatut').value;
    const recherche    = document.getElementById('filtreRecherche').value.toLowerCase().trim();

    var filtrees = [];
    for (var idx = 0; idx < interventions.length; idx++) {
        var intervention = interventions[idx];

        if (filtreStatut && intervention.statut !== filtreStatut) {
            continue;
        }

        if (recherche) {
            var haystack = (
                (intervention.nom_service || '') + ' ' +
                (intervention.nom_utilisateur || '') + ' ' +
                (intervention.prenom_utilisateur || '') + ' ' +
                (intervention.type_rdv || '')
            ).toLowerCase();

            if (!haystack.includes(recherche)) {
                continue;
            }
        }

        filtrees.push(intervention);
    }

    renderTable(filtrees);
}

function reinitialiserFiltres() {
    document.getElementById('filtreStatut').value   = '';
    document.getElementById('filtreRecherche').value = '';
    appliquerFiltre();
}

function ouvrirModal(id) {
    var i = null;
    for (var idx = 0; idx < interventions.length; idx++) {
        if (interventions[idx].id === id) {
            i = interventions[idx];
            break;
        }
    }
    if (!i) return;

    modalInterventionId = id;

    document.getElementById('modalBody').innerHTML =
        '<div class="col-md-6">' +
            '<div class="card h-100 border-0 bg-light">' +
                '<div class="card-body">' +
                    '<h6 class="card-title text-primary mb-3"><i class="bi bi-tools me-2"></i>Service</h6>' +
                    '<p class="mb-2"><strong>Nom :</strong> ' + esc(i.nom_service || '—') + '</p>' +
                    '<p class="mb-2"><strong>Type de rendez-vous :</strong> ' + esc(i.type_rdv || '—') + '</p>' +
                    '<p class="mb-0"><strong>Tarif service :</strong> ' + (i.tarif_service ? Number(i.tarif_service).toFixed(2) + ' €' : '—') + '</p>' +
                '</div>' +
            '</div>' +
        '</div>' +
        '<div class="col-md-6">' +
            '<div class="card h-100 border-0 bg-light">' +
                '<div class="card-body">' +
                    '<h6 class="card-title text-primary mb-3"><i class="bi bi-person me-2"></i>Client</h6>' +
                    '<p class="mb-2"><strong>Nom :</strong> ' + esc((i.prenom_utilisateur + ' ' + i.nom_utilisateur).trim() || '—') + '</p>' +
                    '<p class="mb-2"><strong>Début :</strong> ' + esc(formatDate(i.date_debut)) + '</p>' +
                    '<p class="mb-0"><strong>Fin :</strong> ' + esc(formatDate(i.date_fin)) + '</p>' +
                '</div>' +
            '</div>' +
        '</div>' +
        '<div class="col-12">' +
            '<div class="card border-0 bg-light">' +
                '<div class="card-body">' +
                    '<h6 class="card-title text-primary mb-3"><i class="bi bi-clipboard-check me-2"></i>Intervention #' + esc(i.id) + '</h6>' +
                    '<div class="row">' +
                        '<div class="col-sm-4"><strong>Statut :</strong> ' + badgeStatut(i.statut) + '</div>' +
                        '<div class="col-sm-4"><strong>Montant :</strong> ' + (i.montant ? Number(i.montant).toFixed(2) + ' €' : '—') + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

    document.getElementById('modalStatutSelect').value = i.statut || 'en_attente';

    if (!bsModal) {
        bsModal = new bootstrap.Modal(document.getElementById('modalIntervention'));
    }
    bsModal.show();
}

async function changerStatut() {
    if (!modalInterventionId) return;

    const statut = document.getElementById('modalStatutSelect').value;
    const token  = localStorage.getItem('token');
    const base   = (window.API_BASE || 'http://localhost:9000');

    const resp = await fetch(base + '/prestataire/interventions/' + modalInterventionId, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'Token': token },
        body: JSON.stringify({ statut: statut })
    });

    if (!resp.ok) {
        const txt = await resp.text();
        setAlert('Erreur : ' + txt, 'danger');
        return;
    }

    var idx = -1;
    for (var j = 0; j < interventions.length; j++) {
        if (interventions[j].id === modalInterventionId) {
            idx = j;
            break;
        }
    }
    if (idx !== -1) interventions[idx].statut = statut;

    bsModal.hide();
    clearAlert();
    setAlert('Statut mis à jour avec succès.', 'success');
    mettreAJourCompteurs(interventions);
    appliquerFiltre();
}

async function chargerInterventions() {
    const token = localStorage.getItem('token');
    const base  = (window.API_BASE || 'http://localhost:9000');

    const resp = await fetch(base + '/prestataire/interventions', {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (!resp.ok) {
        const txt = await resp.text();
        setAlert('Impossible de charger les interventions : ' + txt, 'danger');
        document.getElementById('tableContainer').innerHTML =
            '<div class="text-center py-5 text-muted">Aucune donnée disponible.</div>';
        return;
    }

    interventions = await resp.json();
    interventions = Array.isArray(interventions) ? interventions : [];

    mettreAJourCompteurs(interventions);
    appliquerFiltre();
}

async function init() {
    const token = localStorage.getItem('token');
    const ok = await loginUser('online', token);
    if (!ok) return;

    const base  = (window.API_BASE || 'http://localhost:9000');
    const resp  = await fetch(base + '/enligne', { headers: { 'Token': token } });
    if (!resp.ok) { window.location.href = 'connexion.php'; return; }
    const user = await resp.json();

    if (user.role !== 'prestataire') {
        document.getElementById('tableContainer').innerHTML =
            '<div class="alert alert-danger m-3">Accès réservé aux prestataires.</div>';
        return;
    }

    await chargerInterventions();
}

init();
</script>

</body>
</html>
