<?php session_start();
include 'includes/api_config.php';
include 'includes/header.php'?>

<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta charset="UTF-8">
    <title>Abonnement</title>
</head>
<body>

<div class="container d-flex justify-content-center mt-5">
  <div id="affihe_liste" class="d-flex gap-4"></div>
</div>
<?php include 'includes/footer.php'?>

<script>
    async function get_abonnement() {

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/abonnement_all", {
        method: "GET",
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();

        const listeAbonnement = document.getElementById("affihe_liste");

        listeAbonnement.innerHTML = "" 
        
        data.forEach(abonnement => {
    listeAbonnement.innerHTML += `
        <div class="card" style="width: 12rem; height: 25rem;">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-center text-uppercase fw-bold">${abonnement.titre}</h5>
                <p class="card-text text-secondary mb-0">À partir de</p>
                <p class="card-text text-primary fw-bold">${abonnement.prix_mois}€/mois, ${abonnement.prix_an}€/an</p>
                <div class="text-center mb-3">
                    <a href="edit_abonnement.php?id=${abonnement.id}" class="btn btn-danger shadow rounded">Modifier</a>
                </div>
                <hr>
                <p class="card-text">${abonnement.contenue1}</p>
                <p class="card-text">${abonnement.contenue2}</p>
                <p class="card-text">${abonnement.contenue3}</p>
                <p class="card-text">${abonnement.contenue4}</p>
            </div>
        </div>
    `;
});
}
window.addEventListener("load", get_abonnement);
</script>

