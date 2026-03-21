<?php session_start();
include 'includes/api_config.php';
include 'includes/header.php'?>

<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta charset="UTF-8">
    <title>Mon Profil</title>
</head>
<body>

<div class="container d-flex justify-content-center mt-5">
  <div class="d-flex gap-4">
    

    <div class="card" style="width: 12rem; height: 25rem;">
      <div class="card-body">
        <h5 class="card-title text-center">Basic</h5>
        <p class="card-text text-secondary">A partir de</p>
        <p class="card-text text-primary">4€/mois, 40€/an</p>
        <div class="text-center mb-3">
          <a href="#" class="btn btn-danger shadow rounded">Choisir</a>
        </div>
        <hr>
        <p class="card-text">✅ Locaux pour prestation</p>
        <p class="card-text">✅ Trajet offert</p>
        <p class="card-text">⛔ Trajet offert</p>
        <p class="card-text">⛔ Trajet offert</p>
      </div>
    </div>


    <div class="card" style="width: 12rem; height: 25rem;">
      <div class="card-body">
        <h5 class="card-title text-center">Standard</h5>
        <p class="card-text text-secondary">A partir de</p>
        <p class="card-text text-primary">3€/mois, 35€/an</p>
        <div class="text-center mb-3">
          <a href="#" class="btn btn-danger shadow rounded">Choisir</a>
        </div>
        <hr>
        <p class="card-text">✅ Locaux pour prestation</p>
        <p class="card-text">✅ Trajet offert</p>
        <p class="card-text">✅ Trajet offert</p>
        <p class="card-text">⛔ Trajet offert</p>
      </div>
    </div>



    <div class="card" style="width: 12rem; height: 25rem;">
      <div class="card-body">
        <h5 class="card-title text-center">Premium</h5>
        <p class="card-text text-secondary">A partir de</p>
        <p class="card-text text-primary">6€/mois, 72€/an</p>
        <div class="text-center mb-3">
          <a href="#" class="btn btn-danger shadow rounded">Choisir</a>
        </div>
        <hr>
        <p class="card-text">✅ Locaux pour prestation</p>
        <p class="card-text">✅ Trajet offert</p>
        <p class="card-text">✅ Trajet offert</p>
        <p class="card-text">✅ Trajet offert</p>
      </div>
    </div>


  </div>
</div>
<?php include 'includes/footer.php'?>

<script>
    async function search_user() {

        const token = localStorage.getItem("token");
        
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/mon_profil", {
        method: "GET",
        headers: {"Token": token},
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();

            document.getElementById("mon_profil").innerHTML = 
            "<label>ID : " + data.id + "</label><br>" +
            "<label>Nom : " + data.nom + "</label><br>" +
            "<label>Prénom : " + data.prenom + "</label><br>" +
            "<label>Âge : " + data.age + "</label><br>" +
            "<label>Email : " + data.email + "</label><br>" +
            "<label>Role : " + data.role + "</label><br>" +
            "<label>Langue : " + data.langue + "</label><br>" +
            "<a href='modifier_user.php?id=" + data.id + "'>Modifier l'utilisateur</a>";
        }
window.onload = search_user;
</script>