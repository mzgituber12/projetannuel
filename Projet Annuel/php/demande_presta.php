<?php
include 'includes/api_config.php';
include 'includes/header.php'; ?>

<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Demande Prestataire</title>
</head>
<body>

<div class="d-flex justify-content-center">
    <div class="card p-4 shadow-sm w-100 mb-5 mt-5" style="max-width: 700px;">
        <div class="row justify-content-center">
            <div class="col-md-10">

                <div class="mb-3">
                    <label for="post" class="form-label" data-i18n>Choisir votre domaine :</label>
                    <select class="form-select" name="post" id="post" onchange="champ_domaine()">
                        <option value="" data-i18n>Selectionner</option>
                        <option value="Transport" data-i18n>Transport</option>
                        <option value="Soin et bien-etre" data-i18n>Soin et bien-etre</option>
                        <option value="Tourisme / Hebergement" data-i18n>Tourisme / Hebergement</option>
                        <option value="Service a domicile" data-i18n>Service a domicile</option>
                        <option value="Loisirs &amp; Sortie" data-i18n>Loisirs &amp; Sortie</option>
                        <option value="Shoping" data-i18n>Shoping</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" data-i18n>Photo de profil</label>
                    <div class="d-flex">
                        <input class="form-control" id="photo_de_profil" type="file">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" data-i18n>Piece d'identite (recto)</label>
                    <div class="d-flex">
                        <input class="form-control" id="piece_identite_recto" type="file">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" data-i18n>Piece d'identite (verso)</label>
                    <div class="d-flex">
                        <input class="form-control" id="piece_identite_verso" type="file">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" data-i18n>Diplome (le plus récent)</label>
                    <div class="d-flex">
                        <input class="form-control" id="diplome" type="file">
                    </div>
                </div>

                <div id="domain_champ"></div>

                <button type="button" class="btn btn-danger" onclick="alert('Formulaire ajoute. Envoi backend a brancher.'), save_demande()" data-i18n>Postuler</button>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    async function upload_fichier(input, type) {

        let imageValue = "";
        const imageInput = document.getElementById(input);
        if (imageInput.files && imageInput.files.length > 0) {
            const uploadFormData = new FormData();
            uploadFormData.append("file", imageInput.files[0]);
            uploadFormData.append("uploadType", type);

            const uploadResponse = await fetch("upload_image.php", {
                method: "POST",
                body: uploadFormData
            });
            const uploadData = await uploadResponse.json();
            if (!uploadResponse.ok || !uploadData.success) {
                document.getElementById("admin_err").innerHTML = uploadData.message || "Erreur lors de l'upload de l'image.";
                return;
            }
            return imageValue = uploadData.fileName;
        }
}

        
    
   async function save_demande(){
        const photo_de_profil = await upload_fichier("photo_de_profil", "PF")
        const valeur = document.getElementById("post").value;
        const piece_identite_recto = await upload_fichier("piece_identite_recto", "CIR");
        const piece_identite_verso = await upload_fichier("piece_identite_verso", "CIV");
        const diplome = await upload_fichier("diplome", "diplome");

        const data = {
        type: valeur,
        photo_de_profil: photo_de_profil,
        piece_identite_recto: piece_identite_recto, 
        piece_identite_verso: piece_identite_verso,
        diplome: diplome,
        autre: {}
    };



        if (valeur === "Transport") {
            data.autre.permis = await upload_fichier('permis_de_conduire', 'permis');
            data.autre.assurance_vehicule = await upload_fichier('assurance_vehicule', 'assurance');
        }           

         if (valeur == "Soin et bien-etre") {
            data.autre.certification = await upload_fichier('certification', 'autre');
            data.autre.specialite = document.getElementById("specialite").value;
        }

        if (valeur == "Service a domicile") {
            data.autre.permis = await upload_fichier('permis_service', 'permis');
            data.autre.experience = document.getElementById("experience").value;
        }

        if (valeur == "Loisirs & Sortie") {
            data.autre.type_activite = document.getElementById("type_activite").value;
            data.autre.description = document.getElementById("description").value;
            data.autre.permis = await upload_fichier('permis_loisir', 'permis');
        }

        if (valeur == "Shoping") {
            data.autre.livraison = await upload_fichier('livraison', 'livraison');
        }

        if (valeur == "Tourisme / Hebergement") {
            data.autre.type_logement = document.getElementById("type_logement").value;
            data.autre.adresse = document.getElementById("adresse").value;
            data.autre.photos = await upload_fichier('photos_logement', 'logement');
        }
            
            const base = (window.API_BASE || 'http://localhost:9000');
                const response = await fetch(base + "/demande_presta", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Token": localStorage.getItem("token") || ""
                    },
                    body: JSON.stringify(
                        data
                    )
                });

                if (!response.ok) {
                    const text = await response.text();
                    alert(text);
                    window.location.href = "erreur.php?code=" + response.status;
                    return;
                }

                window.location.href = "index.php";

        }




function champ_domaine() {
    const valeur = document.getElementById("post").value;
    const ajout_champ = document.getElementById("domain_champ");

    if (valeur === "Transport") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Permis de conduire</label>
                <input class="form-control" id="permis_de_conduire" type="file">
            </div>
            <div class="mb-3">
                <label class="form-label">Assurance véhicule</label>
                <input class="form-control" id="assurance_vehicule" type="file">
            </div>
        `;
    }

    if (valeur == "Soin et bien-etre") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Certification</label>
                <input class="form-control" id="certification" type="file">
            </div>
            <div class="mb-3">
                <label class="form-label">Specialite</label>
                <input class="form-control" id="specialite" type="text">
            </div>
        `;
    }


    if (valeur == "Service a domicile") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Permis de conduire</label>
                <input class="form-control" id="permis_service" type="file">
            </div>
            <div class="mb-3">
                <label class="form-label">Experience</label>
                <input class="form-control" id="experience" type="text">
            </div>
        `;
    }

    if (valeur == "Loisirs & Sortie") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Type d'activité</label>
                <input class="form-control" id="type_activite" type="text">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <input class="form-control" id="description" type="text">
            </div>
            <div class="mb-3">
                <label class="form-label">Permis de conduire</label>
                <input class="form-control" id="permis_loisir" type="file">
            </div>
        `;
    }

    if (valeur == "Shoping") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Preuve de livraison</label>
                <input class="form-control" id="livraison" type="file">
            </div>
        `;
    }

    if (valeur == "Tourisme / Hebergement") {
        ajout_champ.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Type de logement</label>
                <select class="form-select" id="type_logement">
                    <option value="Chambres d'Hotel">Chambres d'Hotel</option>
                    <option value="Gites">Gites</option>
                    <option value="Camping">Camping</option>
                    <option value="Logements Insolites">Logements Insolites</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Adresse</label>
                <input class="form-control" id="adresse" type="text">
            </div>
            <div class="mb-3">
                <label class="form-label">Photos</label>
                <input class="form-control" id="photos_logement" type="file">
            </div>
        `;
    }
}

</script>

</body>
</html>
