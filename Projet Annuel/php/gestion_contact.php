<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des contacts</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .search-section { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
    </style>
</head>
<body>

<?php include 'includes/header.php'?>

<h1>Gestion des contacts</h1>

<div class="search-section">
    <h4>Liste complète des messages de contact</h4>
</div>

<div id="resultat"></div>

<?php include 'includes/footer.php'?>

<script>
    function escapeHtml(value) {
        return String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#39;");
    }

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
            liste.innerHTML = data.message
        } else {
            let html = "<table><tr><th>Email de l'utilisateur</th><th>Contenu du message</th></tr>";
            data.contact.forEach(contacts => {
            html += "<tr><td>" + escapeHtml(contacts.email) + "</td><td>" + escapeHtml(contacts.contenu) + "</tr>"
            });
            html += "</table>";
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
