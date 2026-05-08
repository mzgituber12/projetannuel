<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Réservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php' ?>

<div class="container mt-5 mb-5">
    <h1 class="mb-4" data-i18n>Réservation</h1>

    <?php if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
        unset($_SESSION['state']);
    }
    ?>

<?php
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nom = isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : '';
$date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : '';
$tarif = isset($_GET['tarif']) ? htmlspecialchars($_GET['tarif']) : '';
$description = isset($_GET['description']) ? htmlspecialchars($_GET['description']) : '';
$prestataire = isset($_GET['prestataire']) ? htmlspecialchars($_GET['prestataire']) : '';
$image = isset($_GET['image']) ? trim($_GET['image']) : '';
$imageUrl = '';
if ($image !== '') {
    if (preg_match('/^https?:\/\//i', $image) || str_starts_with($image, '/')) {
        $imageUrl = htmlspecialchars($image, ENT_QUOTES);
    } else {
        $imageUrl = 'upload/' . rawurlencode($image);
    }
}
?>

<?php if ($type === 'evenement' && $id > 0) : ?>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="h4 card-title mb-3"><span data-i18n>Événement :</span> <?= $nom ?></h2>
                    <?php if ($imageUrl !== '') : ?>
                        <img src="<?= $imageUrl ?>" alt="Image de l'événement" class="img-fluid rounded mb-3" style="max-width: 100%; max-height: 350px; object-fit: cover;">
                    <?php endif; ?>
                    <p class="card-text"><strong data-i18n>Date :</strong> <?= $date ?></p>
                    <p class="card-text"><strong data-i18n>Description :</strong></p>
                    <p class="card-text"><?= $description ?></p>
                    <p class="card-text"><strong data-i18n>Tarif sur place :</strong> <span class="text-primary fw-bold"><?= $tarif ?> €</span></p>

                    <button id="joinEvent" class="btn btn-primary btn-lg" data-i18n>Rejoindre cet événement</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        async function joinEvent() {
            const token = localStorage.getItem('token');
            if (!token) {
                alert('Vous devez être connecté pour réserver.');
                return;
            }

            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + '/reservation_evenement', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Token': token
                },
                body: JSON.stringify({
                    id_evenement: <?= $id ?>
                })
            });

            if (!response.ok) {
                const text = await response.text();
                alert(text);
                window.location.href = 'erreur.php?code=' + response.status;
                return;
            }

            const data = await response.json();
            await fetch('ajouter_session_state.php', { method: 'POST' });
            window.location.href = 'catalogue.php?message=' + encodeURIComponent(data.message || 'Réservation confirmée');
        }

        document.getElementById('joinEvent').addEventListener('click', joinEvent);
    </script>
<?php elseif ($type === 'service' && $id > 0) : ?>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="h4 card-title mb-3"><span data-i18n>Service :</span> <?= $nom ?></h2>
                    <?php if ($imageUrl !== '') : ?>
                        <img src="<?= $imageUrl ?>" alt="Image du service" class="img-fluid rounded mb-3" style="max-width: 100%; max-height: 350px; object-fit: cover;">
                    <?php endif; ?>
                    <p class="card-text"><strong data-i18n>Description :</strong></p>
                    <p class="card-text"><?= $description ?></p>
                    <p class="card-text"><strong data-i18n>Tarif :</strong> <span class="text-primary fw-bold"><?= $tarif ?> €</span></p>

                    <h5 class="mt-4 mb-3" data-i18n>Disponibilités</h5>
                    <div id="calendar" class="mb-3"></div>
                    <div id="calendarError" class="alert alert-danger d-none" role="alert"></div>

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-sm-3">
                            <label for="yearSelect" class="form-label small fw-bold" data-i18n>Année</label>
                            <select id="yearSelect" class="form-select form-select-sm"></select>
                        </div>
                        <div class="col-6 col-sm-3">
                            <label for="monthSelect" class="form-label small fw-bold" data-i18n>Mois</label>
                            <select id="monthSelect" class="form-select form-select-sm"></select>
                        </div>
                        <div class="col-6 col-sm-3">
                            <label for="daySelect" class="form-label small fw-bold" data-i18n>Jour</label>
                            <select id="daySelect" class="form-select form-select-sm"></select>
                        </div>
                        <div class="col-6 col-sm-3">
                            <label for="hourSelect" class="form-label small fw-bold" data-i18n>Heure</label>
                            <select id="hourSelect" class="form-select form-select-sm"></select>
                        </div>
                    </div>

                    <p id="selectionInfo" class="mt-3 text-muted small"></p>
                    <div id="slotsList" class="mt-2 mb-3"></div>
                    <div class="border rounded p-3 bg-white mb-3">
                        <h6 class="mb-2" data-i18n>Paiement de la réservation</h6>
                        <p class="text-muted small mb-3" data-i18n>Comme pour le panier boutique, vous pouvez payer par carte (Stripe) ou indiquer un virement ; la réservation est enregistrée après validation du paiement (carte) ou immédiatement avec les coordonnées de virement.</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" id="payServiceStripe" class="btn btn-primary btn-lg" data-i18n>Réserver et payer par carte</button>
                            <button type="button" id="payServiceTransfer" class="btn btn-success btn-lg" data-i18n>Réserver et payer par virement</button>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-outline-primary btn-lg" href="demande_devis_service.php?id=<?= $id ?>&nom=<?= urlencode($nom) ?>&description=<?= urlencode($description) ?>&tarif=<?= urlencode($tarif) ?>&image=<?= urlencode($image) ?>" data-i18n>Demander un devis</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const serviceId = <?= $id ?>;
        let availabilityByDate = {};
        let currentMonth = new Date();
        let selectedDateKey = '';

        function pad(num) {
            return num.toString().padStart(2, '0');
        }

        function formatYMD(date) {
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
        }

        function buildCalendar() {
            const year = currentMonth.getFullYear();
            const month = currentMonth.getMonth();
            const start = new Date(year, month, 1);
            const end = new Date(year, month + 1, 0);
            const firstDay = start.getDay();

            const calendar = document.getElementById('calendar');
            calendar.innerHTML = '';

            const header = document.createElement('div');
            header.style.display = 'flex';
            header.style.alignItems = 'center';
            header.style.justifyContent = 'space-between';
            header.style.marginBottom = '0.5rem';

            const title = document.createElement('div');
            title.textContent = `${year} - ${pad(month + 1)}`;
            header.appendChild(title);

            const buttons = document.createElement('div');
            const prev = document.createElement('button');
            prev.type = 'button';
            prev.textContent = '◀';
            prev.addEventListener('click', () => {
                currentMonth.setMonth(currentMonth.getMonth() - 1);
                buildCalendar();
            });
            const next = document.createElement('button');
            next.type = 'button';
            next.textContent = '▶';
            next.addEventListener('click', () => {
                currentMonth.setMonth(currentMonth.getMonth() + 1);
                buildCalendar();
            });
            buttons.appendChild(prev);
            buttons.appendChild(next);
            header.appendChild(buttons);

            calendar.appendChild(header);

            const table = document.createElement('table');
            table.style.width = '100%';
            table.style.borderCollapse = 'collapse';

            const weekdays = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
            const headRow = document.createElement('tr');
            weekdays.forEach(d => {
                const th = document.createElement('th');
                th.textContent = d;
                th.style.padding = '4px';
                th.style.border = '1px solid #ddd';
                th.style.background = '#f7f7f7';
                headRow.appendChild(th);
            });
            table.appendChild(headRow);

            let row = document.createElement('tr');
            for (let i = 0; i < firstDay; i++) {
                const td = document.createElement('td');
                td.style.padding = '8px';
                td.style.border = '1px solid #ddd';
                row.appendChild(td);
            }

            for (let day = 1; day <= end.getDate(); day++) {
                if (row.children.length === 7) {
                    table.appendChild(row);
                    row = document.createElement('tr');
                }

                const td = document.createElement('td');
                td.style.padding = '8px';
                td.style.border = '1px solid #ddd';
                td.style.cursor = 'pointer';

                const date = new Date(year, month, day);
                const dateKey = formatYMD(date);
                const slots = availabilityByDate[dateKey] || [];
                const hasAvailable = slots.some(s => s.type === 'disponible');
                const hasUnavailable = slots.some(s => s.type === 'indisponible');

                td.textContent = day;
                if (hasAvailable) {
                    td.style.background = '#d1fae5';
                    td.title = 'Jour avec des créneaux disponibles';
                } else if (hasUnavailable) {
                    td.style.background = '#fee2e2';
                    td.title = 'Jour indisponible (créneaux pris)';
                }

                td.addEventListener('click', () => {
                    selectedDateKey = dateKey;
                    document.getElementById('selectionInfo').textContent = '';
                    document.getElementById('calendarError').textContent = '';
                    document.getElementById('yearSelect').value = year;
                    document.getElementById('monthSelect').value = month + 1;

                    updateDayOptions();
                    document.getElementById('daySelect').value = day;
                    updateHourOptions();

                    renderSlotsForDay(dateKey);

                    if (hasAvailable) {
                        const slot = slots.find(s => s.type === 'disponible');
                        const startTime = slot.start.split(' ')[1];
                        document.getElementById('hourSelect').value = startTime.split(':')[0];
                    }

                    buildSelectionInfo();
                });

                row.appendChild(td);
            }

            while (row.children.length < 7) {
                const td = document.createElement('td');
                td.style.padding = '8px';
                td.style.border = '1px solid #ddd';
                row.appendChild(td);
            }
            table.appendChild(row);
            calendar.appendChild(table);
        }

        function updateDayOptions() {
            const year = parseInt(document.getElementById('yearSelect').value, 10);
            const month = parseInt(document.getElementById('monthSelect').value, 10);
            const daySelect = document.getElementById('daySelect');
            const daysInMonth = new Date(year, month, 0).getDate();

            daySelect.innerHTML = '';
            for (let d = 1; d <= daysInMonth; d++) {
                const option = document.createElement('option');
                option.value = d;
                option.textContent = d;
                daySelect.appendChild(option);
            }
        }

        function updateHourOptions() {
            const hourSelect = document.getElementById('hourSelect');
            hourSelect.innerHTML = '';
            for (let h = 0; h < 24; h++) {
                const option = document.createElement('option');
                option.value = pad(h);
                option.textContent = pad(h) + 'h';
                hourSelect.appendChild(option);
            }
        }

        async function fetchDisponibilites() {
            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(`${base}/service_disponible?id=${serviceId}`);
            if (!response.ok) {
                document.getElementById('calendarError').textContent = 'Impossible de charger les disponibilités.';
                document.getElementById('calendarError').classList.remove('d-none');
                return;
            }

            const slots = await response.json();
            availabilityByDate = {};

            if (!Array.isArray(slots)) {
                document.getElementById('calendarError').textContent = 'Format de disponibilité invalide.';
                document.getElementById('calendarError').classList.remove('d-none');
                return;
            }

            slots.forEach(slot => {
                const [date] = slot.start.split(' ');
                if (!availabilityByDate[date]) availabilityByDate[date] = [];
                availabilityByDate[date].push(slot);
            });

            buildCalendar();

            if (selectedDateKey) {
                renderSlotsForDay(selectedDateKey);
            }
        }

        function renderSlotsForDay(dateKey) {
            const slotsList = document.getElementById('slotsList');
            slotsList.innerHTML = '';

            const slots = availabilityByDate[dateKey] || [];
            if (slots.length === 0) {
                slotsList.textContent = 'Aucun créneau connu pour cette date.';
                return;
            }

            slots.forEach(slot => {
                const div = document.createElement('div');
                div.style.display = 'flex';
                div.style.justifyContent = 'space-between';
                div.style.alignItems = 'center';
                div.style.padding = '0.4rem 0.6rem';
                div.style.border = '1px solid #ddd';
                div.style.borderRadius = '4px';
                div.style.marginBottom = '0.4rem';
                div.style.background = slot.type === 'disponible' ? '#ecfdf5' : '#fef2f2';

                const label = document.createElement('span');
                const startTime = slot.start.split(' ')[1];
                const endTime = slot.end.split(' ')[1];
                label.textContent = `${startTime} → ${endTime}`;
                div.appendChild(label);

                const right = document.createElement('span');
                if (slot.type === 'disponible') {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = 'Sélectionner';
                    btn.addEventListener('click', () => {
                        const [date] = slot.start.split(' ');
                        const [h] = startTime.split(':');
                        const [y, mo, d] = date.split('-');
                        document.getElementById('yearSelect').value = y;
                        document.getElementById('monthSelect').value = parseInt(mo, 10);
                        updateDayOptions();
                        document.getElementById('daySelect').value = parseInt(d, 10);
                        updateHourOptions();
                        document.getElementById('hourSelect').value = h;
                        selectedDateKey = date;
                        buildSelectionInfo();
                    });
                    right.appendChild(btn);
                } else {
                    right.textContent = 'Indisponible';
                    right.style.color = '#b91c1c';
                }

                div.appendChild(right);
                slotsList.appendChild(div);
            });
        }

        function buildSelectionInfo() {
            const year = document.getElementById('yearSelect').value;
            const month = pad(document.getElementById('monthSelect').value);
            const day = pad(document.getElementById('daySelect').value);
            const hour = document.getElementById('hourSelect').value;
            const minute = '00';

            document.getElementById('selectionInfo').textContent = `Date sélectionnée : ${year}-${month}-${day} ${hour}:${minute}`;
        }

        function buildStartForReservation() {
            const year = document.getElementById('yearSelect').value;
            const month = pad(document.getElementById('monthSelect').value);
            const day = pad(document.getElementById('daySelect').value);
            const hour = document.getElementById('hourSelect').value;
            const minute = '00';
            return `${year}-${month}-${day} ${hour}:${minute}`;
        }

        function validateSlotSelection(start) {
            document.getElementById('calendarError').classList.add('d-none');
            document.getElementById('calendarError').textContent = '';
            const selectedDate = start.split(' ')[0];
            const slots = availabilityByDate[selectedDate] || [];

            if (slots.length === 0) {
                document.getElementById('calendarError').textContent = 'Pas de disponibilité pour cette date.';
                document.getElementById('calendarError').classList.remove('d-none');
                return false;
            }

            const isValid = slots.some(slot => {
                if (slot.type !== 'disponible') return false;
                const slotStart = new Date(slot.start);
                const slotEnd = new Date(slot.end);
                const candidate = new Date(start);
                return candidate >= slotStart && candidate < slotEnd;
            });

            if (!isValid) {
                document.getElementById('calendarError').textContent = 'Le créneau sélectionné n’est pas dans une période disponible.';
                document.getElementById('calendarError').classList.remove('d-none');
                return false;
            }
            return true;
        }

        async function reserveWithPayment(paymentMethod) {
            const token = localStorage.getItem('token');
            if (!token) {
                alert('Vous devez être connecté pour réserver.');
                return;
            }

            const start = buildStartForReservation();
            if (!validateSlotSelection(start)) {
                return;
            }

            const base = (window.API_BASE || 'http://localhost:9000');
            const resp = await fetch(base + '/paiement_reservation_service', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Token': token },
                body: JSON.stringify({
                    id_service: serviceId,
                    start: start,
                    payment_method: paymentMethod
                })
            });

            const text = await resp.text();
            let data = null;
            try { data = JSON.parse(text); } catch { data = { message: text }; }

            if (!resp.ok) {
                document.getElementById('calendarError').textContent = data.message || text || 'Erreur lors de la réservation.';
                document.getElementById('calendarError').classList.remove('d-none');
                return;
            }

            if (paymentMethod === 'stripe' && data.url) {
                window.location.href = data.url;
                return;
            }

            if (paymentMethod === 'transfer') {
                const ref = (data.virement && data.virement.reference) ? data.virement.reference : '';
                const iban = (data.virement && data.virement.iban) ? data.virement.iban : '';
                const msg = (data.message || 'Réservation enregistrée.') + (ref ? '\nRéférence virement : ' + ref : '') + (iban ? '\nIBAN : ' + iban : '');
                alert(msg);
                await fetch('ajouter_session_state.php', { method: 'POST' });
                window.location.href = 'catalogue.php?message=' + encodeURIComponent(data.message || 'Réservation confirmée');
                return;
            }

            await fetch('ajouter_session_state.php', { method: 'POST' });
            window.location.href = 'catalogue.php?message=' + encodeURIComponent(data.message || 'Réservation confirmée');
        }

        function initSelects() {
            const today = new Date();
            const yearSelect = document.getElementById('yearSelect');
            const monthSelect = document.getElementById('monthSelect');

            for (let y = today.getFullYear(); y <= today.getFullYear() + 1; y++) {
                const option = document.createElement('option');
                option.value = y;
                option.textContent = y;
                yearSelect.appendChild(option);
            }

            for (let m = 1; m <= 12; m++) {
                const option = document.createElement('option');
                option.value = m;
                option.textContent = pad(m);
                monthSelect.appendChild(option);
            }

            yearSelect.value = today.getFullYear();
            monthSelect.value = today.getMonth() + 1;

            updateDayOptions();
            document.getElementById('daySelect').value = today.getDate();
            updateHourOptions();
            buildSelectionInfo();

            selectedDateKey = formatYMD(today);

            yearSelect.addEventListener('change', () => {
                updateDayOptions();
                buildSelectionInfo();
                selectedDateKey = `${yearSelect.value}-${pad(monthSelect.value)}-${pad(document.getElementById('daySelect').value)}`;
                renderSlotsForDay(selectedDateKey);
            });
            monthSelect.addEventListener('change', () => {
                updateDayOptions();
                buildSelectionInfo();
                selectedDateKey = `${yearSelect.value}-${pad(monthSelect.value)}-${pad(document.getElementById('daySelect').value)}`;
                renderSlotsForDay(selectedDateKey);
            });
            document.getElementById('daySelect').addEventListener('change', () => {
                buildSelectionInfo();
                selectedDateKey = `${document.getElementById('yearSelect').value}-${pad(document.getElementById('monthSelect').value)}-${pad(document.getElementById('daySelect').value)}`;
                renderSlotsForDay(selectedDateKey);
            });
            document.getElementById('hourSelect').addEventListener('change', buildSelectionInfo);
        }

        initSelects();
        fetchDisponibilites();
        document.getElementById('payServiceStripe').addEventListener('click', () => reserveWithPayment('stripe'));
        document.getElementById('payServiceTransfer').addEventListener('click', () => reserveWithPayment('transfer'));
    </script>
<?php else : ?>
    <div class="alert alert-warning" role="alert">
        <strong data-i18n>Erreur :</strong> <span data-i18n>Informations manquantes ou invalides.</span>
    </div>
<?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
