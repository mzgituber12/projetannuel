<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Gestion des services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class="container-fluid mt-4">
    <h1 class="mb-4" data-i18n>Gestion des services</h1>
    <?php
    if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_GET['message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['state']);
    }?>
    <div class="mb-4">
        <a href='creer_service.php' class='btn btn-primary'><i class="bi bi-plus-circle"></i> <span data-i18n>Créer un nouveau service</span></a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0" data-i18n>Rechercher un service</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search_service(event); return false;" class="row g-3">
                <div class="col-md-8">
                    <input id="serv_name" placeholder="Nom du service..." type="text" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100" data-i18n>Rechercher</button>
                </div>
            </form>
        </div>
    </div>
    <div id="resultat"></div>

    <h2 class="mt-5 mb-3" data-i18n>Liste des services</h2>
    <div id="services"></div>
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

    async function supprimer_service(id, nom){
        const confirmation = confirm("Êtes-vous sûr de vouloir supprimer le service " + nom + " ?");
        if (!confirmation){
            return;
        } else {
            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + "/supprimer_service/" + id, {
                method: "DELETE",
            });
            if (!response.ok){
                const text = await response.text();
                alert(text)
                window.location.href = "erreur.php?code=" + response.status
                return;
            }
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = window.location.pathname + "?message=Service " + nom + " supprimé avec succes" ;
            }
    }

    async function search_service(event) {
        event.preventDefault();
        const name = document.getElementById("serv_name").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_service/" + name, {
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
            document.getElementById("resultat").innerHTML = "<div class='error'>Aucun service trouvé</div>";
        }else {
            const imageHtml = renderImageHtml(data.image, "Image du service");
            document.getElementById("resultat").innerHTML = 
            "<div class='success'>" +
            "<label><strong>ID :</strong> " + String(data.id) + "</label><br>" +
            "<label><strong>Nom :</strong> " + String(data.nom) + "</label><br>" +
            "<label><strong>Description :</strong> " + String(data.description) + "</label><br>" +
            "<label><strong>Tarif :</strong> " + String(data.tarif) + "</label><br>" +
            "<label><strong>Image :</strong> " + imageHtml + "</label><br>" +
            "<a href='modifier_service.php?id=" + data.id + "'>Modifier service</a> | " +
            "<a href='#' onclick='supprimer_service(" + data.id + ", \"" + data.nom + "\"); return false;'>Supprimer</a>" +
            "</div>";
        }
    }

    async function listService(token) {
        const base = (window.API_BASE || 'http://localhost:9000');

        const response = await fetch(base + "/list_services", {
            method: "GET",
            headers: {"Token": token}
        });

        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
        const service_list = await response.json();
        const service = document.getElementById("services")

        if (service_list.message){
            service.innerHTML = "<p>" + service_list.message + "</p>"
        } else {
            let html = "<table><tr><th data-i18n>Image</th><th data-i18n>Nom du service</th><th data-i18n>Description</th><th data-i18n>Tarif</th><th data-i18n>Actions</th></tr>";
            service_list.service.forEach(serv => {
                const actions = "<a href='modifier_service.php?id=" + serv.id + "' data-i18n>Modifier</a> | " +
                    "<a href='#' onclick=\"supprimer_service(" + serv.id + ", '" + serv.nom.replaceAll("'", "\\'") + "'); return false;\" data-i18n>Supprimer</a>";
                const imageHtml = renderImageHtml(serv.image, `Image de ${serv.nom}`);
                const desc = (serv.description || '').length > 100 ? String(serv.description).slice(0, 100) + "..." : String(serv.description);
                html += "<tr><td>" + imageHtml + "</td><td>" + String(serv.nom) + "</td><td>" + desc + "</td><td>" + String(serv.tarif) + "</td><td>" + actions + "</td></tr>";
            });
            html += "</table>";
            service.innerHTML = html;
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        listService(token);
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

