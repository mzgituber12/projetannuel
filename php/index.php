<?php session_start(); include 'includes/api_config.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
</head>
<body>

<?php
include 'includes/header.php';

echo "<div class='text-center mt-2 ms-4'><h1>Accueil</div></h1>";
if (isset($_SESSION['state']) && isset($_GET['message'])) { 
    echo "<h3>" . htmlspecialchars($_GET['message']) . "</h3>";
    unset($_SESSION['state']);
}
?>
<h2>Bienvenue sur notre site</h2>
<p><div id=content></div></p>

<div class="container mt-5 pb-5">
  <div class="row justify-content-center g-4">
    <div class="col-md-4 d-flex justify-content-center">
      <div class="card h-100 d-flex flex-column" style="width: 22rem;">
        <img src="https://yogalipette.com/wp-content/uploads/2020/05/yoga-plein-air.jpg" class="card-img-top" alt="Yoga en plein air">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title">Ce matin yoga en plein air</h5>
          <p class="card-text">
            Ce Mardi 12 février, un cours de Yoga sera donné par notre nouvelle professeur “Noa Dupont”. Vous voulez participer ou en savoir plus clic sur l’actualité 
          <ul class="list-unstyled mb-3">
            <li>Date : 12 février 2025</li>
            <li>Lieux : Parc des bois</li>
          </ul>
          <a href="#" class="btn btn-primary mt-auto w-100">En savoir plus</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 d-flex justify-content-center">
      <div class="card h-100 d-flex flex-column" style="width: 22rem;">
        <img src="https://moncentreaquatique.com/images/1999238861.jpg" class="card-img-top" alt="Atelier mémoire">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title">Natation Synchroniser pour bien se reveiller ! </h5>
          <p class="card-text">
            Suivez un programme personaliser pour chacun avec , Jean Dupont notre coach en natation.
          </p>
          <ul class="list-unstyled mb-3">
            <li>Date : 20 Mars 2025</li>
            <li>Lieux : Pscine Municipale de Paris</li>
          </ul>
          <a href="#" class="btn btn-primary mt-auto w-100">En savoir plus</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 d-flex justify-content-center">
      <div class="card h-100 d-flex flex-column" style="width: 22rem;">
        <img src="https://lemagdusenior.ouest-france.fr/images/dossiers/2024-01/mini/geriatre-103905-1200-600.jpg" class="card-img-top" alt="Nouveau médecin">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title">Médecin gériatre validé par Silver Happy</h5>
          <p class="card-text">
            Des problèmes de dos, mal à l’épaule, des douleurs après une promenade, reserver votre rendez vous avec notre médecin agréé
          </p>
          <ul class="list-unstyled mb-3">
            <li>Lieux : Centre Médical de Lyon</li>
            <li>Spécialité : Gériatrie</li>
          </ul>
          <a href="#" class="btn btn-primary mt-auto w-100">Prendre rendez-vous</a>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'?>

<script>
async function onlineUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Content-Type": "application/json", "Token": token},
    });

    if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
    const data = await response.json();
    if (data.message == "Identifié"){
        document.getElementById("content").innerHTML = "Vous êtes connecté";
        if (data.tutoriel == "1"){
            document.getElementById("content").innerHTML += "</p><p>C'est votre 1ere experience sur le site ? Voici le tutoriel pour vous aider";
        }
    } else if (data.message == "Pas identifié"){
        document.getElementById("content").innerHTML = "Veuillez vous connecter pour poursuivre";
    }
}

async function init(){
        const token = localStorage.getItem("token")
        onlineUser(token)
    }

init()
</script>

</body>
</html>