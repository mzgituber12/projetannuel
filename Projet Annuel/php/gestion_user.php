<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Gestion des utilisateurs</title>
    <style>
        .mb-custom{
            margin-bottom: 2rem
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class='container mt-5'>
    <h1 data-i18n class='mb-custom text-center ms-4' style='font-size:50px'>Gestion des utilisateurs</h1>
    <?php
    if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_GET['message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['state']);
    }?>

<div class="container-fluid mt-4">
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0" data-i18n>Rechercher un utilisateur</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search_user(event)" class="row g-3">
                <div class="col-md-8">
                    <input id="user_email" placeholder="Email utilisateur..." type="text" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100" data-i18n>Rechercher</button>
                </div>
            </form>
        </div>
    </div>
    <div id="resultat"></div>

    <h2 class="mt-5 mb-3" data-i18n>Liste des utilisateurs</h2>
    <div id="users"></div>
</div>

<div class="mb-4"></div>

</div>

<?php include 'includes/footer.php'?>

<script>
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

    function libelleStatut(statut) {
        if (statut === "banni") return "Banni";
        if (statut === "suspendu") return "Suspendu";
        return "Actif";
    }

    async function bannir_user(id, email){
        const confirmation = confirm("Êtes-vous sûr de vouloir sanctionner l'utilisateur " + email + " ?");
        if (!confirmation){
            return;
        } else {
            const motif = prompt("Motif du bannissement/suspension :", "Non respect des règles");
            if (motif === null) return;
            const type = prompt("Type de sanction (temp ou perm) :", "temp");
            const typeSanction = (type || "temp").toLowerCase() === "perm" ? "perm" : "temp";
            const token = localStorage.getItem('token');
            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + "/bannir_user/" + id, {
                method: "PATCH",
                headers: {"Content-Type": "application/json", "Token": token},
                body: JSON.stringify({type: typeSanction, motif: motif || "Non précisé"}),
            });
            if (!response.ok){
                const text = await response.text();
                alert(text);
                window.location.href = "erreur.php?code=" + response.status;
                return;
            }
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = window.location.pathname + "?message=Utilisateur " + email + " sanctionné avec succès" ;
            }
    }

    async function debannir_user(id, email){
        const confirmation = confirm("Êtes-vous sûr de vouloir débannir l'utilisateur " + email + " ?");
        if (!confirmation){
            return;
        }
        const token = localStorage.getItem('token');
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/debannir_user/" + id, {
            method: "PATCH",
            headers: {"Content-Type": "application/json", "Token": token},
        });
        if (!response.ok){
            const text = await response.text();
            alert(text);
            window.location.href = "erreur.php?code=" + response.status;
            return;
        }
        await fetch("ajouter_session_state.php", {method: "POST"});
        window.location.href = window.location.pathname + "?message=Utilisateur " + email + " débanni avec succès";
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
            "<label><strong>ID :</strong> " + String(data.id) + "</label><br>" +
            "<label><strong>Nom :</strong> " + String(data.nom) + "</label><br>" +
            "<label><strong>Prénom :</strong> " + String(data.prenom) + "</label><br>" +
            "<label><strong>Âge :</strong> " + String(data.age) + "</label><br>" +
            "<label><strong>Email :</strong> " + String(data.email) + "</label><br>" +
            "<label><strong>Role :</strong> " + String(data.role) + "</label><br>" +
            "<label><strong>Langue :</strong> " + String(data.langue) + "</label><br>" +
            "<label><strong>Statut :</strong> " + libelleStatut(String(data.statut_user || "actif")) + "</label><br>" +
            "<a href='modifier_user.php?id=" + data.id + "'>Modifier l'utilisateur</a> | " +
            "<a href='#' onclick='supprimer_user(" + data.id + ", \"" + data.email + "\"); return false;'>Supprimer</a> | " +
            (String(data.statut_user || "actif") === "banni" || String(data.statut_user || "actif") === "suspendu"
                ? "<a href='#' onclick='debannir_user(" + data.id + ", \"" + data.email + "\"); return false;'>Débannir</a>"
                : "<a href='#' onclick='bannir_user(" + data.id + ", \"" + data.email + "\"); return false;'>Bannir</a>") +
            "</div>";
        }
    }

    async function listUsers(token) {
        const base = (window.API_BASE || 'http://localhost:9000');
        const user = document.getElementById("users");
        try {
            const response = await fetch(base + "/list_users", {
                method: "GET",
                headers: {"Token": token}
            });

            if (!response.ok) {
                const text = await response.text();
                throw new Error("API list_users (" + response.status + ") : " + text);
            }
            const user_list = await response.json();

            if (user_list.message){
                user.innerHTML = "<p>" + user_list.message + "</p>";
            } else {
                let html = "<table class='table table-sm table-bordered'><tr><th>Nom</th><th>Prénom</th><th>Âge</th><th>Email</th><th>Role</th><th>Statut</th><th>Actions</th></tr>";
                (user_list.utilisateur || []).forEach(usr => {
                    const email = String(usr.email || "");
                    const safeEmail = email.replace(/'/g, "\\'");
                    const isBlocked = String(usr.statut_user || "actif") === "banni" || String(usr.statut_user || "actif") === "suspendu";
                    const actions = "<a href='modifier_user.php?id=" + usr.id + "' data-i18n>Modifier</a> | " +
                        "<a href='#' onclick=\"supprimer_user(" + usr.id + ", '" + safeEmail + "'); return false;\" data-i18n>Supprimer</a> | " +
                        (isBlocked
                            ? "<a href='#' onclick=\"debannir_user(" + usr.id + ", '" + safeEmail + "'); return false;\" data-i18n>Débannir</a>"
                            : "<a href='#' onclick=\"bannir_user(" + usr.id + ", '" + safeEmail + "'); return false;\" data-i18n>Bannir</a>");
                    html += "<tr><td>" + String(usr.nom || "") + "</td><td>" + String(usr.prenom || "") + "</td><td>" + String(usr.age || 0) + "</td><td>" + email + "</td><td>" + String(usr.role || "") + "</td><td>" + libelleStatut(String(usr.statut_user || "actif")) + "</td><td>" + actions + "</td></tr>";
                });
                html += "</table>";
                user.innerHTML = html;
            }
        } catch (e) {
            user.innerHTML = "<div class='alert alert-danger'>Erreur chargement utilisateurs : " + String(e.message || e) + "</div>";
            console.error("Erreur listUsers:", e);
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (typeof verifierBannissement === "function") {
            if (!await verifierBannissement(token)) return
        }
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

