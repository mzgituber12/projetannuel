<?php session_start();
include 'includes/api_config.php';
include 'includes/header.php'; ?>

<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Demande Prestataire</title>
</head>
<body>

<div class="d-flex justify-content-center">
    <div class="card p-4 shadow-sm w-100 mb-5 mt-5" style="max-width: 700px;">
        <div class="row justify-content-center">
            <div class="col-md-10">

                <div class="mb-3">
                    <label for="post" class="form-label">Choisir votre domaine :</label>
                    <select class="form-select" name="post" id="post" onchange="champ_domaine()">
                        <option value="">Selectionner</option>
                        <option value="Transport">Transport</option>
                        <option value="Soin et bien-etre">Soin et bien-etre</option>
                        <option value="Tourisme / Hebergement">Tourisme / Hebergement</option>
                        <option value="Service a domicile">Service a domicile</option>
                        <option value="Loisirs & Sortie">Loisirs & Sortie</option>
                        <option value="Shoping">Shoping</option>
                    </select>
                </div>

                <div class="mb-3 position-relative">
                    <label for="email" class="form-label">Email</label>
                    <div class="d-flex">
                        <input type="email" class="form-control me-2" id="email">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="prenom" class="form-label">Prenom</label>
                    <div class="d-flex">
                        <input type="text" class="form-control me-2" id="prenom">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <div class="d-flex">
                        <input type="text" class="form-control me-2" id="nom">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="age" class="form-label">Age</label>
                    <div class="d-flex">
                        <input type="number" class="form-control me-2" id="age">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Telephone</label>
                    <div class="d-flex">
                        <input type="text" class="form-control me-2" id="telephone">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Piece d'identite (recto)</label>
                    <div class="d-flex">
                        <input class="form-control" type="file">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Piece d'identite (verso)</label>
                    <div class="d-flex">
                        <input class="form-control" type="file">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Diplome</label>
                    <div class="d-flex">
                        <input class="form-control" type="file">
                    </div>
                </div>

                <div id="domain_champ"></div>

                <button type="button" class="btn btn-danger" onclick="alert('Formulaire ajoute. Envoi backend a brancher.')">Postuler</button>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function champ_domaine() {
    const valeur = document.getElementById("post").value;
    const ajout_champ = document.getElementById("domain_champ");

    if (valeur === "Transport") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Permis de conduire</label>
                <input class="form-control" type="file">
            </div>
            <div class="mb-3">
                <label class="form-label">Assurance vehicule</label>
                <input class="form-control" type="file">
            </div>
        `;
    }

    if (valeur == "Soin et bien-etre") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Diplome / certification</label>
                <input class="form-control" type="file">
            </div>
            <div class="mb-3">
                <label class="form-label">Specialite</label>
                <input class="form-control" type="text">
            </div>
        `;
    }

    if (valeur == "Service a domicile") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Permis de conduire</label>
                <input class="form-control" type="file">
            </div>
            <div class="mb-3">
                <label class="form-label">Experience</label>
                <input class="form-control" type="text">
            </div>
        `;
    }

    if (valeur == "Loisirs & Sortie") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Type d'activite</label>
                <input class="form-control" type="text">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <input class="form-control" type="text">
            </div>
            <div class="mb-3">
                <label class="form-label">Permis de conduire</label>
                <input class="form-control" type="file">
            </div>
        `;
    }

    if (valeur == "Shoping") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Permis de conduire</label>
                <input class="form-control" type="file">
            </div>
        `;
    }

    if (valeur == "Tourisme / Hebergement") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Choisir Type de logement :</label>
                <select class="form-select">
                    <option value="Chambres d'Hotel">Chambres d'Hotel</option>
                    <option value="Gites">Gites</option>
                    <option value="Camping">Camping</option>
                    <option value="Logements Insolites">Logements Insolites</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Adresse</label>
                <input class="form-control" type="text">
            </div>
            <div class="mb-3">
                <label class="form-label">Photos</label>
                <input class="form-control" type="file">
            </div>
        `;
    }
}
</script>

</body>
</html>
