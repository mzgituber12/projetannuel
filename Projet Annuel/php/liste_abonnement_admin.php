<?php session_start();
include 'includes/api_config.php';
include 'includes/header.php'; ?>

<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta charset="UTF-8">
    <title>Liste Abonnements Admin</title>
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Liste abonnements admin</h1>
        <a href="add_abonnement.php" class="btn btn-danger">Ajouter un abonnement</a>
    </div>
    <div id="liste_abonnements" class="d-flex gap-4 flex-wrap justify-content-center"></div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
async function charger_abonnements_admin() {
    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/abonnement_all", {
        method: "GET",
        headers: {"Token": token}
    });

    if (!response.ok) {
        const text = await response.text();
        alert(text);
        window.location.href = "erreur.php?code=" + response.status;
        return;
    }

    const data = await response.json();
    const container = document.getElementById("liste_abonnements");
    container.innerHTML = "";

    data.forEach(a => {
        container.innerHTML += `
            <div class="card" style="width: 14rem; min-height: 20rem;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-center text-uppercase fw-bold">${a.titre || a.type}</h5>
                    <p class="card-text text-secondary mb-0">À partir de</p>
                    <p class="card-text text-primary fw-bold">${a.prix_mois}€/mois, ${a.prix_an}€/an</p>
                    <p class="card-text">Statut: ${a.statut}</p>
                    <div class="text-center mb-3">
                        <a href="edit_abonnement.php?id=${a.id}" class="btn btn-danger shadow rounded">Modifier</a>
                    </div>
                    <hr>
                    <p class="card-text">${a.contenue1 || ''}</p>
                    <p class="card-text">${a.contenue2 || ''}</p>
                    <p class="card-text">${a.contenue3 || ''}</p>
                    <p class="card-text">${a.contenue4 || ''}</p>
                </div>
            </div>
        `;
    });
}

window.addEventListener("load", charger_abonnements_admin);
</script>

</body>
</html>
