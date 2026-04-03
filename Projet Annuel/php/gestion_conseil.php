<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("online", localStorage.getItem('token')); 
</script>
<script src="admin.js"></script>
<script>
adminUser(localStorage.getItem('token')); 
</script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Gestion des conseils</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class="container-fluid mt-4">
    <h1 class="mb-4" data-i18n>Gestion des conseils</h1>

    <?php
    if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_GET['message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['state']);
    }?>

    <div class="mb-4">
        <a href='modifier_conseil.php?id=new' class='btn btn-primary'><i class="bi bi-plus-circle"></i> <span data-i18n>Créer un nouveau conseil</span></a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0" data-i18n>Rechercher un conseil</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search_conseil(event); return false;" class="row g-3">
                <div class="col-md-8">
                    <input id="conseil_titre" placeholder="Titre du conseil..." type="text" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100" data-i18n>Rechercher</button>
                </div>
            </form>
        </div>
    </div>

    <div id="resultat"></div>

    <h2 class="mt-5 mb-3" data-i18n>Liste des conseils</h2>
    <div id="conseils"></div>
</div>

<script>
    async function search_conseil(event) {
        event.preventDefault();
        const titre = document.getElementById("conseil_titre").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_conseil/" + titre, {
            method: "GET",
        });
        const data = await response.json();

        if(data.id == 0 || !data.titre) {
            document.getElementById("resultat").innerHTML = "<div class='alert alert-warning'>Aucun conseil trouvé</div>";
        } else {
            let imageHTML = data.image ? `<img src="../upload/${data.image}" class='img-thumbnail' style="max-width: 100px; max-height: 100px;">` : '<em>Pas d\'image</em>';
            document.getElementById("resultat").innerHTML = 
            "<div class='alert alert-success'><div class='row'><div class='col-md-8'>" +
            "<label><strong>ID :</strong> " + data.id + "</label><br>" +
            "<label><strong>Titre :</strong> " + data.titre + "</label><br>" +
            "<label><strong>Contenu :</strong> " + data.contenu + "</label><br>" +
            "<label><strong>Date :</strong> " + data.date + "</label><br>" +
            "</div><div class='col-md-4'><label><strong>Image :</strong></label><br>" + imageHTML + 
            "</div></div><div class='mt-3'>" +
            "<a href='modifier_conseil.php?id=" + data.id + "' class='btn btn-sm btn-warning'>Modifier</a> " +
            "<a href='#' onclick='deleteConseils(" + data.id + "); return false;' class='btn btn-sm btn-danger'>Supprimer</a>" +
            "</div></div>";
        }
    }

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    async function listConseils(token) {
        const base = (window.API_BASE || 'http://localhost:9000');

        const response = await fetch(base + "/gestion_conseils", {
            method: "GET",
            headers: {"Token": token}
        });

        if (!response.ok) {
            const text = await response.text();
            if (response.status !== 404) {
                alert(text)
                window.location.href = "erreur.php?code=" + response.status
            }
            return
        }
        const conseil_list = await response.json();
        const conseil = document.getElementById("conseils")

        if (conseil_list.message){
            conseil.innerHTML = "<div class='alert alert-info'>" + conseil_list.message + "</div>"
        } else {
            let html = "<div class='table-responsive'><table class='table table-hover'><thead class='table-success'><tr><th data-i18n>Image</th><th data-i18n>Titre du conseil</th><th data-i18n>Contenu</th><th data-i18n>Date</th><th data-i18n>Actions</th></tr></thead><tbody>";
            conseil_list.conseil.forEach(cons => {
                const actions = "<a href='modifier_conseil.php?id=" + cons.id + "' class='btn btn-sm btn-warning' data-i18n>Modifier</a> " +
                               "<a href='#' onclick='deleteConseils(" + cons.id + "); return false;' class='btn btn-sm btn-danger' data-i18n>Supprimer</a>";
                const imageHTML = cons.image ? `<img src="../upload/${cons.image}" class='img-thumbnail' style="max-width: 80px; max-height: 80px;">` : '<em>-</em>';
                html += "<tr><td>" + imageHTML + "</td><td>" + cons.titre + "</td><td>" + cons.contenu.substring(0, 100) + "...</td><td>" + cons.date + "</td><td>" + actions + "</td></tr>";
            });
            html += "</tbody></table></div>";
            conseil.innerHTML = html;
        }
    }

    async function deleteConseils(id) {
        if (!confirm("Êtes-vous sûr de vouloir supprimer ce conseil ?")) {
            return;
        }

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/supprimer_conseil/" + id, {
            method: "DELETE",
            headers: {"Token": localStorage.getItem('token')}
        });

        if (!response.ok) {
            const text = await response.text();
            alert("Erreur : " + text);
            return;
        }

        const data = await response.json();
        if (data.value == 1) {
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = "gestion_conseil.php?message=" + data.message;
        } else {
            alert("Erreur : " + data.message);
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        adminUser(token)
        listConseils(token);
    }
    init()
</script>
<?php include 'includes/footer.php'?>
</body>
</html>
