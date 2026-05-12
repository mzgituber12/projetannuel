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
    <title data-i18n>Liste Attente Préstataire</title>
    <link rel="stylesheet" href="police.css">
</head>
<body>


<div class='container mt-5'>
    <h1 data-i18n class='mb-custom text-center ms-4' style='font-size:50px'>Validation des prestataires</h1>
    <?php
    if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_GET['message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['state']);
    }?>

<div class="container mt-5">
    <div id="liste_demande_presta" class="d-flex gap-4 flex-wrap justify-content-center"></div>
</div>

<div class="mb-4"></div>

</div>

<?php include 'includes/footer.php'; ?>

<script>
async function charger_abonnements_admin() {
    const token = localStorage.getItem("token") || "";
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/liste_attente_validation", {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "Token": token}
    });

    if (!response.ok) {
        const text = await response.text();
        alert(text);
        window.location.href = "erreur.php?code=" + response.status;
        return;
    }

    const data = await response.json();
    const container = document.getElementById("liste_demande_presta");
    container.innerHTML = "";

    if (!Array.isArray(data) || data.length == 0) {
        container.innerHTML = `
            <div class="alert alert-info text-center w-100" role="alert">
                Aucune demande en attente pour le moment.
            </div>
        `;
        return;
    }

    data.forEach(a => {

    container.innerHTML += `
        <div class="card shadow-sm" style="width: 16rem; min-height: 22rem; margin: 10px; border-radius: 15px;">
            <div class="card-body d-flex flex-column align-items-center">
                <div class="mb-3">
                    <img src="upload/${a.photo_profil}" alt="Profil" class="rounded-circle shadow" 
                         style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #dc3545;">
                </div>

                <h5 class="card-title text-center text-uppercase fw-bold mb-1">
                    ${a.nom}
                </h5>
                <h5 class="card-title text-center text-uppercase fw-bold mb-1">
                    ${a.prenom}
                </h5>

                <p class="badge bg-info text-dark mb-3">${a.categorie}</p>
                
                <p class="card-text text-secondary text-center small mb-4">
                    En attente de validation
                </p>

                <div class="mt-auto w-100">
                    <a href="validation.php?id=${a.id}" 
                       class="btn btn-danger w-100 shadow-sm rounded-pill">
                       Examiner
                    </a>
                </div>
            </div>
        </div>
    `;
});
}

window.addEventListener("load", charger_abonnements_admin);
</script>

</body>
</html>
