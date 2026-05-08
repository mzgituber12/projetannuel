<?php include 'includes/api_config.php';?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Accueil</title>
    <link rel="stylesheet" href="police.css">
    <style>
      .mb-custom{
        margin-bottom: 2.3rem
      }
      .mt-custom{
        margin-top: 2.3rem
      }
    </style>
</head>
<body>

<?php
include 'includes/header.php';
?>
<div class="mb-custom mt-4 text-center ms-4" style='font-size:50px' data-i18n data-i18n id="popover1" class="col-md-4 d-flex justify-content-center" data-bs-toggle="popover" data-bs-title="Tutoriel" data-bs-content="Bienvenue sur le tutoriel proposé par Silver Happy, laissez nous vous guider pour découvrir notre site web. Sur la page d'accueil vous retrouvrez nos recommandation, l'actualité et divers produit du moment.<br> <br>Pour poursuivre le tutoriel cliquer sur le bouton Suivant juste en dessous<br><div class='d-flex justify-content-between align-items-center mt-3'><button class='btn btn-sm btn-primary mt-2' onclick='tuto()'>Suivant</button><button class='btn btn-sm btn-danger mt-2' onclick='fin_tuto()'>Arreter le Tuto</button></div>"><h1 data-i18n>Accueil</h1></div>

<?php
echo "<div class='container mt-5'>";
if (isset($_SESSION['state']) && isset($_GET['message'])) { 
    echo "<h3 data-i18n>" . htmlspecialchars($_GET['message']) . "</h3>";
    unset($_SESSION['state']);
}
?>
<h2 class='mb-3' data-i18n>Silver Happy, les seniors sont encore jeunes</h2>
<h5><div id="content"class='mb-5' data-i18n ></div></h5>

<div class="container mt-4">
  <h2 class="text-center mt-4 mb-custom" data-i18n>Prestations à l'affiche</h2>

  <div class="row justify-content-center g-4">
    <div id="events-container" class="row g-3"></div>
  </div>
</div>

<div id="reco_wrapper" class="container mt-custom" style="display:none">
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

</div>
<div class='mb-4'></div>

<?php include 'includes/footer.php'?>

<script>
async function evenements_populaire(){
  const base = (window.API_BASE || 'http://localhost:9000');
  const response = await fetch(base + "/evenements", {
    method:"GET",
    });
  if (!response.ok) return;
  const data = await response.json();
  const derniers = data.evenement.slice(-3);
  derniers.forEach(event =>{
    nom = event.nom
    nom = nom.charAt(0).toUpperCase() + nom.slice(1);

    description = event.description
    description = description.charAt(0).toUpperCase() + description.slice(1);
    
    date = event.date
    const datePart = date.split("||")[0].trim();
    const [day, month, year] = datePart.split("/");
    switch (month){
      case "01":
        month_str = "Janvier"
        break;
      case "02":
        month_str = "Février"
        break;
      case "03":
        month_str = "Mars"
        break;
      case "04":
        month_str = "Avril"
        break;
      case "05":
        month_str = "Mai"
        break;
      case "06":
        month_str = "Juin"
        break;
      case "07":
        month_str = "Juillet"
        break;
      case "08":
        month_str = "Aout"
        break
      case "09":
        month_str = "Septembre"
        break;
      case "10":
        month_str = "Octobre"
        break;
      case "11":
        month_str = "Novembre"
        break;
      default:
        month_str = "Décembre"
    }

    image = event.image
    if (image == null ||image == "") {
      image = "noimage.avif";
    } else {
      image = "upload/" + image
    }
    console.log(day, month_str, year, nom, image, event)
    document.getElementById("events-container").innerHTML += `
    <div class="col-md-4 d-flex justify-content-center">

      <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden" style="width: 22rem;">

        <div class="ratio ratio-4x3">
        <img src=${image} class="w-100 h-100 object-fit-cover">
      </div>
         

        <div class="card-body d-flex flex-column p-4">
          <h5 class="card-title fw-semibold">${nom}</h5>

          <p class="card-text text-muted small">
            ${description}
          </p>

          <ul class="list-unstyled mb-3 small text-secondary">
            <li>📅 ${day} ${month_str} ${year}</li>
            <li>📍 Parc des bois</li>
          </ul>

          <a href="#" class="btn btn-primary w-100 mt-auto rounded-3">
            En savoir plus
          </a>
        </div>
      </div>
    </div>
    `
  })
}

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
    const token = localStorage.getItem("token");
    onlineUser(token)
    evenements_populaire();
}

init()
</script>
<script src="tuto.js"></script>
</body>
</html>