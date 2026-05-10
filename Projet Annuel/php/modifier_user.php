<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title id ="page_title"></title>
    <style>
        .mb-custom{
            margin-bottom: 2rem
        }
        .mt-custom{
            margin-top: 2rem
        }
    </style>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class='container mt-5'>
<h1 id="admin_title" data-i18n class='mb-custom text-center ms-4' style='font-size:50px'></h1>
<h2 id ="admin_err" class="mt-4"></h2>

<div id="resultat" class="mt-custom p-3 pb-1 border rounded bg-light"></div>
</div>

<div class="mt-4"></div>
<?php include 'includes/footer.php'?>

<script>
    async function updateUser(event) {
        event.preventDefault();

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/modifier_user/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "PATCH",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                nom: document.getElementById('user_nom').value,
                prenom: document.getElementById('user_prenom').value,
                age: parseInt(document.getElementById("user_age").value, 10),
                email: document.getElementById('user_email').value,
                role: document.getElementById('user_role').value,
                langue: document.getElementById('user_langue').value
            })
        });
        
        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

        const data = await response.json();
        if (data.value == 1) {
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = "gestion_user.php?message=" + data.message;
        } else {
            document.getElementById("admin_err").innerHTML = data.message;
        }
    }   

    async function search_user() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_user_id/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "GET",
        });

        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

        const data = await response.json();

        document.getElementById("page_title").innerHTML = "Modifier l'utilisateur " + data.email;
        document.getElementById('admin_title').innerHTML = "Modification de l'utilisateur " + data.email;
        if(data.id == 0) {
            document.getElementById("resultat").innerHTML = "Aucun utilisateur trouvé";
        } else {
            document.getElementById("resultat").innerHTML = `
            <form onsubmit="updateUser(event)" class="text-start">

            <label>ID :</label>
            <input class="form-control mb-2" type="number" value="${data.id}" readonly>

            <label>Nom :</label>
            <input class="form-control mb-2" type="text" id="user_nom" value="${data.nom}" required>

            <label>Prénom :</label>
            <input class="form-control mb-2" type="text" id="user_prenom" value="${data.prenom}" required>

            <label>Âge :</label>
            <input class="form-control mb-2" type="number" id="user_age" value="${data.age}" required>

            <label>Email :</label>
            <input class="form-control mb-2" type="email" id="user_email" value="${data.email}" required>

            <label>Role :</label>
            <input class="form-control mb-2" type="text" id="user_role" value="${data.role}" required>

            <label>Langue :</label>
            <input class="form-control mb-4" type="text" id="user_langue" value="${data.langue}" required>

            <button type="submit" class="btn btn-primary w-100">
                Confirmer les modifications
            </button>
            </form>
            `;
            }
        }

    async function init(){
        const token = localStorage.getItem("token")
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        search_user()
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
