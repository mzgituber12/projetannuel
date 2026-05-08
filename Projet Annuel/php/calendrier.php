<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Calendrier prestataire</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php' ?>

<div class="container mt-4 mb-5">
    <div class="mb-3">
        <h1 class="h3 fw-bold mb-1" data-i18n>Calendrier prestataire</h1>
        <p class="text-muted mb-0" data-i18n>Consultez vos rendez-vous et gérez vos créneaux de disponibilité/indisponibilité.</p>
    </div>

    <div id="pageError" class="alert alert-danger d-none" role="alert"></div>
    <div id="pageInfo" class="alert alert-info d-none" role="alert"></div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold" data-i18n><i class="bi bi-plus-circle me-2"></i>Ajouter un créneau</h6>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-semibold" for="slotType" data-i18n>Type de créneau</label>
                    <select class="form-select" id="slotType">
                        <option value="disponible" data-i18n>Disponible</option>
                        <option value="indisponible" data-i18n>Indisponible</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-semibold" for="slotRecurrence" data-i18n>Récurrence</label>
                    <select class="form-select" id="slotRecurrence">
                        <option value="unique" data-i18n>Unique</option>
                        <option value="quotidienne" data-i18n>Tous les jours (période)</option>
                        <option value="hebdomadaire" data-i18n>Toutes les semaines</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 d-none" id="ruleEndContainer">
                    <label class="form-label fw-semibold" for="slotRuleEnd" data-i18n>Fin de période</label>
                    <input type="date" class="form-control" id="slotRuleEnd">
                </div>
                <div class="col-12 col-sm-6 col-lg-3 d-none" id="weekdayContainer">
                    <label class="form-label fw-semibold" for="slotWeekday" data-i18n>Jour de la semaine</label>
                    <select class="form-select" id="slotWeekday">
                        <option value="lundi" data-i18n>Lundi</option>
                        <option value="mardi" data-i18n>Mardi</option>
                        <option value="mercredi" data-i18n>Mercredi</option>
                        <option value="jeudi" data-i18n>Jeudi</option>
                        <option value="vendredi" data-i18n>Vendredi</option>
                        <option value="samedi" data-i18n>Samedi</option>
                        <option value="dimanche" data-i18n>Dimanche</option>
                    </select>
                </div>
                <div class="col-12">
                    <p class="text-muted small mb-0" data-i18n><i class="bi bi-info-circle me-1"></i>Sélectionnez une plage horaire directement dans le calendrier pour créer un créneau.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
        <span class="d-inline-flex align-items-center gap-2 small">
            <span style="width:12px;height:12px;border-radius:50%;background:#2563eb;display:inline-block;"></span>
            <span data-i18n>Rendez-vous</span>
        </span>
        <span class="d-inline-flex align-items-center gap-2 small">
            <span style="width:12px;height:12px;border-radius:50%;background:#059669;display:inline-block;"></span>
            <span data-i18n>Disponibilités</span>
        </span>
        <span class="d-inline-flex align-items-center gap-2 small">
            <span style="width:12px;height:12px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
            <span data-i18n>Indisponibilités</span>
        </span>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-2">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
async function fetchEnligne(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/enligne', {
        method: 'GET',
        headers: { 'Token': token }
    });
    if (!response.ok) return null;
    return response.json();
}

function setError(message) {
    const node = document.getElementById('pageError');
    node.textContent = message;
    node.classList.remove('d-none');
}

function clearError() {
    const node = document.getElementById('pageError');
    node.textContent = '';
    node.classList.add('d-none');
}

function setInfo(message) {
    const node = document.getElementById('pageInfo');
    node.textContent = message;
    node.classList.remove('d-none');
    window.__calendarInfoTimeout = window.setTimeout(() => {
        node.classList.add('d-none');
        node.textContent = '';
    }, 2200);
}

function updateRecurrenceFields() {
    const recurrence = document.getElementById('slotRecurrence').value;
    const endContainer = document.getElementById('ruleEndContainer');
    const weekdayContainer = document.getElementById('weekdayContainer');

    if (recurrence === 'unique') {
        endContainer.classList.add('d-none');
        weekdayContainer.classList.add('d-none');
        return;
    }

    endContainer.classList.remove('d-none');
    if (recurrence === 'hebdomadaire') {
        weekdayContainer.classList.remove('d-none');
    } else {
        weekdayContainer.classList.add('d-none');
    }
}

async function apiFetchJson(base, path, token, options) {
    const response = await fetch(base + path, {
        method: (options && options.method) ? options.method : 'GET',
        headers: Object.assign(
            { 'Token': token },
            (options && options.headers) ? options.headers : {}
        ),
        body: (options && options.body) ? options.body : undefined
    });

    if (!response.ok) {
        throw new Error(await response.text());
    }

    return response.json();
}

function mapRdvEvents(payload) {
    if (!Array.isArray(payload)) return [];

    const events = [];
    for (let idx = 0; idx < payload.length; idx++) {
        const e = payload[idx];
        events.push({
            id: 'rdv-' + e.id,
            title: e.title || 'Rendez-vous',
            start: e.start,
            end: e.end,
            color: '#2563eb',
            textColor: '#ffffff',
            extendedProps: { kind: 'rdv' }
        });
    }
    return events;
}

function mapDisponibiliteEvents(payload) {
    if (!Array.isArray(payload)) return [];

    const events = [];
    for (let idx = 0; idx < payload.length; idx++) {
        const e = payload[idx];
        const isDisponible = e.type === 'disponible';
        const recurrenceLabel = e.recurrence === 'quotidienne'
            ? ' (quotidien)'
            : (e.recurrence === 'hebdomadaire' ? ' (hebdo)' : '');

        events.push({
            id: 'slot-' + e.id,
            title: (isDisponible ? 'Disponible' : 'Indisponible') + recurrenceLabel,
            start: e.start,
            end: e.end,
            color: isDisponible ? '#059669' : '#dc2626',
            textColor: '#ffffff',
            extendedProps: {
                kind: 'slot',
                slotId: e.id,
                slotType: e.type,
                recurrence: e.recurrence
            }
        });
    }
    return events;
}

async function loadRdvEvents(base, token) {
    const payload = await apiFetchJson(base, '/prestataire/rendez_vous', token);
    return mapRdvEvents(payload);
}

async function loadDisponibiliteEvents(base, token, info) {
    const params = new URLSearchParams({
        start: info.startStr,
        end: info.endStr
    });
    const payload = await apiFetchJson(base, '/prestataire/disponibilites?' + params.toString(), token);
    return mapDisponibiliteEvents(payload);
}

async function createDisponibilite(base, token, selectionInfo, payload) {
    await apiFetchJson(base, '/prestataire/disponibilites/creer', token, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            start: selectionInfo.startStr,
            end: selectionInfo.endStr,
            type: payload.type,
            recurrence: payload.recurrence,
            date_fin_regle: payload.dateFinRegle,
            jour_semaine: payload.jourSemaine
        })
    });
}

async function deleteDisponibilite(base, token, slotId) {
    await apiFetchJson(base, '/prestataire/disponibilites/' + slotId, token, {
        method: 'DELETE'
    });
}

async function init() {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'connexion.php';
        return;
    }

    if (!await loginUser('online', token)) return;

    const user = await fetchEnligne(token);
    if (!user || user.role !== 'prestataire') {
        alert('Cette page est réservée aux prestataires.');
        window.location.href = 'index.php';
        return;
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const calendarEl = document.getElementById('calendar');

    const defaultRuleEnd = new Date();
    defaultRuleEnd.setDate(defaultRuleEnd.getDate() + 30);
    document.getElementById('slotRuleEnd').value = defaultRuleEnd.toISOString().slice(0, 10);
    document.getElementById('slotRecurrence').addEventListener('change', updateRecurrenceFields);
    updateRecurrenceFields();

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        firstDay: 1,
        locale: 'fr',
        selectable: true,
        nowIndicator: true,
        allDaySlot: false,
        slotMinTime: '06:00:00',
        slotMaxTime: '24:00:00',
        slotDuration: '00:30:00',
        height: 'auto',
        eventSources: [
            {
                events: async function(info, successCallback, failureCallback) {
                    try {
                        successCallback(await loadRdvEvents(base, token));
                    } catch (error) {
                        setError('Erreur chargement rendez-vous : ' + (error.message || error));
                        failureCallback(error);
                    }
                }
            },
            {
                events: async function(info, successCallback, failureCallback) {
                    try {
                        successCallback(await loadDisponibiliteEvents(base, token, info));
                    } catch (error) {
                        setError('Erreur chargement disponibilités : ' + (error.message || error));
                        failureCallback(error);
                    }
                }
            }
        ],
        select: async function(selectionInfo) {
            clearError();
            const type = document.getElementById('slotType').value;
            const recurrence = document.getElementById('slotRecurrence').value;
            const dateFinRegle = document.getElementById('slotRuleEnd').value;
            const jourSemaine = document.getElementById('slotWeekday').value;

            if (recurrence !== 'unique' && !dateFinRegle) {
                setError('Veuillez renseigner la fin de période pour cette récurrence.');
                calendar.unselect();
                return;
            }

            try {
                await createDisponibilite(base, token, selectionInfo, {
                    type: type,
                    recurrence: recurrence,
                    dateFinRegle: dateFinRegle,
                    jourSemaine: jourSemaine
                });

                setInfo('Créneau ajouté avec succès.');
                calendar.refetchEvents();
            } catch (error) {
                setError(error.message || 'Erreur lors de la création du créneau.');
            } finally {
                calendar.unselect();
            }
        },
        eventClick: async function(clickInfo) {
            clearError();

            if (clickInfo.event.extendedProps.kind === 'rdv') {
                alert(
                    'Rendez-vous : ' + clickInfo.event.title +
                    '\nDébut : ' + clickInfo.event.start +
                    '\nFin : ' + clickInfo.event.end
                );
                return;
            }

            const slotId = Number(clickInfo.event.extendedProps.slotId || 0);
            if (!slotId) return;

            const recurrence = String(clickInfo.event.extendedProps.recurrence || 'unique');
            const deleteLabel = recurrence === 'unique'
                ? 'Supprimer ce créneau ?'
                : 'Supprimer cette règle récurrente complète ?';

            const ok = confirm(deleteLabel);
            if (!ok) return;

            try {
                await deleteDisponibilite(base, token, slotId);

                setInfo('Créneau supprimé.');
                calendar.refetchEvents();
            } catch (error) {
                setError(error.message || 'Erreur lors de la suppression du créneau.');
            }
        },
        eventDidMount: function() {}
    });

    calendar.render();
}

init();
</script>
</body>
</html>
