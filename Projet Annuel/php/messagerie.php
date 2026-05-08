<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Messagerie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<h1 data-i18n>Messagerie</h1>
<h2 id="geturl"></h2>

<a class="btn btn-primary" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample" data-i18n>
  Mes Contact
</a>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasExampleLabel" data-i18n>Mes contacts</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="d-grid gap-2 col-6 mx-auto">
      <div id="liste_contact"></div>
    </div>
  </div>
</div>

<div id="affichemessage" style="height:300px; overflow-y:auto; border:1px solid #ddd; padding:10px; margin-top:10px;"></div>

<div class="mt-2">
  <input id="msgInput" type="text" class="form-control" placeholder="Tapez votre message..." />
  <button class="btn btn-primary mt-1" onclick="sendMessage()" data-i18n>Envoyer</button>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
  const token = localStorage.getItem('token');
  const base = (window.API_BASE || 'http://localhost:9000');
  let currentContactID = null;

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

    if (!response.ok) {
      const text = await response.text();
      alert(text);
      window.location.href = "erreur.php?code=" + response.status;
      return;
    }

    const data = await response.json();
    let valeur = "";
    data.forEach(message => {
      valeur += `<p><strong>${message.auteur}:</strong> ${message.contenu} <small class="text-muted">${message.date_envoie}</small></p>`;
    });
    document.getElementById("affichemessage").innerHTML = valeur || "<p data-i18n>Aucun message</p>";
  }

  async function chargementContact() {
    const response = await fetch(base + "/load-contacts", {
      method: "GET",
      headers: { "Token": token }
    });

    if (!response.ok) {
      const text = await response.text();
      alert(text);
      window.location.href = "erreur.php?code=" + response.status;
      return;
    }

    const data = await response.json();
    let valeur = "";
    data.forEach(contact => {
      valeur += `<button class="btn btn-primary w-100 mb-1" type="button" data-id="${contact.id}" data-name="${contact.prenom} ${contact.nom}" onclick="selectContact(this.dataset.id, this.dataset.name)">${contact.prenom} ${contact.nom}</button>`;
    });
    document.getElementById("liste_contact").innerHTML = valeur || "<p data-i18n>Aucun contact</p>";
  }

  function selectContact(id, name) {
    currentContactID = parseInt(id);
    document.getElementById("geturl").textContent = "Conversation avec " + name;
    chargementMessage();
  }

  setInterval(() => {
    if (currentContactID) chargementMessage();
  }, 3000);

  window.onload = chargementContact;
</script>
</body>
</html>
