<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des interventions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">Gestion des interventions</h1>

    <?php
    if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_GET['message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['state']);
    }?>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Rechercher une intervention</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search_intervention(event); return false;" class="row g-3">
                <div class="col-md-8">
                    <input id="intervention_id" placeholder="ID d'intervention..." type="text" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100">Rechercher</button>
                </div>
            </form>
        </div>
    </div>

    <div id="resultat"></div>
</div>

<?php include 'includes/footer.php'?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
    async function search_intervention(event) {
        event.preventDefault();
        const id = document.getElementById("intervention_id").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_intervention/" + id, {
            method: "GET",
        });
        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
        const data = await response.json();

        if(data.id == 0 || !data.id) {
            document.getElementById("resultat").innerHTML = "<div class='alert alert-warning'>Aucune intervention trouvée</div>";
        }else {
            document.getElementById("resultat").innerHTML = 
            "<div class='card'><div class='card-body'>" +
            "<div class='row'><div class='col-md-6'>" +
            "<p><strong>ID :</strong> " + data.id + "</p>" +
            "<p><strong>ID Service :</strong> " + data.id_service + "</p>" +
            "<p><strong>ID Prestataire :</strong> " + data.id_prestataire + "</p>" +
            "</div><div class='col-md-6'>" +
            "<p><strong>ID Utilisateur :</strong> " + data.id_utilisateur + "</p>" +
            "<p><strong>Date :</strong> " + data.date + "</p>" +
            "<p><strong>Statut :</strong> " + data.statut + "</p>" +
            "<p><strong>Montant :</strong> " + data.montant + "€</p>" +
            "</div></div>" +
            "<a href='modifier_intervention.php?id=" + data.id + "' class='btn btn-warning'>Modifier l'intervention</a>" +
            "</div></div>";
        }
    }

    async function listIntervention(token) {
       //
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        listIntervention(token);
    }

window.addEventListener('pageshow', function(event) {
if (event.persisted) {
    window.location.reload();
}
});

init()
</script>

</body>
</html>
