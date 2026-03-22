<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        a { color: #4CAF50; text-decoration: none; margin: 0 5px; }
        a:hover { text-decoration: underline; }
        .search-section { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
        .search-section input { padding: 8px; margin-right: 10px; width: 300px; }
        .search-section button { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .search-section button:hover { background-color: #45a049; }
        #resultat { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<?php include 'includes/header.php'?>

<h1>Gestion des utilisateurs</h1>
<?php
if (isset($_SESSION['state']) && isset($_GET['message'])) {
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}?>
<div class="search-section">
    <h4>Entrer un email pour avoir toutes les informations !</h4>
    <form onsubmit="search_user(event)">
        <input id="user_email" placeholder="Email utilisateur..." type="text">
        <button type="submit">Rechercher</button>
    </form>
</div>
<div id="resultat"></div>

<h2> Liste des utilisateurs </h2>
<div id = "users"></div>

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

    async function supprimer_user(id, email){
        const confirmation = confirm("Êtes-vous sûr de vouloir supprimer l'utilisateur " + email + " ?");
        if (!confirmation){
            return;
        } else {
            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + "/supprimer_user/" + id, {
                method: "DELETE",
            });
            if (!response.ok){
                const text = await response.text();
                alert(text)
                window.location.href = "erreur.php?code=" + response.status
                return;
            }
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = window.location.pathname + "?message=Utilisateur " + email + " supprimé avec succes" ;
            }
    }

    async function bannir_user(id, email){
        const confirmation = confirm("Êtes-vous sûr de vouloir bannir l'utilisateur " + email + " ?");
        if (!confirmation){
            return;
        } else {
            const base = (window.API_BASE || 'http://localhost:9000');
            //const response = await fetch(base + "/bannir_user/" + id, {
            //    method: "PATCH",
            //});
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = window.location.pathname + "?message=Utilisateur " + email + " banni avec succes" ;
            }
    }

    async function search_user(event) {
        event.preventDefault();
        const email = document.getElementById("user_email").value;

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_user_email/" + email, {
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
            document.getElementById("resultat").innerHTML = "<div class='error'>Aucun utilisateur trouvé</div>";
        }else {
            document.getElementById("resultat").innerHTML = 
            "<div class='success'>" +
            "<label><strong>ID :</strong> " + escapeHtml(data.id) + "</label><br>" +
            "<label><strong>Nom :</strong> " + escapeHtml(data.nom) + "</label><br>" +
            "<label><strong>Prénom :</strong> " + escapeHtml(data.prenom) + "</label><br>" +
            "<label><strong>Âge :</strong> " + escapeHtml(data.age) + "</label><br>" +
            "<label><strong>Email :</strong> " + escapeHtml(data.email) + "</label><br>" +
            "<label><strong>Role :</strong> " + escapeHtml(data.role) + "</label><br>" +
            "<label><strong>Langue :</strong> " + escapeHtml(data.langue) + "</label><br>" +
            "<a href='modifier_user.php?id=" + data.id + "'>Modifier l'utilisateur</a> | " +
            "<a href='#' onclick='supprimer_user(" + data.id + ", \"" + data.email + "\"); return false;'>Supprimer</a> | " +
            "<a href='#' onclick='bannir_user(" + data.id + ", \"" + data.email + "\"); return false;'>Bannir</a>" +
            "</div>";
        }
    }

    async function listUsers(token) {
        const base = (window.API_BASE || 'http://localhost:9000');

        const response = await fetch(base + "/list_users", {
            method: "GET",
            headers: {"Token": token}
        });

        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
        const user_list = await response.json();
        const user = document.getElementById("users")

        if (user_list.message){
            user.innerHTML = "<p>" + user_list.message + "</p>"
        } else {
            let html = "<table><tr><th>Nom</th><th>Prénom</th><th>Email</th><th>Role</th><th>Actions</th></tr>";
            user_list.utilisateur.forEach(usr => {
                const actions = "<a href='modifier_user.php?id=" + usr.id + "'>Modifier</a> | " +
                    "<a href='#' onclick=\"supprimer_user(" + usr.id + ", '" + usr.email.replaceAll("'", "\\'") + "'); return false;\">Supprimer</a> | " +
                    "<a href='#' onclick=\"bannir_user(" + usr.id + ", '" + usr.email.replaceAll("'", "\\'") + "'); return false;\">Bannir</a>";
                html += "<tr><td>" + escapeHtml(usr.nom) + "</td><td>" + escapeHtml(usr.prenom) + "</td><td>" + escapeHtml(usr.email) + "</td><td>" + escapeHtml(usr.role) + "</td><td>" + actions + "</td></tr>";
            });
            html += "</table>";
            user.innerHTML = html;
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        listUsers(token);
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
