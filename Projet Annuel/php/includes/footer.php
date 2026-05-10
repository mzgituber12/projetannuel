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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<footer class="text-center text-lg-start" style="background-color:black;">

  <div class="container p-4">
    <div class="row align-items-center">

      <div class="col-lg-7 mb-3 mb-lg-0">
        <div id="footer-links"
             class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
        </div>
      </div>

      <div class="col-lg-5 text-center text-lg-end">
        <a href="https://instagram.com" class="me-3 fs-2 text-white text-decoration-none">
          <i class="bi bi-instagram"></i>
        </a>
        <a href="https://twitter.com" class="me-3 fs-2 text-white text-decoration-none">
          <i class="bi bi-twitter"></i>
        </a>
        <a href="https://facebook.com" class="fs-2 text-white text-decoration-none">
          <i class="bi bi-facebook"></i>
        </a>
      </div>

    </div>
  </div>

</footer>

<script>
async function footerUser(token) {

  const footer = document.getElementById("footer-links");

  const addLink = (text, href) => {
    const a = document.createElement("a");
    a.href = href;
    a.className = "text-white text-decoration-none";
    a.textContent = text;
    footer.appendChild(a);
  };

  // liens de base
  addLink("Politique et confidentialité", "politique_confidentialite.php");
  addLink("Nous découvrir", "qui_sommes_nous.php");

  const base = (window.API_BASE || 'http://localhost:9000');

  try {
    const response = await fetch(base + "/enligne", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Token": token
      },
    });

    if (!response.ok) return;

    const data = await response.json();

    if (data.message == "Identifié") {
      addLink("Contactez-nous", "contactez_nous.php");
    }

  } catch (e) {
    console.log("Erreur footer:", e);
  }
}

footerUser(localStorage.getItem('token'));
</script>