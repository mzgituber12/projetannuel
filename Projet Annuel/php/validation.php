<?php session_start();
include 'includes/api_config.php';
include 'includes/header.php'; ?>

<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta charset="UTF-8">
    <title data-i18n>valider le Préstataire</title>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<div class="container mt-5 mb-5">
    <div id="page_validation" class="row g-4">
        </div>
</div>

<script>
async function charger_ficher_presta() {
    const urlParams = new URLSearchParams(window.location.search);
    const idPresta = urlParams.get('id');
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + "/validation?id=" + idPresta);
    if (!response.ok) return;

    const f = await response.json();
    const container = document.getElementById("page_validation");

    container.innerHTML = `
        <div class="col-md-7 border-end pe-4">
            <h3 class="mb-4">Documents du prestataire</h3>
            <div id="document_dossier" class="row g-3">
                </div>
        </div>

        <div class="col-md-5 ps-4">
            <div class="bg-light p-4 rounded shadow-sm text-center">
                
                <img src="upload/${f.photo_profil}" class="rounded-circle border mb-3 shadow-sm" width="100" height="100" style="object-fit: cover;">
                
                <h4 class="fw-bold mb-4">Fiche du Préstataire</h4>
                
                <div class="text-start">
                    <label class="small text-muted mb-1">Prénom</label>
                    <div class="form-control mb-3 bg-white border-0 shadow-sm">${f.prenom}</div>

                    <label class="small text-muted mb-1">Nom</label>
                    <div class="form-control mb-3 bg-white border-0 shadow-sm">${f.nom}</div>

                    <label class="small text-muted mb-1">Date de Naissance</label>
                    <div class="form-control mb-4 bg-white border-0 shadow-sm">${new Date(f.date_naissance).toLocaleDateString()}</div>
                    <div id="donnee_text"></div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button class="btn btn-success py-2 fw-bold" onclick=valider_le_presta()>Valider le Préstataire</button>
                    <button class="btn btn-danger py-2 fw-bold"onclick=refuser_le_presta()>Refuser le Préstataire</button>
                </div>
            </div>
        </div>
    `;

    const document_dossier = document.getElementById("document_dossier");
    
    f.documents.forEach(doc => {
        if (doc.type_document !== "PF") {
            document_dossier.innerHTML += `
                <div class="col-md-6">
                    <div class="mb-2">
                        <label class="text-uppercase fw-bold small text-muted d-block mb-1">${doc.type_document}</label>
                        <img src="upload/${doc.nom_fichier}" class="img-fluid rounded border shadow-sm w-100">
                    </div>
                </div>
            `;
        }
    });

    f.documents_txt.forEach(doc_txt => {
        if (doc_txt.type_document !== "PF") {
            donnee_text.innerHTML += `
                <label class="small text-muted mb-1">${doc_txt.categorie_text}</label>
                <div class="form-control mb-3 bg-white border-0 shadow-sm">${doc_txt.contenu}</div>
            `;
        }
    });
}

    async function valider_le_presta(){
        const urlParams = new URLSearchParams(window.location.search);
        const idPresta = urlParams.get('id');
        const base = (window.API_BASE || 'http://localhost:9000');

        const response = await fetch(base + "/valider_presta?id=" + idPresta);
         if (!response.ok) {
            const text = await response.text();
            alert(text);
            window.location.href = "erreur.php?code=" + response.status;
            return;
        }

        window.location.href = "attente_validation.php"


    }

    async function refuser_le_presta(){
        const urlParams = new URLSearchParams(window.location.search);
        const idPresta = urlParams.get('id');
        const base = (window.API_BASE || 'http://localhost:9000');

        const response = await fetch(base + "/refuser_presta?id=" + idPresta);
         if (!response.ok) {
            const text = await response.text();
            alert(text);
            window.location.href = "attente_validation.php?code=" + response.status;
            return;
        }

        window.location.href = "attente_validation.php"
    }

window.addEventListener("load", charger_ficher_presta);
</script>


</body>
</html>
