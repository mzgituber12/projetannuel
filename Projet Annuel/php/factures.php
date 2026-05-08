<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title data-i18n>Mes factures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
        <h1 class="mb-0" data-i18n>Mes factures prestataire</h1>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-primary" onclick="chargerFactures()"><i class="bi bi-arrow-repeat"></i> <span data-i18n>Actualiser</span></button>
            <button class="btn btn-warning" onclick="simulerDebutMois()"><i class="bi bi-lightning-charge"></i> <span data-i18n>Simulation debut mois (temporaire)</span></button>
        </div>
    </div>

    <div id="alertZone"></div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-primary h-100">
                <div class="card-body">
                    <div class="text-muted small" data-i18n>Nombre de factures</div>
                    <div class="fs-3 fw-bold" id="cntFactures">0</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-success h-100">
                <div class="card-body">
                    <div class="text-muted small" data-i18n>Total facture</div>
                    <div class="fs-3 fw-bold" id="sumFactures">0.00 EUR</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-info h-100">
                <div class="card-body">
                    <div class="text-muted small" data-i18n>Total prestations facturees</div>
                    <div class="fs-3 fw-bold" id="cntPrestations">0</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="card-title mb-0" data-i18n>Factures mensuelles</h5>
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
    const n = Number(v || 0);
    return n.toFixed(2) + ' EUR';
}

function afficherAlerte(message, type) {
    document.getElementById('alertZone').innerHTML =
        '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
        esc(message) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}

function formaterMois(key) {
    const parts = String(key || '').split('-');
    if (parts.length !== 2) return key || '-';
    const y = parts[0];
    const m = parts[1];
    return m + '/' + y;
}

function majStats() {
    let totalMontant = 0;
    let totalPrestations = 0;

    factures.forEach((f) => {
        totalMontant += Number(f.montant_total || 0);
        totalPrestations += Array.isArray(f.interventions) ? f.interventions.length : 0;
    });

    document.getElementById('cntFactures').textContent = String(factures.length);
    document.getElementById('sumFactures').textContent = formatEUR(totalMontant);
    document.getElementById('cntPrestations').textContent = String(totalPrestations);
}

function renderFactures() {
    const zone = document.getElementById('facturesList');

    if (!Array.isArray(factures) || factures.length == 0) {
        zone.innerHTML = '<div class="alert alert-info mb-0">Aucune facture disponible pour le moment.</div>';
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
                '<td class="text-end fw-semibold">' + esc(formatEUR(it.montant)) + '</td>' +
            '</tr>';
        });

        if (!rows) {
            rows = '<tr><td colspan="6" class="text-center text-muted">Aucune prestation dans cette facture.</td></tr>';
        }

        html += '<div class="accordion-item">';
        html += '<h2 class="accordion-header" id="heading' + idx + '">';
        html += '<button class="accordion-button ' + (idx == 0 ? '' : 'collapsed') + '" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' + idx + '" aria-expanded="' + (idx === 0 ? 'true' : 'false') + '" aria-controls="collapse' + idx + '">';
        html += 'Facture #' + Number(f.id_facture || 0) + ' - Mois ' + esc(formaterMois(f.mois)) + ' - Total ' + esc(formatEUR(f.montant_total));
        html += '</button></h2>';
        html += '<div id="collapse' + idx + '" class="accordion-collapse collapse ' + (idx === 0 ? 'show' : '') + '" aria-labelledby="heading' + idx + '" data-bs-parent="#accordionFactures">';
        html += '<div class="accordion-body">';
        const statutVirement = String(f.statut_virement || '');
        let virementBadge = '';
        if (statutVirement == 'paid') {
            virementBadge = '<span class="badge bg-success ms-2"><i class="bi bi-check-circle"></i> Virement reçu</span>';
        } else if (statutVirement == 'pending') {
            virementBadge = '<span class="badge bg-warning text-dark ms-2"><i class="bi bi-hourglass-split"></i> Virement en attente</span>';
        } else {
            virementBadge = '<span class="badge bg-secondary ms-2"><i class="bi bi-dash-circle"></i> Pas de virement</span>';
        }

        html += '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">';
        html += '<div><span class="badge text-bg-light me-2">Generation: ' + esc(f.date_generation || '-') + '</span><span class="badge text-bg-secondary">Prestations: ' + interventions.length + '</span>' + virementBadge + '</div>';
        html += '<button class="btn btn-sm btn-danger" onclick="telechargerPDF(' + Number(f.id_facture || 0) + ')"><i class="bi bi-file-earmark-pdf"></i> PDF</button>';
        html += '</div>';
        html += '<div class="table-responsive"><table class="table table-sm table-striped align-middle">';
        html += '<thead class="table-light"><tr><th>ID</th><th>Service</th><th>Client</th><th>Date</th><th>Statut</th><th class="text-end">Montant</th></tr></thead><tbody>' + rows + '</tbody></table></div>';
        html += '</div></div></div>';
    });

    html += '</div>';
    zone.innerHTML = html;
}

function telechargerPDF(idFacture) {
    const targetId = Number(idFacture || 0);
    let facture = null;
    for (let idx = 0; idx < factures.length; idx++) {
        const item = factures[idx];
        if (Number(item.id_facture || 0) == targetId) {
            facture = item;
            break;
        }
    }
    if (!facture) {
        afficherAlerte('Facture introuvable.', 'danger');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(16);
    doc.text('Facture prestataire #' + Number(facture.id_facture || 0), 14, 16);
    doc.setFontSize(11);
    doc.text('Mois: ' + formaterMois(facture.mois), 14, 24);
    doc.text('Date generation: ' + String(facture.date_generation || '-'), 14, 30);
    doc.text('Montant total: ' + formatEUR(facture.montant_total), 14, 36);
    const svLabel = facture.statut_virement === 'paid' ? 'Virement reçu' : (facture.statut_virement === 'pending' ? 'Virement en attente' : 'Pas de virement');
    doc.text('Statut virement: ' + svLabel, 14, 42);

    const body = (facture.interventions || []).map((it) => [
        String(it.id_intervention || 0),
        String(it.service || '-'),
        String(it.client || '-'),
        String(it.date_rdv || '-'),
        String(it.statut || '-'),
        formatEUR(it.montant)
    ]);

    doc.autoTable({
        startY: 50,
        head: [['ID', 'Service', 'Client', 'Date', 'Statut', 'Montant']],
        body: body.length ? body : [['-', 'Aucune prestation', '-', '-', '-', '0.00 EUR']],
        styles: { fontSize: 9 },
        headStyles: { fillColor: [33, 37, 41] }
    });

    doc.save('facture_prestataire_' + String(facture.id_facture || 'x') + '_' + String(facture.mois || 'mois') + '.pdf');
}

async function chargerFactures() {
    const token = localStorage.getItem('token');
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + '/prestataire/factures', {
        method: 'GET',
        headers: { 'Token': token || '' }
    });

    if (!response.ok) {
        const text = await response.text();
        afficherAlerte(text || 'Erreur lors du chargement des factures.', 'danger');
        return;
    }

    const data = await response.json();
    factures = Array.isArray(data.factures) ? data.factures : [];

    if (data.generation_auto && data.generation_auto.created) {
        afficherAlerte('Facture du mois ' + formaterMois(data.generation_auto.month) + ' generee automatiquement (' + formatEUR(data.generation_auto.total) + ').', 'success');
    }

    majStats();
    renderFactures();
}

async function simulerDebutMois() {
    const token = localStorage.getItem('token');
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + '/prestataire/factures/simuler', {
        method: 'POST',
        headers: { 'Token': token || '' }
    });

    const text = await response.text();
    let data = {};
    try {
        data = JSON.parse(text);
    } catch (_) {
        data = { message: text || 'Erreur simulation.' };
    }

    if (!response.ok) {
        afficherAlerte(data.message || 'Erreur simulation.', 'danger');
        return;
    }

    afficherAlerte(data.message + ' Mois: ' + formaterMois(data.month) + ' - Total: ' + formatEUR(data.total), data.created ? 'success' : 'warning');
    await chargerFactures();
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/enligne', {
        method: 'GET',
        headers: { 'Token': token || '' }
    });

    if (!response.ok) {
        window.location.href = 'connexion.php';
        return;
    }

    const user = await response.json();
    if (user.role != 'prestataire') {
        document.getElementById('facturesList').innerHTML = '<div class="alert alert-danger">Acces reserve aux prestataires.</div>';
        return;
    }

    await chargerFactures();
}

init();
</script>
</body>
</html>
