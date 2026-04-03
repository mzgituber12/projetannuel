<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Planning</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
</head>
<body>

<?php include 'includes/header.php' ?>

<h1 data-i18n> Planning </h1>

<div id="calendar"></div>

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
        events: base + "/planning_rdv",

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
