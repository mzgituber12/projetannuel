<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("online", localStorage.getItem('token')); 
</script>
<script src="admin.js"></script>
<script>
adminUser(localStorage.getItem('token')); 
</script>

<script>
    const isNewConseils = <?php echo json_encode($_GET["id"] === "new"); ?>;
    const conseilId = <?php echo json_encode($_GET["id"]); ?>;
    let currentImage = "";

    function setError(message) {
        const el = document.getElementById("err_message");
        if (el) el.textContent = message || "";
    }

    async function updateConseils(event) {
        event.preventDefault();
        setError("");

        const titreInput = document.getElementById("conseil_titre");
        const contenuInput = document.getElementById("conseil_contenu");
        const imageInput = document.getElementById("conseil_image");

        if (!titreInput || !contenuInput) {
            setError("Formulaire invalide.");
            return;
        }

        const titre = titreInput.value.trim();
        const contenu = contenuInput.value.trim();
        if (!titre || !contenu) {
            setError("Le titre et le contenu sont obligatoires.");
            return;
        }

        let imageValue = "";

        if (imageInput && imageInput.files && imageInput.files.length > 0) {
            const file = imageInput.files[0];

            const uploadFormData = new FormData();
            uploadFormData.append("file", file);
            uploadFormData.append("uploadType", "conseil");

            const uploadResponse = await fetch("upload_image.php", {
                method: "POST",
                body: uploadFormData
            });

            const uploadRaw = await uploadResponse.text();
            let uploadData = null;
            try {
                uploadData = JSON.parse(uploadRaw);
            } catch {
                setError("Réponse invalide pendant l'upload de l'image: " + uploadRaw);
                return;
            }

            if (!uploadResponse.ok || !uploadData.success) {
                setError(uploadData.message || "Erreur lors de l'upload de l'image.");
                return;
            }

            imageValue = uploadData.fileName;
        } else if (!isNewConseils && currentImage) {
            imageValue = currentImage;
        } else {
            imageValue = "";
        }

        const payload = {
            titre,
            contenu,
            image: imageValue
        };

        const base = (window.API_BASE || "http://localhost:9000");
        const endpoint = isNewConseils ? "/creer_conseil" : "/modifier_conseil/" + conseilId;
        const method = isNewConseils ? "POST" : "PATCH";

        const response = await fetch(base + endpoint, {
            method,
            headers: {
                "Content-Type": "application/json",
                "Token": localStorage.getItem("token") || ""
            },
            body: JSON.stringify(payload)
        });

        const reponse_fonc = await response.text();
        let data = null;
        try {
            data = JSON.parse(reponse_fonc);
        } catch {
            setError(reponse_fonc || "Réponse invalide du serveur.");
            return;
        }

        if (!response.ok || data.value !== 1) {
            setError(data.message || "Impossible d'enregistrer le conseil.");
            return;
        }

        await fetch("ajouter_session_state.php", { method: "POST" });
        window.location.href = "gestion_conseil.php?message=" + encodeURIComponent(data.message);
    }

    function previewImage() {
        const fileInput = document.getElementById('conseil_image');
        const preview = document.getElementById('imagePreview');
        
        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 300px; max-height: 300px; border-radius: 5px;">';
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    async function search_conseils() {
        if (isNewConseils) {
            document.getElementById("page_title").innerHTML = "Créer un nouveau conseil";
            document.getElementById('admin_title').innerHTML = "Création d'un nouveau conseil";
            document.getElementById("resultat").innerHTML = `
                <style>
                    form { max-width: 600px; }
                    label { display: block; margin-top: 15px; font-weight: bold; }
                    input, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
                    textarea { min-height: 200px; resize: vertical; }
                    button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
                    button:hover { background-color: #45a049; }
                    #imagePreview { margin-top: 10px; }
                </style>
                <form onsubmit="updateConseils(event); return false;">
                    <label data-i18n>Titre :</label>
                    <input type="text" name="titre" id="conseil_titre" required><br><br>
                    <label data-i18n>Contenu :</label>
                    <textarea name="contenu" id="conseil_contenu" required></textarea><br><br>
                    <label data-i18n>Image :</label>
                    <input type="file" name="image" id="conseil_image" accept="image/*" onchange="previewImage()"><br>
                    <div id="imagePreview"></div><br>
                    <button type="submit" data-i18n>Créer le conseil</button>
                </form>
            `;
            return;
        }

        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_conseil_id/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "GET",
            headers: {"Token": localStorage.getItem('token')}
        });

        if (!response.ok) {
            const text = await response.text();
            document.getElementById("resultat").innerHTML = "<div style='color: red;'>" + text + "</div>";
            return;
        }

        const data = await response.json();

        document.getElementById("page_title").innerHTML = "Modifier le conseil " + data.titre;
        document.getElementById('admin_title').innerHTML = "Modification du conseil " + data.titre;
        currentImage = data.image;
        
        if(data.id == 0 || !data.id) {
            document.getElementById("resultat").innerHTML = "<div style='color: red;'>Aucun conseil trouvé</div>";
        } else {
            let imagePreviewHTML = '';
            if (data.image) {
                imagePreviewHTML = `<div id="imagePreview" style="margin-top: 10px;"><strong>Image actuelle :</strong><br><img src="../upload/${data.image}" style="max-width: 300px; max-height: 300px; border-radius: 5px;"></div>`;
            } else {
                imagePreviewHTML = '<div id="imagePreview"></div>';
            }
            
            document.getElementById("resultat").innerHTML = `
                <style>
                    form { max-width: 600px; }
                    label { display: block; margin-top: 15px; font-weight: bold; }
                    input, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
                    textarea { min-height: 200px; resize: vertical; }
                    button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
                    button:hover { background-color: #45a049; }
                    input[readonly] { background-color: #f0f0f0; cursor: not-allowed; }
                    #imagePreview { margin-top: 10px; }
                </style>
                <form onsubmit="updateConseils(event); return false;">
                <label>ID :</label>
                <input type="number" name="id" id="conseil_id" value="${data.id}" readonly> <span data-i18n>(Non modifiable)</span> <br><br>
                <label data-i18n>Titre :</label>
                <input type="text" name="titre" id="conseil_titre" value="${data.titre}" required><br><br>
                <label data-i18n>Contenu :</label>
                <textarea name="contenu" id="conseil_contenu" required>${data.contenu}</textarea><br><br>
                <label data-i18n>Date de publication :</label>
                <input type="text" value="${data.date}" readonly><br><br>
                <label data-i18n>Image :</label>
                <input type="file" name="image" id="conseil_image" accept="image/*" onchange="previewImage()"><br>
                ${imagePreviewHTML}
                <button type="submit" data-i18n>Confirmer les modifications</button>
                </form>
            `;
        }
    }
    window.addEventListener('DOMContentLoaded', function() {
        search_conseils();
    });
</script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title id="page_title"></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        h2 { color: #d32f2f; }
    </style>
</head>
<body>

<?php include 'includes/header.php'?>
<h1 id="admin_title"></h1>
<h2 id="admin_err"></h2>

<div id="resultat"></div>
<div id="error"></div>
    <div id="err_message" style="color: red; margin: 10px 0;"></div>
<?php include 'includes/footer.php'?>
</body>
</html>
