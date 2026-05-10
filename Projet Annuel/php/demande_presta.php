<?php
include 'includes/api_config.php';
include 'includes/header.php'; 
?>

<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Postuler</title>
    <style>
        .mb-custom{
            margin-bottom: 2rem
        }
    </style>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<div class="container mt-5">
    <h1 class='mb-custom text-center ms-4' style='font-size:50px'>Postuler</h1>
    <p class="text-center">
        Vous pouvez soumettre une demande pour devenir prestataire. Votre candidature sera étudiée et validée par les experts de Silver Happy.  
        <br>Vous devrez d’abord fournir plusieurs documents afin de permettre votre certification.
    </p>
</div>

<div class="d-flex justify-content-center">
    <div class="card p-4 shadow-sm w-100 mb-5 mt-4" style="max-width: 700px;">
        <div class="row justify-content-center" data-i18n id="popover5" class="col-md-4 d-flex justify-content-center" data-bs-toggle="popover" data-bs-title="Postuler Pour Silver Happy" data-bs-content="Vous souhaitez faire partie de notre équipe et vous souhaitez faire part de vos prestations, envoyez votre candidature ici !<br><div class='d-flex justify-content-between align-items-center mt-3'><button class='btn btn-sm btn-primary mt-2' onclick='tuto()'>Suivant</button><button class='btn btn-sm btn-danger mt-2' onclick='fin_tuto()'>Arreter le Tuto</button></div>">
            <div class="col-md-10">
                <div class="mb-3">
                    <label for="post" class="form-label" data-i18n>Choisir votre domaine :</label>
                    <select class="form-select" name="post" id="post" onchange="champ_domaine()">
                        <option value="" data-i18n>Selectionner</option>
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
                
                <div id="admin_err" class="text-danger mb-3"></div>

                <button type="button" class="btn btn-danger" onclick="save_demande()" data-i18n>Postuler</button>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    let configCategories = {};
    document.addEventListener("DOMContentLoaded", async () => {
        await chargerCategories();
    });

    async function chargerCategories() {
        const base = (window.API_BASE || 'http://localhost:9000');
        
        const response = await fetch(base + "/get_champs_postuler");
        if (response.ok) {
            configCategories = await response.json();
            
            const select = document.getElementById("post");
            select.innerHTML = '<option value="" data-i18n>Selectionner</option>';
            
            for (const categorie_name in configCategories) {
                const option = document.createElement("option");
                option.value = categorie_name;
                option.textContent = categorie_name;
                select.appendChild(option);
            }
        } else {
            const text = await response.text();
            alert(text);
            window.location.href = "erreur.php?code=" + response.status;
        }
    }

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
                alert("Erreur lors de l'upload de l'image.");
                window.location.href = "erreur.php?code=500";
            }
            imageValue = uploadData.fileName;
        }
        return imageValue;
    }

    async function save_demande() {
        const photo_de_profil = await upload_fichier("photo_de_profil", "PF");
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
            autre: {},
            autre_txt: {}
        };

        if (configCategories[valeur]) {
            for (let champ of configCategories[valeur]) {
                if (champ.type === "file") {
                    data.autre[champ.id] = await upload_fichier(champ.id, 'autre');
                } else {
                    const elem = document.getElementById(champ.id);
                    data.autre_txt[champ.id] = elem ? elem.value : "";
                }
            }
        }
            
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/demande_presta", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Token": localStorage.getItem("token")
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const text = await response.text();
            alert(text);
            window.location.href = "erreur.php?code=" + response.status;
            return;
        }
        alert("Votre demande a bien été prise en compte !")
        window.location.href = "index.php";
        
    }

    function champ_domaine() {
        const valeur = document.getElementById("post").value;
        const ajout_champ = document.getElementById("domain_champ");

        ajout_champ.innerHTML = "";

        if (configCategories[valeur]) {
            configCategories[valeur].forEach(champ => {
                let inputHTML = "";

                if (champ.type === "file") {
                    inputHTML = `<input class="form-control" id="${champ.id}" type="file">`;
                } else if (champ.type === "select") {
                    inputHTML = `
                        <select class="form-select" id="${champ.id}">
                            <option value="Chambres d'Hotel">Chambres d'Hotel</option>
                            <option value="Gites">Gites</option>
                            <option value="Camping">Camping</option>
                            <option value="Logements Insolites">Logements Insolites</option>
                        </select>
                    `;
                } else {
                    inputHTML = `<input class="form-control" id="${champ.id}" type="text">`;
                }

                ajout_champ.innerHTML += `
                    <div class="mb-3">
                        <label class="form-label">${champ.label}</label>
                        ${inputHTML}
                    </div>
                `;
            });
        }
    }
</script>
</body>
</html>