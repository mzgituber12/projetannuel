<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Gestion des contacts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class="container mt-5">
    <h1 class="mb-4" data-i18n>Gestion des contacts</h1>

    <div class="card bg-light mb-4">
        <div class="card-body">
            <h5 class="card-title" data-i18n>Liste complète des messages de contact</h5>
        </div>
    </div>

    <div id="resultat"></div>
</div>

<?php include 'includes/footer.php'?>

<script>
    async function list_msgcontact() {

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_contact",{
            method: "GET",
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();
        const liste = document.getElementById("resultat")

        if (data.message == "Personne n'a contacté les administrateurs pour le moment") {
            liste.innerHTML = '<div class="alert alert-info">' + String(data.message) + '</div>'
        } else {
            let html = '<div class="table-responsive"><table class="table table-hover"><thead class="table-success"><tr><th data-i18n>Email de l\'utilisateur</th><th data-i18n>Contenu du message</th></tr></thead><tbody>';
            data.contact.forEach(contacts => {
                html += "<tr><td>" + String(contacts.email) + "</td><td>" + String(contacts.contenu) + "</td></tr>"
            });
            html += "</tbody></table></div>";
            liste.innerHTML = html;
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        list_msgcontact()
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

