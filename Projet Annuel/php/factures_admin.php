<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title data-i18n>Factures prestataires — Comptabilité</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
        <h1 class="mb-0" data-i18n>Factures prestataires — Service comptable</h1>
        <button class="btn btn-outline-primary" onclick="chargerFactures()">
            <i class="bi bi-arrow-repeat"></i> <span data-i18n>Actualiser</span>
        </button>
    </div>

    <div id="alertZone"></div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-primary h-100">
                <div class="card-body">
                    <div class="text-muted small" data-i18n>Total factures</div>
                    <div class="fs-3 fw-bold" id="cntFactures">0</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-success h-100">
                <div class="card-body">
                    <div class="text-muted small" data-i18n>Montant total facturé</div>
                    <div class="fs-3 fw-bold" id="sumFactures">0.00 EUR</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small" data-i18n>Virements en attente</div>
                    <div class="fs-3 fw-bold" id="cntVirementsPending">0</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="card-title mb-0" data-i18n>Toutes les factures prestataires</h5>
        </div>
        <div class="card-body">
            <div id="facturesList">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                    <span data-i18n>Chargement...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
let factures = [];

function esc(v) {
    return String(v ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function formatEUR(v) {
    return Number(v || 0).toFixed(2) + ' EUR';
}

function formaterMois(key) {
    const parts = String(key || '').split('-');
    if (parts.length != 2) return key || '-';
    return parts[1] + '/' + parts[0];
}

function virementBadge(statut) {
    if (statut == 'paid') {
        return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Virement effectué</span>';
    } else if (statut == 'pending') {
        return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> En attente</span>';
    }
    return '<span class="badge bg-secondary"><i class="bi bi-dash-circle"></i> Pas de virement</span>';
}

function afficherAlerte(message, type) {
    document.getElementById('alertZone').innerHTML =
        '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
        esc(message) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}

function majStats() {
    let total = 0;
    let pending = 0;

    factures.forEach((f) => {
        total += Number(f.montant_total || 0);
        if (f.statut_virement == 'pending') pending++;
    });

    document.getElementById('cntFactures').textContent = String(factures.length);
    document.getElementById('sumFactures').textContent = formatEUR(total);
    document.getElementById('cntVirementsPending').textContent = String(pending);
}

function renderFactures() {
    const zone = document.getElementById('facturesList');

    if (!Array.isArray(factures) || factures.length == 0) {
        zone.innerHTML = '<div class="alert alert-info mb-0">Aucune facture disponible.</div>';
        return;
    }

    let html = '<div class="accordion" id="accordionFactures">';

    factures.forEach((f, idx) => {
        const interventions = Array.isArray(f.interventions) ? f.interventions : [];
        let rows = '';

        interventions.forEach((it) => {
            rows += '<tr>' +
                '<td>' + Number(it.id_intervention || 0) + '</td>' +
                '<td>' + esc(it.service || '-') + '</td>' +
                '<td>' + esc(it.client || '-') + '</td>' +
                '<td>' + esc(it.date_rdv || '-') + '</td>' +
                '<td>' + esc(it.statut || '-') + '</td>' +
                '<td class="text-end fw-semibold">' + formatEUR(it.montant) + '</td>' +
            '</tr>';
        });

        if (!rows) {
            rows = '<tr><td colspan="6" class="text-center text-muted">Aucune prestation dans cette facture.</td></tr>';
        }

        const canConfirm = f.statut_virement == 'pending';
        const confirmBtn = canConfirm
            ? '<button class="btn btn-sm btn-success" onclick="confirmerVirement(' + Number(f.id_facture || 0) + ')"><i class="bi bi-bank"></i> Confirmer virement</button>'
            : '';

        html += '<div class="accordion-item">';
        html += '<h2 class="accordion-header" id="heading' + idx + '">';
        html += '<button class="accordion-button ' + (idx == 0 ? '' : 'collapsed') + '" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' + idx + '" aria-expanded="' + (idx === 0 ? 'true' : 'false') + '" aria-controls="collapse' + idx + '">';
        html += '<span class="me-2">Facture #' + Number(f.id_facture || 0) + ' — ' + esc(f.nom_prestataire || '-') + ' — Mois ' + esc(formaterMois(f.mois)) + ' — ' + formatEUR(f.montant_total) + '</span>';
        html += virementBadge(f.statut_virement);
        html += '</button></h2>';

        html += '<div id="collapse' + idx + '" class="accordion-collapse collapse ' + (idx === 0 ? 'show' : '') + '" aria-labelledby="heading' + idx + '" data-bs-parent="#accordionFactures">';
        html += '<div class="accordion-body">';
        html += '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">';
        html += '<div>';
        html += '<span class="badge text-bg-light me-2">Génération : ' + esc(f.date_generation || '-') + '</span>';
        html += '<span class="badge text-bg-secondary me-2">Prestations : ' + interventions.length + '</span>';
        if (f.date_virement) {
            html += '<span class="badge text-bg-info">Virement le : ' + esc(f.date_virement) + '</span>';
        }
        html += '</div>';
        html += '<div class="d-flex gap-2">';
        html += confirmBtn;
        html += '<button class="btn btn-sm btn-danger" onclick="telechargerPDF(' + Number(f.id_facture || 0) + ')"><i class="bi bi-file-earmark-pdf"></i> PDF</button>';
        html += '</div>';
        html += '</div>';
        html += '<div class="table-responsive"><table class="table table-sm table-striped align-middle">';
        html += '<thead class="table-light"><tr><th>ID</th><th>Service</th><th>Client</th><th>Date</th><th>Statut</th><th class="text-end">Montant</th></tr></thead><tbody>' + rows + '</tbody></table></div>';
        html += '</div></div></div>';
    });

    html += '</div>';
    zone.innerHTML = html;
}

async function confirmerVirement(idFacture) {
    if (!confirm('Confirmer le virement pour la facture #' + idFacture + ' ?')) return;

    const token = localStorage.getItem('token');
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + '/admin/virement/confirmer/' + idFacture, {
        method: 'POST',
        headers: { 'Token': token || '' }
    });

    const text = await response.text();
    let data = {};
    try { data = JSON.parse(text); } catch (_) { data = { message: text || 'Erreur.' }; }

    if (!response.ok) {
        afficherAlerte(data.message || 'Erreur lors de la confirmation.', 'danger');
        return;
    }

    afficherAlerte('Virement confirmé pour la facture #' + idFacture, 'success');
    await chargerFactures();
}

function telechargerPDF(idFacture) {
    const targetId = Number(idFacture || 0);
    let facture = null;
    for (let idx = 0; idx < factures.length; idx++) {
        if (Number(factures[idx].id_facture || 0) == targetId) {
            facture = factures[idx];
            break;
        }
    }
    if (!facture) { afficherAlerte('Facture introuvable.', 'danger'); return; }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(16);
    doc.text('Facture prestataire #' + Number(facture.id_facture || 0), 14, 16);
    doc.setFontSize(11);
    doc.text('Prestataire: ' + String(facture.nom_prestataire || '-'), 14, 24);
    doc.text('Mois: ' + formaterMois(facture.mois), 14, 30);
    doc.text('Date génération: ' + String(facture.date_generation || '-'), 14, 36);
    doc.text('Montant total: ' + formatEUR(facture.montant_total), 14, 42);
    const svLabel = facture.statut_virement === 'paid' ? 'Virement effectué le ' + (facture.date_virement || '-') : (facture.statut_virement === 'pending' ? 'Virement en attente' : 'Pas de virement');
    doc.text('Statut virement: ' + svLabel, 14, 48);

    const body = (facture.interventions || []).map((it) => [
        String(it.id_intervention || 0),
        String(it.service || '-'),
        String(it.client || '-'),
        String(it.date_rdv || '-'),
        String(it.statut || '-'),
        formatEUR(it.montant)
    ]);

    doc.autoTable({
        startY: 56,
        head: [['ID', 'Service', 'Client', 'Date', 'Statut', 'Montant']],
        body: body.length ? body : [['-', 'Aucune prestation', '-', '-', '-', '0.00 EUR']],
        styles: { fontSize: 10 },
        headStyles: { fillColor: [33, 37, 41] }
    });

    doc.save('facture_prestataire_' + String(facture.id_facture || 'x') + '_' + String(facture.mois || 'mois') + '.pdf');
}

async function chargerFactures() {
    const token = localStorage.getItem('token');
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + '/admin/factures_prestataires', {
        method: 'GET',
        headers: { 'Token': token || '' }
    });

    if (!response.ok) {
        const text = await response.text();
        afficherAlerte(text || 'Erreur lors du chargement.', 'danger');
        return;
    }

    const data = await response.json();
    factures = Array.isArray(data.factures) ? data.factures : [];
    majStats();
    renderFactures();
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('admin', token)) return;
    await chargerFactures();
}

init();
</script>

</body>
</html>
