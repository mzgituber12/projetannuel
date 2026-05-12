<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title id="page_title"></title>
    <link rel="stylesheet" href="police.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        h2 { color: #d32f2f; }
    </style>
</head>
<body>

<?php include 'includes/header.php'?>
<div class='container mt-5'>
<h1 id="admin_title" data-i18n class='mb-custom text-center ms-4' style='font-size:50px'></h1>
<h2 id="admin_err" class="mt-4"></h2>

<div id="resultat" class="mt-custom p-3 pb-1 border rounded bg-light"></div>
</div>

<div class="mt-4"></div>
<?php include 'includes/footer.php'?>
</body>
</html>

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
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
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
                <form onsubmit="updateConseils(event); return false;">
                <label data-i18n>ID :</label>
                <input type="number" name="id" id=""conseil_id" value="${data.id}" readonly> <span data-i18n>Pas modifiable</span> <br><br>
                <label data-i18n>Titre :</label>
                <input type="text" name="nom" id="conseil_titre" value="${data.titre}" required><br><br>
                <div class="mb-3">
                <label for="conseil_contenu" class="form-label" data-i18n>Contenu :</label>
                <textarea class="form-control" id="conseil_contenu" rows="4" required>${data.contenu}</textarea>
                </div>
                <label data-i18n>Date de publication :</label>
                <input type="text" value="${data.date}" readonly><br><br>
                <label data-i18n>Image :</label>
                <input type="file" name="image" id="conseil_image" accept="image/*" onchange="previewImage()"><br>
                ${imagePreviewHTML}
                <br>
                <button type="submit" class="btn btn-primary w-100">
                Confirmer les modifications
                </button>
                </form>
            `;
        }
    }
    async function init(){
        const token = localStorage.getItem("token")
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        search_conseils()
    }

    window.addEventListener('pageshow', function(event) {
if (event.persisted) {
    window.location.reload();
}
});
init()
</script>
