<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Gestion financier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'includes/header.php'?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="mb-0" data-i18n>Gestion financier</h1>
        <button id="reload_btn" class="btn btn-outline-primary" type="button" onclick="chargerDonnees()">
            <i class="bi bi-arrow-repeat"></i> <span data-i18n>Actualiser</span>
        </button>
    </div>

    <p class="text-muted" data-i18n>Vue globale des achats, paiements et couts du site.</p>

    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0" data-i18n>Filtres</h5>
        </div>
        <div class="card-body">
            <form class="row g-3" onsubmit="appliquerFiltres(event)">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="filter_start" data-i18n>Date debut</label>
                    <input class="form-control" id="filter_start" type="date">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="filter_end" data-i18n>Date fin</label>
                    <input class="form-control" id="filter_end" type="date">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="filter_mode" data-i18n>Mode de paiement</label>
                    <select class="form-select" id="filter_mode">
                        <option value="">Tous</option>
                        <option value="stripe">stripe</option>
                        <option value="transfer">transfer</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="filter_status" data-i18n>Statut</label>
                    <select class="form-select" id="filter_status">
                        <option value="">Tous</option>
                        <option value="paid">paid</option>
                        <option value="pending">pending</option>
                        <option value="pending_transfer">pending_transfer</option>
                        <option value="pending_stripe">pending_stripe</option>
                        <option value="canceled">canceled</option>
                        <option value="en_attente">en_attente</option>
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> <span data-i18n>Appliquer</span></button>
                    <button class="btn btn-outline-secondary" type="button" onclick="resetFiltres()"><i class="bi bi-x-circle"></i> <span data-i18n>Reinitialiser</span></button>
                    <button class="btn btn-success" type="button" onclick="exportCSV()"><i class="bi bi-filetype-csv"></i> <span data-i18n>Exporter CSV</span></button>
                    <button class="btn btn-dark" type="button" onclick="exportExcel()"><i class="bi bi-file-earmark-spreadsheet"></i> <span data-i18n>Exporter Excel</span></button>
                </div>
            </form>
        </div>
    </div>

    <div id="stats" class="row g-3 mb-4"></div>

    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0" data-i18n>Evolution mensuelle des revenus</h5>
        </div>
        <div class="card-body">
            <div style="position: relative; height: 350px;">
                <canvas id="financeChart"></canvas>
            </div>
        </div>
    </div>

    <div id="erreur" class="mb-3"></div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0" data-i18n>Achats boutique</h5>
        </div>
        <div class="card-body">
            <div id="orders" class="table-responsive"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0" data-i18n>Paiements abonnements</h5>
        </div>
        <div class="card-body">
            <div id="subscriptions" class="table-responsive"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="card-title mb-0" data-i18n>Interventions et couts prestations</h5>
        </div>
        <div class="card-body">
            <div id="interventions" class="table-responsive"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
let financeChart = null;
let dernieresDonnees = {
    stats: {},
    orders: [],
    subscription_payments: [],
    interventions: [],
    monthly: []
};

function esc(v) {
    return String(v ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function montant(v) {
    const n = Number(v || 0);
    return n.toFixed(2) + ' EUR';
}

function lireFiltres() {
    return {
        start_date: document.getElementById('filter_start').value,
        end_date: document.getElementById('filter_end').value,
        mode: document.getElementById('filter_mode').value,
        status: document.getElementById('filter_status').value
    };
}

function buildQueryString(filters) {
    const params = new URLSearchParams();
    Object.keys(filters).forEach((k) => {
        if (filters[k]) {
            params.set(k, filters[k]);
        }
    });
    const q = params.toString();
    return q ? ('?' + q) : '';
}

function badgeStatut(statut) {
    const s = String(statut || '').toLowerCase();
    if (s === 'paid' || s === 'paye') return '<span class="badge bg-success">paid</span>';
    if (s === 'pending' || s === 'pending_transfer' || s === 'pending_stripe' || s === 'en_attente') return '<span class="badge bg-warning text-dark">pending</span>';
    if (s === 'canceled' || s === 'annule') return '<span class="badge bg-secondary">canceled</span>';
    return '<span class="badge bg-light text-dark border">' + esc(statut || '-') + '</span>';
}

function renderStats(stats) {
    const cards = [
        { label: 'Total global', value: montant(stats.global_revenue_total), color: 'primary', icon: 'bi-cash-stack' },
        { label: 'Achats boutique', value: String(stats.orders_total || 0), color: 'info', icon: 'bi-cart-check' },
        { label: 'CA boutique', value: montant(stats.shop_revenue_total), color: 'dark', icon: 'bi-bag-check' },
        { label: 'Boutique paye', value: montant(stats.shop_paid_total), color: 'success', icon: 'bi-check2-circle' },
        { label: 'Boutique en attente', value: montant(stats.shop_pending_total), color: 'warning', icon: 'bi-hourglass-split' },
        { label: 'CA abonnements', value: montant(stats.subscription_revenue_total), color: 'success', icon: 'bi-stars' },
        { label: 'Abonnements en attente', value: montant(stats.subscription_pending_total), color: 'warning', icon: 'bi-clock-history' },
        { label: 'Interventions', value: String(stats.interventions_total || 0), color: 'secondary', icon: 'bi-tools' },
        { label: 'Cout prestations', value: montant(stats.interventions_amount_total), color: 'warning', icon: 'bi-receipt' }
    ];

    let html = '';
    cards.forEach(c => {
        html += '<div class="col-12 col-sm-6 col-lg-4 col-xl-3">';
        html += '<div class="card border-' + c.color + ' h-100">';
        html += '<div class="card-body">';
        html += '<div class="d-flex justify-content-between align-items-start gap-2">';
        html += '<div><div class="text-muted small">' + esc(c.label) + '</div><div class="fs-5 fw-bold">' + esc(c.value) + '</div></div>';
        html += '<i class="bi ' + esc(c.icon) + ' fs-4 text-' + c.color + '"></i>';
        html += '</div></div></div></div>';
    });
    document.getElementById('stats').innerHTML = html;
}

function renderOrders(orders) {
    if (!Array.isArray(orders) || orders.length === 0) {
        document.getElementById('orders').innerHTML = '<div class="alert alert-info mb-0">Aucun achat pour le moment.</div>';
        return;
    }

    let html = '<table class="table table-hover table-striped align-middle">';
    html += '<thead class="table-light"><tr><th>#Commande</th><th>Panier</th><th>Date</th><th>Utilisateur</th><th>Articles</th><th>Mode</th><th>Statut</th><th>Montant</th></tr></thead><tbody>';
    orders.forEach(o => {
        html += '<tr>';
        html += '<td>' + Number(o.id_achat || 0) + '</td>';
        html += '<td>' + Number(o.id_panier || 0) + '</td>';
        html += '<td>' + esc(o.date_achat || '-') + '</td>';
        html += '<td><div>' + esc(o.nom_complet || '-') + '</div><small class="text-muted">' + esc(o.email || '-') + '</small></td>';
        html += '<td>' + Number(o.nb_articles || 0) + '</td>';
        html += '<td>' + esc(o.mode || '-') + '</td>';
        html += '<td>' + badgeStatut(o.statut) + '</td>';
        html += '<td class="fw-semibold">' + esc(montant(o.montant)) + '</td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    document.getElementById('orders').innerHTML = html;
}

function renderSubscriptions(subs) {
    if (!Array.isArray(subs) || subs.length === 0) {
        document.getElementById('subscriptions').innerHTML = '<div class="alert alert-info mb-0">Aucun paiement abonnement pour le moment.</div>';
        return;
    }

    let html = '<table class="table table-hover table-striped align-middle">';
    html += '<thead class="table-light"><tr><th>#Paiement</th><th>Abonnement</th><th>Date</th><th>Mode</th><th>Statut</th><th>Montant</th></tr></thead><tbody>';
    subs.forEach(s => {
        html += '<tr>';
        html += '<td>' + Number(s.id_paiement_abonnement || 0) + '</td>';
        html += '<td><div>' + esc(s.abonnement || '-') + '</div><small class="text-muted">ID: ' + Number(s.id_abonnement || 0) + '</small></td>';
        html += '<td>' + esc(s.date_paiement || '-') + '</td>';
        html += '<td>' + esc(s.mode || '-') + '</td>';
        html += '<td>' + badgeStatut(s.statut) + '</td>';
        html += '<td class="fw-semibold">' + esc(montant(s.montant)) + '</td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    document.getElementById('subscriptions').innerHTML = html;
}

function renderInterventions(items) {
    if (!Array.isArray(items) || items.length === 0) {
        document.getElementById('interventions').innerHTML = '<div class="alert alert-info mb-0">Aucune intervention pour le moment.</div>';
        return;
    }

    let html = '<table class="table table-hover table-striped align-middle">';
    html += '<thead class="table-light"><tr><th>#Intervention</th><th>Service</th><th>Client</th><th>Prestataire</th><th>Date RDV</th><th>Statut</th><th>Montant</th></tr></thead><tbody>';
    items.forEach(i => {
        html += '<tr>';
        html += '<td>' + Number(i.id_intervention || 0) + '</td>';
        html += '<td>' + esc(i.service || '-') + '</td>';
        html += '<td>' + esc(i.client || '-') + '</td>';
        html += '<td>' + esc(i.prestataire || '-') + '</td>';
        html += '<td>' + esc(i.date_rdv || '-') + '</td>';
        html += '<td>' + badgeStatut(i.statut) + '</td>';
        html += '<td class="fw-semibold">' + esc(montant(i.montant)) + '</td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    document.getElementById('interventions').innerHTML = html;
}

function renderChart(monthly) {
    const ctx = document.getElementById('financeChart');
    if (!ctx) return;

    if (financeChart) {
        financeChart.destroy();
        financeChart = null;
    }

    const list = Array.isArray(monthly) ? monthly : [];
    const labels = list.map((m) => String(m.month || '-'));
    const shop = list.map((m) => Number(m.shop || 0));
    const subscription = list.map((m) => Number(m.subscription || 0));
    const intervention = list.map((m) => Number(m.intervention || 0));
    const total = list.map((m) => Number(m.total || 0));

    financeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Boutique', data: shop, backgroundColor: 'rgba(13, 110, 253, 0.70)' },
                { label: 'Abonnements', data: subscription, backgroundColor: 'rgba(25, 135, 84, 0.70)' },
                { label: 'Interventions', data: intervention, backgroundColor: 'rgba(255, 193, 7, 0.70)' },
                { label: 'Total', data: total, type: 'line', borderColor: 'rgba(33, 37, 41, 1)', backgroundColor: 'rgba(33, 37, 41, 0.15)', yAxisID: 'y' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        format: {
                            style: 'currency',
                            currency: 'EUR'
                        }
                    }
                }
            }
        }
    });
}

function downloadFile(fileName, content, type) {
    const blob = new Blob([content], { type: type });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}

function csvEscape(value) {
    const s = String(value ?? '');
    const verifcar = s.includes(';') || s.includes('"') || s.includes('\n');
    return verifcar ? '"' + s.replaceAll('"', '""') + '"' : s;
}

function exportCSV() {
    const lines = [];

    lines.push('Statistiques');
    lines.push('cle;valeur');
    const stats = dernieresDonnees.stats || {};
    lines.push('total_global;' + csvEscape(montant(stats.global_revenue_total)));
    lines.push('achats_boutique;' + csvEscape(String(stats.orders_total || 0)));
    lines.push('ca_boutique;' + csvEscape(montant(stats.shop_revenue_total)));
    lines.push('ca_abonnements;' + csvEscape(montant(stats.subscription_revenue_total)));
    lines.push('cout_prestations;' + csvEscape(montant(stats.interventions_amount_total)));
    lines.push('');

    lines.push('Achats boutique');
    lines.push('id_achat;id_panier;date;nom;email;nb_articles;mode;statut;montant');
    (dernieresDonnees.orders || []).forEach((o) => {
        lines.push([
            o.id_achat,
            o.id_panier,
            o.date_achat,
            o.nom_complet,
            o.email,
            o.nb_articles,
            o.mode,
            o.statut,
            Number(o.montant || 0).toFixed(2)
        ].map(csvEscape).join(';'));
    });
    lines.push('');

    lines.push('Paiements abonnements');
    lines.push('id_paiement_abonnement;id_abonnement;abonnement;date;mode;statut;montant');
    (dernieresDonnees.subscription_payments || []).forEach((s) => {
        lines.push([
            s.id_paiement_abonnement,
            s.id_abonnement,
            s.abonnement,
            s.date_paiement,
            s.mode,
            s.statut,
            Number(s.montant || 0).toFixed(2)
        ].map(csvEscape).join(';'));
    });
    lines.push('');

    lines.push('Interventions');
    lines.push('id_intervention;service;client;prestataire;date_rdv;statut;montant');
    (dernieresDonnees.interventions || []).forEach((i) => {
        lines.push([
            i.id_intervention,
            i.service,
            i.client,
            i.prestataire,
            i.date_rdv,
            i.statut,
            Number(i.montant || 0).toFixed(2)
        ].map(csvEscape).join(';'));
    });

    downloadFile('gestion_financier.csv', lines.join('\n'), 'text/csv;charset=utf-8');
}

function exportExcel() {
    const stats = dernieresDonnees.stats || {};

    let html = '<html><head><meta charset="UTF-8"></head><body>';
    html += '<h2>Gestion financier</h2>';

    html += '<h3>Statistiques</h3><table border="1"><tr><th>Cle</th><th>Valeur</th></tr>';
    html += '<tr><td>Total global</td><td>' + esc(montant(stats.global_revenue_total)) + '</td></tr>';
    html += '<tr><td>Achats boutique</td><td>' + esc(String(stats.orders_total || 0)) + '</td></tr>';
    html += '<tr><td>CA boutique</td><td>' + esc(montant(stats.shop_revenue_total)) + '</td></tr>';
    html += '<tr><td>CA abonnements</td><td>' + esc(montant(stats.subscription_revenue_total)) + '</td></tr>';
    html += '<tr><td>Cout prestations</td><td>' + esc(montant(stats.interventions_amount_total)) + '</td></tr>';
    html += '</table>';

    html += '<h3>Achats boutique</h3><table border="1"><tr><th>ID</th><th>Panier</th><th>Date</th><th>Nom</th><th>Email</th><th>Articles</th><th>Mode</th><th>Statut</th><th>Montant</th></tr>';
    (dernieresDonnees.orders || []).forEach((o) => {
        html += '<tr>';
        html += '<td>' + Number(o.id_achat || 0) + '</td>';
        html += '<td>' + Number(o.id_panier || 0) + '</td>';
        html += '<td>' + esc(o.date_achat || '-') + '</td>';
        html += '<td>' + esc(o.nom_complet || '-') + '</td>';
        html += '<td>' + esc(o.email || '-') + '</td>';
        html += '<td>' + Number(o.nb_articles || 0) + '</td>';
        html += '<td>' + esc(o.mode || '-') + '</td>';
        html += '<td>' + esc(o.statut || '-') + '</td>';
        html += '<td>' + esc(montant(o.montant)) + '</td>';
        html += '</tr>';
    });
    html += '</table>';

    html += '<h3>Paiements abonnements</h3><table border="1"><tr><th>ID</th><th>Abonnement</th><th>Date</th><th>Mode</th><th>Statut</th><th>Montant</th></tr>';
    (dernieresDonnees.subscription_payments || []).forEach((s) => {
        html += '<tr>';
        html += '<td>' + Number(s.id_paiement_abonnement || 0) + '</td>';
        html += '<td>' + esc(s.abonnement || '-') + ' (ID ' + Number(s.id_abonnement || 0) + ')</td>';
        html += '<td>' + esc(s.date_paiement || '-') + '</td>';
        html += '<td>' + esc(s.mode || '-') + '</td>';
        html += '<td>' + esc(s.statut || '-') + '</td>';
        html += '<td>' + esc(montant(s.montant)) + '</td>';
        html += '</tr>';
    });
    html += '</table>';

    html += '<h3>Interventions</h3><table border="1"><tr><th>ID</th><th>Service</th><th>Client</th><th>Prestataire</th><th>Date RDV</th><th>Statut</th><th>Montant</th></tr>';
    (dernieresDonnees.interventions || []).forEach((i) => {
        html += '<tr>';
        html += '<td>' + Number(i.id_intervention || 0) + '</td>';
        html += '<td>' + esc(i.service || '-') + '</td>';
        html += '<td>' + esc(i.client || '-') + '</td>';
        html += '<td>' + esc(i.prestataire || '-') + '</td>';
        html += '<td>' + esc(i.date_rdv || '-') + '</td>';
        html += '<td>' + esc(i.statut || '-') + '</td>';
        html += '<td>' + esc(montant(i.montant)) + '</td>';
        html += '</tr>';
    });
    html += '</table>';

    html += '</body></html>';

    downloadFile('gestion_financier.xls', html, 'application/vnd.ms-excel;charset=utf-8');
}

function afficherErreur(message) {
    document.getElementById('erreur').innerHTML = '<div class="alert alert-danger">' + esc(message || 'Erreur de chargement.') + '</div>';
}

async function chargerDonnees() {
    document.getElementById('erreur').innerHTML = '';
    const token = localStorage.getItem('token') || '';
    const base = (window.API_BASE || 'http://localhost:9000');
    const query = buildQueryString(lireFiltres());

    const response = await fetch(base + '/gestion_financier' + query, {
        method: 'GET',
        headers: { 'Token': token }
    });

    if (!response.ok) {
        const text = await response.text();
        afficherErreur(text || 'Impossible de charger les donnees financieres.');
        return;
    }

    const data = await response.json();
    dernieresDonnees = {
        stats: data.stats || {},
        orders: data.orders || [],
        subscription_payments: data.subscription_payments || [],
        interventions: data.interventions || [],
        monthly: data.monthly || []
    };

    renderStats(data.stats || {});
    renderOrders(data.orders || []);
    renderSubscriptions(data.subscription_payments || []);
    renderInterventions(data.interventions || []);
    renderChart(data.monthly || []);
}

async function appliquerFiltres(event) {
    if (event) event.preventDefault();
    await chargerDonnees();
}

async function resetFiltres() {
    document.getElementById('filter_start').value = '';
    document.getElementById('filter_end').value = '';
    document.getElementById('filter_mode').value = '';
    document.getElementById('filter_status').value = '';
    await chargerDonnees();
}

async function init() {
    const token = localStorage.getItem('token');
    if (!await loginUser('online', token)) return;
    if (!await adminUser(token)) return;
    await chargerDonnees();
}

window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});

init();
</script>
</body>
</html>
