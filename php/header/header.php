<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script>
async function headerUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Content-Type": "application/json", "Token": token},
    });
    const data = await response.json();

    if (data.message == "Identifié"){
        document.getElementById("header").innerHTML = "<a href='index.php'>Accueil</a> | <a href='deconnexion.php'>Déconnexion</a>";
        if (data.role == "adherant"){
            document.getElementById("header").innerHTML += " | <a href='contrats.php'>Contrats</a> | <a href='conseils.php'>Conseils</a>  | <a href='catalogue.php'>Catalogue</a> | <a href='devis.php'>Devis</a>  | <a href='planning.php'>Planning</a>  | <a href='rendez_vous.php'>Rendez Vous</a>  | <a href='messagerie.php'>Messagerie</a>";
        } else if (data.role == "prestataire"){
            document.getElementById("header").innerHTML += " | <a href='suivi.php'>Suivi des prestations</a> | <a href='validations.php'>Validations</a>  | <a href='calendrier.php'>Calendrier</a> | <a href='interventions.php'>Interventions</a>  | <a href='factures.php'>Factures</a>  | <a href='rendez_vous.php'>Rendez Vous</a>  | <a href='messagerie.php'>Messagerie</a>";
        } else if (data.role == "admin"){
            document.getElementById("header").innerHTML += " | <a href='gestion_user.php'>Gestion des Utilisateur</a> | <a href='gestion_event.php'>Gestion des Evenements</a>  | <a href='gestion_shop.php'>Gestion du Catalogue</a> | <a href='gestion_conseil.php'>Gestion des Conseils</a>  | <a href='gestion_notifs.php'>Gestion des Notifications</a>  | <a href='gestion_finance.php'>Gestion Financiere</a>";
        }
    } else if (data.message == "Pas identifié"){
        document.getElementById("header").innerHTML = "<a href='index.php'>Accueil</a> | <a href='inscription.php'>Inscription</a> | <a href='connexion.php'>Connexion</a>";
    }
}
headerUser(localStorage.getItem('token'));
</script>


<header>
<a class="navbar-brand" href="index.php">
            <i class="bi bi-house fs-2"></i>
        </a>
<nav><div id = "header">
</div></nav>
 </div>
</header>