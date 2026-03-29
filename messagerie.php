<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

<?php include 'includes/header.php'; ?>

<h1> Contactez Nous </h1>
<h2 id="geturl"></h2>
<p>Formuler votre demande juste en dessous, nous vous répondrons par mail le plus vite possible !</p>

<a class="btn btn-primary" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">
  Mes Contact
</a>

  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasExampleLabel">Mes contacts</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div class="d-grid gap-2 col-6 mx-auto">
        <div id ="liste_contact"></div>
      </div>
    </div>
  </div>

<div id = "affichemessage"></div>


  <input id="msgInput" type="text" placeholder="Type a message..." />
  <button onclick="sendMessage()">Send</button>
  <ul id="messages"></ul>
 <script>

  async function sendMessage(params) {
    const msg = docuement.getElementById("msgInput")

    const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/messagerie", {
        method: "POST",
        body : JSON.stringify({
          msg: msg}
        )
        });
        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }

    
  }







async function chargementContact(){
     const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/chargement_contact", {
        method: "GET",
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();
        let valeur = "";
        data.forEach(contact => {
          valeur += `<button class="btn btn-primary" type="button">Jean</button><p>${prenom} ${nom}</p>`;

        })
        document.getElementById("liste_contact").innerHTML = valeur


  }
    
  window.onload = chargementContact;









  async function chargementMessage(){
     const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/messagerie", {
        method: "GET",
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();
        let valeur = "";
        data.forEach(message => {
          valeur += `<p>${message}</p>`;

        })
        document.getElementById("affichemessage").innerHTML = valeur


  }
    
  window.onload = chargementMessage;


  </script>

</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>