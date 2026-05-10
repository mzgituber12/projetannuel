<?php
include 'includes/api_config.php';
include 'includes/header.php'?>

<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Mon Profil</title>
    <style>
    .mb-custom{
        margin-bottom: 2.3rem
    }
    </style>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<div class="container mt-5">

    <h1 class="mb-custom text-center" style="font-size:50px">
        Mon profil
    </h1>

    <h2 data-i18n></h2>
    <h2 id="status"></h2>

    <div class="d-flex justify-content-center m-4">
        <button type="button"
                class="btn btn-warning"
                onclick="recommencer_tuto()"
                data-i18n>
            Recommencer le tutoriel
        </button>
    </div>

</div>

<div class="row justify-content-center">
    <div class="col-md-6 mb-5 mt-2">
        <div class="mb-3 position-relative" data-i18n id="popover4" data-bs-toggle="popover" data-bs-title="Modifier mon profil" data-bs-content="Sur cette page vous pourrez consultez votre profil et le modifier !<br> Maintenant cliquer à nouveau sur le menu, puis 'Postuler'<br><div class='d-flex justify-content-between align-items-center mt-3'><button class='btn btn-sm btn-primary mt-2' onclick='tuto()'>Suivant</button><button class='btn btn-sm btn-danger mt-2' onclick='fin_tuto()'>Arreter le Tuto</button></div>">            <label for="Email" class="form-label" data-i18n>Email</label>
            <div class="d-flex">
                <input type="email" class="form-control me-2" id="email">
                <button type="button" class="btn btn-danger" onclick="update_profil('email')" data-i18n>Modifier</button>
            </div>
        </div>

        <div class="mb-3">
            <label for="Mot de passe" class="form-label" data-i18n>Mot de passe</label>
            <div class="d-flex">
                <input type="password" class="form-control" id="password" >
                <button type="button" class="btn btn-danger" onclick="update_profil('password')" data-i18n>Modifier</button>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="checkmdp">
            <label class="form-check-label" for="checkmdp" data-i18n>Voir</label>
        </div>
        <script> 
        const champs = document.getElementById("password")
        const check = document.getElementById("checkmdp")
        
        check.addEventListener("change", function() {
            if (this.checked)
                champs.type = "text"
            else champs.type = "password"
        });
        </script>

        <div class="mb-3">
            <label for="Prénom" class="form-label" data-i18n>Prénom</label>
            <div class="d-flex">
                <input type="text" class="form-control" id="prenom" >
                <button type="button" class="btn btn-danger" onclick="update_profil('prenom')" data-i18n>Modifier</button>
            </div>
        </div>

        <div class="mb-3">
            <label for="Nom" class="form-label" data-i18n>Nom</label>
            <div class="d-flex">
                <input type="text" class="form-control" id="nom" >
                <button type="button" class="btn btn-danger" onclick="update_profil('nom')" data-i18n>Modifier</button>
            </div>
        </div>

        <div class="mb-3">
            <label for="Date" class="form-label" data-i18n>Date naissance</label>
            <div class="d-flex">
                <input type="date" class="form-control" id="date_naissance" >
                <button type="button" class="btn btn-danger" onclick="update_profil('date_naissance')" data-i18n>Modifier</button>
            </div>
        </div>

        <div class="mb-3">
            <label for="phoneExample" class="form-label">n°téléphone</label>
            <div class="d-flex">
            <input type="tel" class="form-control" id="telephone" pattern="^06+[0-6]{8}$">
            <button type="button" class="btn btn-danger" onclick="update_profil('telephone')" data-i18n>Modifier</button>
        </div>
    </div>

        <div class="mb-3">
            <label for="langue" class="form-label" data-i18n>Langue</label>
            <div class="d-flex">
                <select class="form-select" id="langue">
                    <option value="fr" data-i18n>Francais</option>
                    <option value="en" data-i18n>English</option>
                    <option value="it" data-i18n>Italiano</option>
                    <option value="de" data-i18n>Deutsch</option>
                    <option value="ru" data-i18n>Russkiy</option>
                    <option value="uk" data-i18n>Ukrayinska</option>
                    <option value="pt" data-i18n>Portugues</option>
                    <option value="pl" data-i18n>Polski</option>
                    <option value="nl" data-i18n>Nederlands</option>
                </select>
                <button type="button" class="btn btn-danger ms-2" onclick="update_profil('langue')" data-i18n>Modifier</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'?>

<script>

    async function recommencer_tuto() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/reco_tuto", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Token": localStorage.getItem("token")
            },
        });

         if (response.ok){
            window.location.href = "index.php"
        }
    }


    let profil_state = 0

    async function afficher_profil() {

        const token = localStorage.getItem("token");
        
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/mon_profil", {
        method: "GET",
        headers: {"Token": token},
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
        const data = await response.json();
         document.getElementById("email").placeholder = data.email
         document.getElementById("prenom").placeholder = data.prenom
         document.getElementById("nom").placeholder = data.nom
         document.getElementById("date_naissance").placeholder = data.date_naissance
         document.getElementById("telephone").placeholder = data.telephone
            document.getElementById("langue").value = data.langue || "fr"

         if (profil_state == 1){
                profil_state = 0
            document.getElementById("status").innerHTML = "Profil modifié avec succes"
         }
}

async function update_profil(update) {

    if (!document.getElementById(update).checkValidity()) {

        document.getElementById("status").innerHTML = "Veuillez entrer un email valide";
        return;
    }
    const champ = update
    const value_champ = document.getElementById(update).value;
    const token = localStorage.getItem("token");

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/update_profil", {
            method: "POST",
            headers: {
                "Token": token,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({champ: champ, value: value_champ })
        });
    if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
    const data = await response.json();
    if (data.message != "Profil modifié avec succes"){
        document.getElementById("status").innerHTML = data.message
        return
    }
    profil_state = 1
    afficher_profil()
}

async function init(){
        const token = localStorage.getItem("token")
        loginUser("online", token)
        afficher_profil()
    }

init()

</script>
