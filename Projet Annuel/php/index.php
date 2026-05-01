<?php include 'includes/api_config.php';?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Accueil</title>
</head>
<body>

<?php
include 'includes/header.php';

echo "<div class='text-center mt-2 ms-4'><h1 data-i18n>Accueil</h1></div>";
if (isset($_SESSION['state']) && isset($_GET['message'])) { 
    echo "<h3 data-i18n>" . htmlspecialchars($_GET['message']) . "</h3>";
    unset($_SESSION['state']);
}
?>
<h2 data-i18n>Bienvenue sur notre site</h2>
<div id="content" data-i18n></div>
<div id="reco_wrapper" class="container mt-4" style="display:none;">
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <strong data-i18n>Service recommandé pour vous</strong>
        </div>
        <div class="card-body">
            <div id="reco_main"></div>
            <hr>
            <h6 class="mb-2" data-i18n>Alternatives</h6>
            <div id="reco_alternatives" class="row g-3"></div>
        </div>
    </div>
</div>

<div class="container mt-5 pb-5">
  <div class="row justify-content-center g-4">
    <div id="popover1" class="col-md-4 d-flex justify-content-center" data-bs-toggle="popover" data-bs-title="Popover1" data-bs-content="Voici le tuto <br><button class='btn btn-sm btn-primary mt-2' onclick='tuto()'>Suivant</button>">
      <div class="card h-100 d-flex flex-column" style="width: 22rem;">
        <img src="https://yogalipette.com/wp-content/uploads/2020/05/yoga-plein-air.jpg" class="card-img-top" alt="Yoga en plein air">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title" data-i18n>Ce matin yoga en plein air</h5>
          <p class="card-text" data-i18n>
            Ce Mardi 12 février, un cours de Yoga sera donné par notre nouvelle professeur “Noa Dupont”. Vous voulez participer ou en savoir plus clic sur l’actualité 
          <ul class="list-unstyled mb-3">
            <li data-i18n>Date : 12 février 2025</li>
            <li data-i18n>Lieux : Parc des bois</li>
          </ul>
          <a href="#" class="btn btn-primary mt-auto w-100" data-i18n>En savoir plus</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 d-flex justify-content-center">
      <div id="popover2" class="card h-100 d-flex flex-column" style="width: 22rem;" data-bs-toggle="popover" data-bs-title="Popover2" data-bs-content="Voici le+a fin du tuto <br><button class='btn btn-sm btn-primary mt-2' onclick='fin_tuto()'>Terminer</button>">
        <img src="https://moncentreaquatique.com/images/1999238861.jpg" class="card-img-top" alt="Atelier mémoire">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title" data-i18n>Natation Synchroniser pour bien se reveiller ! </h5>
          <p class="card-text" data-i18n>
            Suivez un programme personaliser pour chacun avec , Jean Dupont notre coach en natation.
          </p>
          <ul class="list-unstyled mb-3">
            <li data-i18n>Date : 20 Mars 2025</li>
            <li data-i18n>Lieux : Pscine Municipale de Paris</li>
          </ul>
          <a href="#" class="btn btn-primary mt-auto w-100" data-i18n>En savoir plus</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 d-flex justify-content-center">
      <div class="card h-100 d-flex flex-column" style="width: 22rem;">
        <img src="https://lemagdusenior.ouest-france.fr/images/dossiers/2024-01/mini/geriatre-103905-1200-600.jpg" class="card-img-top" alt="Nouveau médecin">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title" data-i18n>Médecin gériatre validé par Silver Happy</h5>
          <p class="card-text" data-i18n>
            Des problèmes de dos, mal à l’épaule, des douleurs après une promenade, reserver votre rendez vous avec notre médecin agréé
          </p>
          <ul class="list-unstyled mb-3">
            <li data-i18n>Lieux : Centre Médical de Lyon</li>
            <li data-i18n>Spécialité : Gériatrie</li>
          </ul>
          <a href="#" class="btn btn-primary mt-auto w-100" data-i18n>Prendre rendez-vous</a>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'?>

<script>
function predictionCard(prediction, large) {
    const colClass = large ? "col-12" : "col-md-4";
    const label = String(prediction.service_trouver || "Inconnu");
    const score = Number(prediction.score || 0);
    const scoreText = score > 0 ? Math.round(score * 100) + "%" : "n/a";
    return "<div class='" + colClass + "'><div class='card h-100'><div class='card-body'>" +
        "<h5 class='card-title'>Prédiction: " + label + "</h5>" +
        "<div class='small text-muted'>Confiance: " + scoreText + "</div>" +
        "</div></div></div>";
}

async function chargerRecommandation(token) {
    if (!token) return;
    const base = (window.API_BASE || 'http://localhost:9000');
    try {
        const response = await fetch(base + "/services_recommandes", {
            method: "GET",
            headers: {"Token": token}
        });
        if (!response.ok) return;
        const data = await response.json();
        if (!data || !data.principal || !data.principal.service_trouver) return;

        document.getElementById("reco_wrapper").style.display = "block";
        document.getElementById("reco_main").innerHTML = "<div class='row g-3'>" + predictionCard(data.principal, true) + "</div>";

        const alternatives = Array.isArray(data.alternatives) ? data.alternatives : [];
        let htmlAlt = "";
        alternatives.forEach(prediction => {
            htmlAlt += predictionCard(prediction, false);
        });
        document.getElementById("reco_alternatives").innerHTML = htmlAlt || "<p class='text-muted'>Aucune alternative disponible.</p>";
    } catch (_) {
        
    }
}

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
    const langue = data.langue || "fr";

    if (data.message == "Identifié") {
      const msgConnecte = await traduireText("Vous êtes connecté", langue);
      if (data.tutoriel == "1") {
        const msgTutoriel = await traduireText("C'est votre 1ere experience sur le site ? Voici le tutoriel pour vous aider", langue);
        document.getElementById("content").innerHTML = msgConnecte + "<br>" + msgTutoriel;
      } else {
        document.getElementById("content").innerHTML = msgConnecte;
      }
      await chargerRecommandation(token);
    } else if (data.message == "Pas identifié") {
      const msgPasConnecte = await traduireText("Veuillez vous connecter pour poursuivre", langue);
      document.getElementById("content").innerHTML = msgPasConnecte;
      document.getElementById("reco_wrapper").style.display = "none";
    }
}

async function init(){
        const token = localStorage.getItem("token")
        onlineUser(token)
    }

init()
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="tuto.js"></script>
</body>
</html>
