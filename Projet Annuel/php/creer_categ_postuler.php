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
    <title data-i18n>Creer Catégorie</title>
    <link rel="stylesheet" href="police.css">
</head>
<body>
    <div class="d-flex justify-content-center">
    <div class="card p-4 shadow-sm w-100 mb-5 mt-5" style="max-width: 700px;">
        <div class="row justify-content-center">
            <div class="col-md-10">


                <div class="mb-3">
                    <label class="form-label" data-i18n>Nom de la nouvelle catégorie</label>
                    <div class="d-flex">
                        <input class="form-control" id="nom_categorie" type="text">
                    </div>
                </div>


                <div class="mb-3">
                    <label for="post" class="form-label" data-i18n>Choisir le nombre de champs supplémentaire</label>
                    <div class="d-flex gap-2">
                        <input class="form-control" id="nb_champ" type="number" min="1" max="15">
                        <button type="button" class="btn btn-danger" onclick="add_champ()" data-i18n>Confirmer</button>
                    </div>
                </div>

                <div id="add_champs"></div>

                <button type="button" class="btn btn-danger" onclick="new_categorie()" data-i18n>Ajouter</button>

            </div>
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>
<script>
    async function new_categorie() {
        const nb_champs = parseInt(document.getElementById("nb_champ").value);
        const nom_categorie = document.getElementById("nom_categorie").value;
        const data = {
            nom_categorie: nom_categorie,
            champs: []
        };

        for(let i = 1; i <= nb_champs; i++){
            const label = document.getElementById(`label_${i}`).value
            const input_id = document.getElementById(`input_id_${i}`).value
            const type = document.getElementById(`type_${i}`).value

                data.champs.push({
                    label: label,
                    type: type,
                    input_id: input_id
                });
        }

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/new_champs_postuler", {
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
            window.location.href = "creer_categ_postuler.php?code=" + response.status;
            return;
        }

        window.location.href = "index.php";
    }




     async function add_champ() {
        const nbChamps = parseInt(document.getElementById("nb_champ").value);
        const container = document.getElementById("add_champs");        
        container.innerHTML = "";

        for (let i = 1; i <= nbChamps; i++) {
            container.innerHTML += `
                <div class="card p-3 mb-3 bg-light">
                    <h6 class="text-secondary mb-3">Champ n°${i}</h6>
                    
                    <div class="mb-2">
                        <label class="form-label">Label du champ</label>
                        <input type="text" class="form-control" id="label_${i}" placeholder="ex: Permis de conduire">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Type du champ</label>
                        <select class="form-select" id="type_${i}">
                            <option value="text">Texte</option>
                            <option value="file">Fichier</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">id</label>
                        <input type="text" class="form-control" id="input_id_${i}" placeholder="ex: permis_de_conduire">
                    </div>
                </div>
            `;
        }
    }
</script>
