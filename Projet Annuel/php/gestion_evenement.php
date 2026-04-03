<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Gestion des evenements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class="container-fluid mt-4">
    <h1 class="mb-4" data-i18n>Gestion des evenements</h1>
    <?php
    if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_GET['message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['state']);
    }?>
    <div class="mb-4">
        <a href='creer_evenement.php' class='btn btn-primary'><i class="bi bi-plus-circle"></i> <span data-i18n>Créer un nouvel evenement</span></a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0" data-i18n>Rechercher un evenement</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search_event(event)" class="row g-3">
                <div class="col-md-8">
                    <input id="event_name" placeholder="Nom de l'evenement..." type="text" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100" data-i18n>Rechercher</button>
                </div>
            </form>
        </div>
    </div>
    <div id="resultat"></div>

    <h2 class="mt-5 mb-3" data-i18n>Liste des evenements</h2>
    <div id="evenements"></div>
</div>
<?php include 'includes/footer.php'?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
    function renderImageHtml(image, altText) {
        const file = String(image ?? "").trim();
        if (!file) return "<em>Pas d'image</em>";
        const src = file.startsWith("http://") || file.startsWith("https://") || file.startsWith("/")
            ? file
            : `upload/${encodeURIComponent(file)}`;
        return `<img src="${src}" alt="${String(altText)}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">`;
    }

    async function supprimer_evenement(id, nom){
        const confirmation = confirm("Êtes-vous sûr de vouloir supprimer l'evenement " + nom + " ?");
        if (!confirmation){
            return;
        } else {
            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + "/supprimer_evenement/" + id, {
                method: "DELETE",
            });
            if (!response.ok){
                const text = await response.text();
                alert(text)
                window.location.href = "erreur.php?code=" + response.status
                return;
            }
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = window.location.pathname + "?message=Evenement " + nom + " supprimé avec succes" ;
            }
    }

    async function search_event(event) {
        event.preventDefault();
        const name = document.getElementById("event_name").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_evenement_nom/" + name, {
            method: "GET",
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();

        if(data.id == 0) {
            document.getElementById("resultat").innerHTML = "<div class='error'>Aucun evenement trouvé</div>";
        }else {
            const imageHtml = renderImageHtml(data.image, "Image de l'evenement");
            document.getElementById("resultat").innerHTML = 
            "<div class='success'>" +
            "<label><strong>ID :</strong> " + String(data.id) + "</label><br>" +
            "<label><strong>Nom :</strong> " + String(data.nom) + "</label><br>" +
            "<label><strong>Date :</strong> " + String(data.date) + "</label><br>" +
            "<label><strong>Description :</strong> " + String(data.description) + "</label><br>" +
            "<label><strong>Tarif :</strong> " + String(data.tarif) + "</label><br>" +
            "<label><strong>Image :</strong> " + imageHtml + "</label><br>" +
            "<a href='modifier_evenement.php?id=" + data.id + "' data-i18n>Modifier l'événement</a> | " +
            "<a href='#' onclick='supprimer_evenement(" + data.id + ", \"" + data.nom + "\"); return false;' data-i18n>Supprimer</a>" +
            "</div>";
        }
    }

async function listEvenements(token) {
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + "/list_evenements", {
        method: "GET",
        headers: {"Token": token}
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
    }
    const evenement_list = await response.json();
    const evenement  = document.getElementById("evenements")

    if (evenement_list.message){
        evenement.innerHTML = "<p>" + evenement_list.message + "</p>"
    } else {
        let html = "<table><tr><th data-i18n>Image</th><th data-i18n>Nom de l'événement</th><th data-i18n>Description</th><th data-i18n>Date de l'événement</th><th data-i18n>Actions</th></tr>";
        evenement_list.evenement.forEach(evenement => {
            const actions = "<a href='modifier_evenement.php?id=" + evenement.id + "' data-i18n>Modifier</a> | " +
                "<a href='#' onclick=\"supprimer_evenement(" + evenement.id + ", '" + evenement.nom.replaceAll("'", "\\'") + "'); return false;\" data-i18n>Supprimer</a>";
            const imageHtml = renderImageHtml(evenement.image, `Image de ${evenement.nom}`);
            const desc = (evenement.description || '').length > 100 ? String(evenement.description).slice(0, 100) + "..." : String(evenement.description);
            html += "<tr><td>" + imageHtml + "</td><td>" + String(evenement.nom) + "</td><td>" + desc + "</td><td>" + String(evenement.date) + "</td><td>" + actions + "</td></tr>";
        });
        html += "</table>";
        evenement.innerHTML = html;
    }
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        listEvenements(token);
    }

window.addEventListener('pageshow', function(event) {
if (event.persisted) {
    window.location.reload();
}
});

init()
</script>

</body>
</html>

