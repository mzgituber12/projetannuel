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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Ajouter Abonnement</title>
</head>
<body>

<div class="d-flex justify-content-center">
    <div class="card p-4 shadow-sm w-100 mb-5 mt-5" style="max-width: 600px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="h3 mb-4">Créer un abonnement</h1>

                <div class="mb-3">
                    <label for="categorie" class="form-label">L'abonnement sera pour :</label>
                    <select class="form-select" id="categorie">
                        <option value=""></option>
                        <option value="senior">Sénior</option>
                        <option value="prestataire">Prestataire</option>
                    </select>
                </div>

                <div class="mb-3 position-relative">
                    <label for="name_abonnement" class="form-label">Nom de l'abonnement</label>
                    <div class="d-flex">
                        <input type="text" class="form-control me-2" id="name_abonnement">
                    </div>
                </div>

                <div class="mb-3 position-relative">
                    <label for="prix_mois_abonnement" class="form-label">Prix/mois</label>
                    <div class="d-flex">
                        <input type="number" class="form-control me-2" id="prix_mois_abonnement" min="0" step="0.01">
                    </div>
                </div>

                <div class="mb-3 position-relative">
                    <label for="prix_an_abonnement" class="form-label">Prix/an</label>
                    <div class="d-flex">
                        <input type="number" class="form-control me-2" id="prix_an_abonnement" min="0" step="0.01">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="post" class="form-label text-danger">Définir le nombre d'avantages :</label>
                    <select class="form-select" id="post" onchange="nombre_avantage()">
                        <option value=""></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>

                <div class="mb-3 position-relative">
                    <label for="ai1" class="form-label">Ajouter Avantage/inconvénient</label>
                    <div class="d-flex">
                        <input type="text" class="form-control me-2" id="ai1">
                    </div>
                </div>
                <div class="mb-3 position-relative">
                    <label for="ai2" class="form-label">Ajouter Avantage/inconvénient</label>
                    <div class="d-flex">
                        <input type="text" class="form-control me-2" id="ai2">
                    </div>
                </div>
                <div class="mb-3 position-relative">
                    <label for="ai3" class="form-label">Ajouter Avantage/inconvénient</label>
                    <div class="d-flex">
                        <input type="text" class="form-control me-2" id="ai3">
                    </div>
                </div>
                <div class="mb-3 position-relative">
                    <label for="ai4" class="form-label">Ajouter Avantage/inconvénient</label>
                    <div class="d-flex">
                        <input type="text" class="form-control me-2" id="ai4">
                    </div>
                </div>

                <div class="d-flex justify-content-md-end gap-2">
                    <a href="liste_abonnement_admin.php" class="btn btn-outline-secondary">Retour</a>
                    <button type="button" class="btn btn-danger" onclick="add_abonnement()">Ajouter</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function nombre_avantage() {
    const valeur = document.getElementById("post").value;

    if (valeur == "1") {
        document.getElementById("ai1").value = "✅ ";
        document.getElementById("ai2").value = "⛔ ";
        document.getElementById("ai3").value = "⛔ ";
        document.getElementById("ai4").value = "⛔ ";
    }

    if (valeur == "2") {
        document.getElementById("ai1").value = "✅ ";
        document.getElementById("ai2").value = "✅ ";
        document.getElementById("ai3").value = "⛔ ";
        document.getElementById("ai4").value = "⛔ ";
    }

    if (valeur == "3") {
        document.getElementById("ai1").value = "✅ ";
        document.getElementById("ai2").value = "✅ ";
        document.getElementById("ai3").value = "✅ ";
        document.getElementById("ai4").value = "⛔ ";
    }

    if (valeur == "4") {
        document.getElementById("ai1").value = "✅ ";
        document.getElementById("ai2").value = "✅ ";
        document.getElementById("ai3").value = "✅ ";
        document.getElementById("ai4").value = "✅ ";
    }
}

async function add_abonnement() {
    const categorie = document.getElementById("categorie").value;
    const name_abonnement = document.getElementById("name_abonnement").value.trim();
    const prix_mois_abonnement = document.getElementById("prix_mois_abonnement").value;
    const prix_an_abonnement = document.getElementById("prix_an_abonnement").value;
    const ai1 = document.getElementById("ai1").value.trim();
    const ai2 = document.getElementById("ai2").value.trim();
    const ai3 = document.getElementById("ai3").value.trim();
    const ai4 = document.getElementById("ai4").value.trim();

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/add_abonnement", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Token": localStorage.getItem("token") || ""
        },
        body: JSON.stringify({
            categorie: categorie,
            name_abonnement: name_abonnement,
            prix_mois_abonnement: parseFloat(prix_mois_abonnement),
            prix_an_abonnement: parseFloat(prix_an_abonnement),
            ai1: ai1,
            ai2: ai2,
            ai3: ai3,
            ai4: ai4
        })
    });

    if (!response.ok) {
        const text = await response.text();
        alert(text);
        window.location.href = "erreur.php?code=" + response.status;
        return;
    }

    alert("Abonnement ajouté avec succès");
    window.location.href = "liste_abonnement_admin.php";
}
</script>

</body>
</html>