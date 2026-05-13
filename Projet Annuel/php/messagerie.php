<?php session_start();
include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title data-i18n>Messagerie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="police.css">
    <style>
      .mb-custom {
        margin-bottom: 2.3rem;
      }
      #affichemessage {
        height: 300px; 
        overflow-y: auto; 
        border: 1px solid #ddd; 
        padding: 10px;
        background-color: #fcfcfc;
      }
      .bulle-moi { background-color: #dcf8c6; color: black; padding: 8px; border-radius: 10px; display: inline-block; max-width: 80%; }
      .bulle-autre { background-color: #ebebeb; color: black; padding: 8px; border-radius: 10px; display: inline-block; max-width: 80%; }
      @media (max-width: 768px) {
        
      #geturl {
        font-size: 1rem !important;
        text-align: center;
      }

      .col-3 .btn {
        font-size: 0.75rem !important;
        padding: 5px 2px !important;
    }
    }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class='container mt-5'>
    <h1 data-i18n class='mb-custom text-center ms-4' style='font-size:50px'>Messagerie</h1>

    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-3 border-end">
                <div class="d-grid gap-2">
                  <?php if (isset($_SESSION['state']) && isset($_GET['message'])) { 
                    echo "<h3 id='geturl' data-i18n class='text-center fs-15'>" . htmlspecialchars($_GET['message']) . "</h3>";
                    unset($_SESSION['state']);
                    } else {
                    echo '<h3 id="geturl" class="mb-3 mt-0" style="font-size: 1.5rem; font-weight: bold;"></h3>';
                    }?>

                    <a onclick="chargementContact(1)" class="btn btn-primary" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample" data-i18n>
                        Envoyer un message
                    </a>
                    <a onclick="chargementContact(2)" class="btn btn-primary" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample" data-i18n>
                        Ajouter un contact
                    </a>
                    <a onclick="chargementContact(3)" class="btn btn-danger" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample" data-i18n>
                        Supprimer un contact
                    </a>
                    <a id="block" onclick="chargementContact(4)" class="btn btn-danger" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample" data-i18n>
                        Bloquer un contact
                    </a>
                    <a id="block" onclick="chargementContact(5)" class="btn btn-danger" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample" data-i18n>
                        Débloquer un contact
                    </a>
                    <a onclick="chargementContact(6)" class="btn btn-warning" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample" data-i18n>
                        Demandes de contact
                    </a>
                </div>
            </div>

            <div class="col-9">
                <div id="affichemessage">
                    <p class="text-center text-muted" data-i18n>Sélectionnez un contact pour discuter</p>
                </div>

                <div class="mt-3">
                    <input id="msgInput" type="text" class="form-control" placeholder="Tapez votre message..." data-i18n/>
                    <button class="btn btn-primary w-100 mt-2" onclick="sendMessage()" data-i18n>Envoyer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel" data-i18n></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-grid gap-2 mx-auto" style="max-width: 300px;">
            <div id="liste_contact">
            </div>
        </div>
    </div>
</div>

<div class="mb-4"></div>

<?php include 'includes/footer.php'; ?>

<script>
  const token = localStorage.getItem('token');
  const base = (window.API_BASE || 'http://localhost:9000');
  let currentContactID = null;
  let val;

  async function sendMessage() {
    const msg = document.getElementById("msgInput").value.trim();

    if (!msg) {
      alert("Le message ne peut pas être vide");
      return;
    }

    if (!currentContactID) {
      alert("Veuillez sélectionner un contact");
      return;
    }

    const response = await fetch(base + "/send-message", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Token": token
      },
      body: JSON.stringify({
        destinataire_id: currentContactID,
        contenu: msg
      })
    });

    if (!response.ok) {
      const text = await response.text();
      alert(text);
      window.location.href = "erreur.php?code=" + response.status;
      return;
    }

    document.getElementById("msgInput").value = "";
    await chargementMessage();
  }

  async function chargementMessage() {
    if (!currentContactID) return;

    const response = await fetch(base + "/load-messages?contact_id=" + currentContactID, {
      method: "GET",
      headers: { "Token": token }
    });

    if (!response.ok) return;

    const data = await response.json();
    let valeur = "";
    data.forEach(message => {
      const dateStr = message.date_envoie;
      const [day, d2] = dateStr.split("T");
      const hour = d2.replace("Z", "").substring(0, 5);
      if (message.auteur == message.user){
        valeur += `
        <div class="mb-2 text-end" style="color:blue">
          <div class="bulle-moi"><strong>Vous :</strong> ${message.contenu} <br>
          <small class="text-muted" style="font-size:10px">${day} ${hour}</small></div>
        </div>`;
      } else {
        valeur += `
          <div class="mb-2">
          <div class="bulle-autre"><strong>${message.auteur}</strong> ${message.contenu} <br>
          <small class="text-muted" style="font-size:10px">${day} ${hour}</small></div>
          </div>`;
      }
      
      
    });
    document.getElementById("affichemessage").innerHTML = valeur || "<p class='text-center'>Aucun message</p>";

    document.getElementById("affichemessage").scrollTop = document.getElementById("affichemessage").scrollHeight;
  }

  async function chargementContact(value) {
    let response;
    
    if (value == 2){
      response = await fetch(base + "/load-contacts-send", {
      method: "GET",
      headers: { "Token": token }
    });
    } else if (value == 5){
      response = await fetch(base + "/load-contacts-block", {
      method: "GET",
      headers: { "Token": token }
    });
    } else if (value == 6){
      response = await fetch(base + "/load-contacts-get", {
      method: "GET",
      headers: { "Token": token }
    });
    } else {
      response = await fetch(base + "/load-contacts", {
      method: "GET",
      headers: { "Token": token }
    });
    }

    const xx = document.getElementById("offcanvasExampleLabel")
    let butt
    let butt2
    let rien
    switch (value){
      case 1:
        xx.innerHTML="Mes contacts"
        butt = 'btn btn-primary w-100 mb-1'
        val = true
        rien = "Aucun Contact"
        break
      case 2:
        xx.innerHTML="Ajouter un contact"
        butt = 'btn btn-danger w-100 mb-1'
        val = false
        rien = "Aucune Demande Envoyée"
        break
      case 3:
        xx.innerHTML="Supprimer un contact"
        butt = 'btn btn-danger w-100 mb-1'
        val = false
        rien = "Aucun Contact"
        break
      case 4:
        xx.innerHTML="Bloquer un contact"
        butt = 'btn btn-danger w-100 mb-1'
        val = false
        rien = "Aucun Contact"
        break
      case 5:
        xx.innerHTML="Mes bloqués"
        butt = 'btn btn-primary w-100 mb-1'
        val = false
        rien = "Aucun Contact Bloqué"
        break
      case 6:
        xx.innerHTML="Demandes de contact reçues"
        butt = 'btn btn-danger w-100 mb-1'
        butt2 = 'btn btn-primary w-100 mb-1'
        val = false
        rien = "Aucune Demande Reçue"
        break
    } 
      
    if (!response.ok) return;
    const data = await response.json();

    let func;
    func = `onclick="selectContact(this.dataset.id, this.dataset.name, ${value})"`;

    let valeur = ""
    let valeur2 = ""

    data.forEach(contact => {
        if (value == 6){
          valeur += `
              <div class="d-flex w-100">
              <button class="${butt2} btn-md"
              type="button"
              style="flex: 1; margin-right: 2px;" 
              data-bs-dismiss="offcanvas"
              data-id="${contact.id}" 
              data-name='${contact.prenom} ${contact.nom}'
              onclick="selectContact(this.dataset.id, this.dataset.name, 60)">
              ${contact.prenom} ${contact.nom}
              </button>

              <button class="${butt} btn-md"
              type="button"
              style="flex: 0.5;"
              data-bs-dismiss="offcanvas"
              data-id="${contact.id}" 
              data-name='${contact.prenom} ${contact.nom}'
              ${func}>
              Refuser
              </button>
              </div>
`;
        } else {
          valeur += `
                <button class="${butt}"
                type="button"
                data-bs-dismiss="offcanvas" 
                data-id="${contact.id}" 
                data-name="${contact.prenom} ${contact.nom}"
                ${func}>
                ${contact.prenom} ${contact.nom}
                </button>`;
      }
    });
    document.getElementById('liste_contact').innerHTML = valeur || "<p>" + rien + "</p>";

    if (value == 2){
        valeur2 += `
        <div class="input-group mb-3 w-100">
            <input type="text" 
                  id="searchContact" 
                  class="form-control form-control-sm" 
                  placeholder="Rechercher un contact..." 
                  aria-label="Recherche">
            
            <button class="btn btn-primary" 
                    type="button" 
                    onclick="Demander(document.getElementById('searchContact').value)">
                <i class="bi bi-search"></i> Rechercher
            </button>
        </div>
        `;
        document.getElementById('liste_contact').innerHTML = valeur2 + (valeur || "<p>" + rien + "</p>")
    }
  }

  function selectContact(id, name, numero) {
    currentContactID = parseInt(id);
    if(numero == 1){
      document.getElementById("geturl").textContent = "Conversation avec " + name;
      chargementMessage();
    }else if(numero == 2){
      annulerDemande(name)
    }else if(numero == 3){
      deleteContact(name, 1);
    }else if(numero == 4){
      bloquerContact(name);
    }else if(numero == 5){
      debloquerContact(name);
    }else if (numero == 6){
      refuserDemande(name);
    } else if (numero == 60){
      accepterDemande(name);
    }
  }

  async function Demander(name){
    if (name == "" || !name){
      alert("Veuillez rechercher un utilisateur sous la forme 'Prenom Nom'")
      return
    }
    const response = await fetch(base + "/add-contact", {
        method: "POST",
        headers: { "Token": token, "Content-Type": "application/json"},
        body: JSON.stringify({
          user: name
        })
    })
    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
     }
    const data = await response.json();
    await fetch("ajouter_session_state.php", {method: "POST"});
    window.location.href = `messagerie.php?message=${data.message}`
  }

  async function annulerDemande(name) {
    if(confirm("Annuler votre demande envoyée à " + name + " ?")) {
       const response = await fetch(base + "/remove-demand/" + currentContactID, {
        method: "DELETE",
        headers: { "Token": token }
      });
      if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
      }
      await fetch("ajouter_session_state.php", {method: "POST"});
      window.location.href = `messagerie.php?message=Demande de contact annulée`
    }
  }

  async function refuserDemande(name) {
    if(confirm("Refuser la demande de contact de " + name + " ?")) {
       const response = await fetch(base + "/deny-demand/" + currentContactID, {
        method: "DELETE",
        headers: { "Token": token }
      });
      if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
      await fetch("ajouter_session_state.php", {method: "POST"});
      window.location.href = `messagerie.php?message=Demande de contact refusée`
    }
  }

  async function accepterDemande(name) {
    if(confirm("Accepter la demande de contact de " + name + " ?")) {
       const response = await fetch(base + "/accept-demand/" + currentContactID, {
        method: "DELETE",
        headers: { "Token": token }
      });
      if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
      await fetch("ajouter_session_state.php", {method: "POST"});
      window.location.href = `messagerie.php?message=Demande de contact acceptée`
    }
  }

  async function deleteContact(name, numero) {
    const confirmed = (numero === 1) ? confirm("Supprimer " + name + " ?") : true;
    if(confirmed) {
       const response = await fetch(base + "/delete-contact/" + currentContactID, {
        method: "DELETE",
        headers: { "Token": token }
      });
      if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
      if (numero == 1){
        await fetch("ajouter_session_state.php", {method: "POST"});
        window.location.href = `messagerie.php?message=Utilisateur ${name} supprimé`
      }
    }
  }

  async function bloquerContact(name) {
    if(confirm("Bloquer " + name + " ?")) {
       const response = await fetch(base + "/block-contact/" + currentContactID, {
        method: "POST",
        headers: { "Token": token }
      });
      if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
      await deleteContact(name, 0);
      await fetch("ajouter_session_state.php", {method: "POST"});
      window.location.href = `messagerie.php?message=Utilisateur ${name} bloqué`
    }
  }

  async function debloquerContact(name) {
    if(confirm("Débloquer " + name + " ?")) {
       const response = await fetch(base + "/deblock-contact/" + currentContactID, {
        method: "POST",
        headers: { "Token": token }
      });
      if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
      await fetch("ajouter_session_state.php", {method: "POST"});
      window.location.href = `messagerie.php?message=Utilisateur ${name} débloqué`
    }
  }

  setInterval(() => {
    if (currentContactID && val) chargementMessage();
  }, 3000);
</script>
</body>
</html>