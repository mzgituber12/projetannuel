<style>
  body {
          min-height: 100vh; 
          display: flex;
          flex-direction: column;
        
      }
      footer { 
  margin-top: auto; 
}
</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script>
async function footerUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Content-Type": "application/json", "Token": token},
    });
    const data = await response.json();

    document.getElementById("footer").innerHTML += "<a href='politique_confidentialite.php' class='me-3 text-white text-decoration-none'>Politique et confidentialité</a>";
    document.getElementById("footer").innerHTML += "<a href='qui_sommes_nous.php' class='me-3 text-white text-decoration-none'>Nous découvrir</a>";

    if (data.message == "Identifié"){
        document.getElementById("footer").innerHTML += "<a href='contactez_nous.php' class='me-3 text-white text-decoration-none'>Contactez-nous</a>";
    }
}
footerUser(localStorage.getItem('token'));
</script>


<footer class=" text-center text-lg-start mt-auto" style="background-color:black;">
  <div class="container p-4">
    <div class="row align-items-center">

      <div class="col-lg-5 mb-md-0 text-center text-lg-start" id="footer">
      </div>

      <div class="col-lg-5 col-md-12 text-center text-lg-end">
        <a href="https://instagram.com" class="me-3 fs-4 text-white">
          <i class="bi bi-instagram"></i>
        </a>
        <a href="https://twitter.com" class="me-3 fs-4 text-white">
          <i class="bi bi-twitter"></i>
        </a>
        <a href="https://facebook.com" class="fs-4 text-white">
          <i class="bi bi-facebook"></i>
        </a>
      </div>
      
    </div>
  </div>
</footer>