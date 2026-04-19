<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Creer un service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

<?php include 'includes/header.php'?>

<h1 data-i18n>Creer un service</h1>
<h2 id="admin_err"></h2>

<form onsubmit="createService(event)">
            <label data-i18n>Nom :</label>
            <input type="text" name="nom" id="service_nom" placeholder="Nom" required><br><br>
            <label data-i18n>Description :</label>
            <textarea name="description" id="service_description" placeholder="Description" required></textarea><br><br>
            <label data-i18n>Tarif :</label>
            <input type="number" name="tarif" id="service_tarif" placeholder="Tarif" step="0.01" required><br><br>
            <label data-i18n>Catégorie :</label>
            <select id="service_id_categorie" class="form-select" style="max-width: 420px;">
                <option value="" data-i18n>Aucune catégorie</option>
            </select><br><br>
            <label data-i18n>Nouvelle catégorie :</label>
            <div class="d-flex flex-wrap gap-2 align-items-center" style="max-width: 520px;">
                <input type="text" id="nouvelle_categorie_nom" class="form-control" placeholder="Nom de la catégorie">
                <button type="button" class="btn btn-outline-secondary" onclick="creerNouvelleCategorie()" data-i18n>Créer la catégorie</button>
            </div>
            <p class="text-muted small" data-i18n>La nouvelle catégorie est ajoutée à la liste et sélectionnée automatiquement.</p><br>
            <label data-i18n>Image (optionnel) :</label>
            <input type="file" name="image" id="service_image" accept="image/*" onchange="previewImage()"><br>
            <div id="imagePreview"></div><br>
            <button type="submit" data-i18n>Creer le service</button>
</form>

<?php include 'includes/footer.php'?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
    function previewImage() {
        const fileInput = document.getElementById('service_image');
        const preview = document.getElementById('imagePreview');
        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 300px; max-height: 300px; border-radius: 5px;">';
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    async function loadCategoriesIntoSelect() {
        const base = (window.API_BASE || "http://localhost:9000");
        const response = await fetch(base + "/categories", { method: "GET" });
        if (!response.ok) return;
        const payload = await response.json();
        const list = payload.categorie || [];
        const sel = document.getElementById("service_id_categorie");
        const prev = sel.value;
        sel.innerHTML = '<option value="" data-i18n>Aucune catégorie</option>';
        list.forEach((c) => {
            const opt = document.createElement("option");
            opt.value = String(c.id);
            opt.textContent = c.nom;
            sel.appendChild(opt);
        });
        if (prev && [...sel.options].some((o) => o.value === prev)) {
            sel.value = prev;
        }
    }

    async function creerNouvelleCategorie() {
        const nom = document.getElementById("nouvelle_categorie_nom").value.trim();
        if (!nom) {
            document.getElementById("admin_err").innerHTML = "Indiquez un nom pour la catégorie.";
            return;
        }
        const base = (window.API_BASE || "http://localhost:9000");
        const response = await fetch(base + "/creer_categorie", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ nom: nom }),
        });
        if (!response.ok) {
            const text = await response.text();
            document.getElementById("admin_err").innerHTML = text || "Erreur lors de la création de la catégorie.";
            return;
        }
        const cat = await response.json();
        const sel = document.getElementById("service_id_categorie");
        const opt = document.createElement("option");
        opt.value = String(cat.id);
        opt.textContent = cat.nom;
        sel.appendChild(opt);
        sel.value = String(cat.id);
        document.getElementById("nouvelle_categorie_nom").value = "";
        document.getElementById("admin_err").innerHTML = "";
    }

    async function createService(e) {
        e.preventDefault();

        let imageValue = "";
        const imageInput = document.getElementById('service_image');
        if (imageInput.files && imageInput.files.length > 0) {
            const uploadFormData = new FormData();
            uploadFormData.append("file", imageInput.files[0]);
            uploadFormData.append("uploadType", "service");

            const uploadResponse = await fetch("upload_image.php", {
                method: "POST",
                body: uploadFormData
            });
            const uploadData = await uploadResponse.json();
            if (!uploadResponse.ok || !uploadData.success) {
                document.getElementById("admin_err").innerHTML = uploadData.message || "Erreur lors de l'upload de l'image.";
                return;
            }
            imageValue = uploadData.fileName;
        }

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/creer_service", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                nom: document.getElementById('service_nom').value,
                description: document.getElementById('service_description').value,
                tarif: parseFloat(document.getElementById('service_tarif').value),
                image: imageValue,
                id_categorie: parseInt(document.getElementById('service_id_categorie').value, 10) || 0,
            })
        });

        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }

        const data = await response.json();
        if (data.value == 1) {
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = "gestion_service.php?message=" + data.message;
        } else {
            document.getElementById("admin_err").innerHTML = data.message;
        }
    }

    async function init(){
        const token = localStorage.getItem("token")
        if (!await loginUser("online", token)) return
        await adminUser(token)
        await loadCategoriesIntoSelect()
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