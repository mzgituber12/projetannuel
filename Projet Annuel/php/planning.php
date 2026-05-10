<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Planning</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php' ?>

<div class='container mt-5'>
<h1 data-i18n class='mb-custom text-center ms-4 mb-4' style='font-size:50px' id="popover3" data-bs-toggle="popover" data-bs-title="Planning Personel" data-bs-content="Consultez votre emploie du temps ici.<br>Vous retrouverez aussi vos réservations et évenements.<br> Pour la suite de ce tutoriel cliquer sur le Menu Burger en haut a droite puis sur 'Mon profil'<br><div class='d-flex justify-content-between align-items-center mt-3'><button class='btn btn-sm btn-primary mt-2' onclick='tuto()'>Suivant</button><button class='btn btn-sm btn-danger mt-2' onclick='fin_tuto()'>Arreter le Tuto</button></div>"> Planning </h1>
<div id="calendar"></div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
init();

async function init() {
    const token = localStorage.getItem("token");
    if (!await loginUser("online", token)) return;

    const base = (window.API_BASE || 'http://localhost:9000');
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        firstDay: 1,
        weekends: true,
        hiddenDays: [],
        slotMinTime: "06:00:00",
        slotMaxTime: "24:00:00",
        slotDuration: "00:30:00",
        allDaySlot: false,
        locale: "fr",
        height: "auto",
        events: async function (info) {
            const url = new URL(base + "/planning_rdv");
            url.searchParams.set("start", info.startStr);
            url.searchParams.set("end", info.endStr);
            const res = await fetch(url.toString(), {
                headers: { Token: token }
            });
            if (!res.ok) {
                throw new Error("Chargement des rendez-vous impossible");
            }
            return await res.json();
        },

        eventClick: function(info) {
            alert(
                "Rendez-vous : " + info.event.title +
                "\nDébut : " + info.event.start +
                "\nFin : " + info.event.end
            );
        }
    });

    calendar.render();
}
</script>


</body>
</html>
