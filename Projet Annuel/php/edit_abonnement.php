<?php session_start();
include 'includes/api_config.php';
include 'includes/header.php'; ?>

<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Modifier Abonnement</title>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<div class="d-flex justify-content-center">
    <div class="card p-4 shadow-sm w-100 mb-5 mt-5" style="max-width: 600px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="h3 mb-4 text-center">Modifier un abonnement</h1>

                <div class="mb-3">
                    <label class="form-label">Catégorie</label>
                    <select class="form-select" id="categorie">
                        <option value=""></option>
                        <option value="senior">Sénior</option>
                        <option value="prestataire">Prestataire</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" class="form-control" id="name_abonnement">
                </div>

                <div class="mb-3">
                    <label class="form-label">Prix/mois</label>
                    <input type="number" class="form-control" id="prix_mois_abonnement">
                </div>

                <div class="mb-3">
                    <label class="form-label">Prix/an</label>
                    <input type="number" class="form-control" id="prix_an_abonnement">
                </div>

                <div class="mb-3">
                    <label class="form-label text-danger">Nombre d'avantages :</label>
                    <select class="form-select" id="post" onchange="nombre_avantage()">
                        <option value=""></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>

                <div class="input-group mb-2">
                    <span class="input-group-text" id="icon1"></span>
                    <input type="text" class="form-control" id="ai1">
                </div>

                <div class="input-group mb-2">
                    <span class="input-group-text" id="icon2"></span>
                    <input type="text" class="form-control" id="ai2">
                </div>

                <div class="input-group mb-2">
                    <span class="input-group-text" id="icon3"></span>
                    <input type="text" class="form-control" id="ai3">
                </div>

                <div class="input-group mb-2">
                    <span class="input-group-text" id="icon4"></span>
                    <input type="text" class="form-control" id="ai4">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="liste_abonnement_admin.php" class="btn btn-secondary">Retour</a>
                    <button class="btn btn-danger" onclick="update_abonnement()">Modifier</button>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>

const base = (window.API_BASE || 'http://localhost:9000');
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get("id");

function nombre_avantage() {
    const valeur = parseInt(document.getElementById("post").value);

    for (let i = 1; i <= 4; i++) {
        const icon = document.getElementById("icon" + i);

        if (i <= valeur) {
            icon.textContent = "✅";
        } else {
            icon.textContent = "⛔";
        }
    }
}


async function load_abonnement() {
    const response = await fetch(base + "/modifier_abonnement?id=" + id, {
        headers: {
            "Token": localStorage.getItem("token")
        }
    });

    if (!response.ok) {
        alert("Erreur chargement");
        return;
    }

    const data = await response.json();

    document.getElementById("categorie").value = data.categorie;
    document.getElementById("name_abonnement").value = data.type;
    document.getElementById("prix_mois_abonnement").value = data.prix_mois;
    document.getElementById("prix_an_abonnement").value = data.prix_an;

    document.getElementById("ai1").value = data.contenue1;
    document.getElementById("ai2").value = data.contenue2;
    document.getElementById("ai3").value = data.contenue3;
    document.getElementById("ai4").value = data.contenue4;

    document.getElementById("post").value = data.nb_avantage;
    nombre_avantage();
}
async function update_abonnement() {
    const response = await fetch(base + "/update_abonnement?id=" + id, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Token": localStorage.getItem("token") || ""
        },
        body: JSON.stringify({
            categorie: document.getElementById("categorie").value,
            name_abonnement: document.getElementById("name_abonnement").value.trim(),
            prix_mois_abonnement: parseFloat(document.getElementById("prix_mois_abonnement").value),
            prix_an_abonnement: parseFloat(document.getElementById("prix_an_abonnement").value),
            nb_avantage: parseInt(document.getElementById("post").value),
            ai1: document.getElementById("ai1").value.trim(),
            ai2: document.getElementById("ai2").value.trim(),
            ai3: document.getElementById("ai3").value.trim(),
            ai4: document.getElementById("ai4").value.trim()
        })
    });

    if (!response.ok) {
        const text = await response.text();
        alert(text);
        return;
    }

    alert("Abonnement modifié !");
    window.location.href = "liste_abonnement_admin.php";
}


load_abonnement();

</script>

</body>
</html>