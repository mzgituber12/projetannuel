<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Creer un article</title>
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'?>

<h1 data-i18n>Creer un article</h1>
<h2 id = "admin_err"></h2>

<form onsubmit="createArticle(event)">
            <label data-i18n>Titre</label>
            <input type="text" name="titre" id="article_titre" placeholder="Titre" required><br><br>
            <label data-i18n>Description :</label>
            <input type="text" name="description" id="article_description" placeholder="Description" required><br><br>
            <label data-i18n>Tarif :</label>
            <input type="number" name="prix" id="article_prix" placeholder="Prix" step="0.01" required><br><br>
            <label data-i18n>Image (optionnel) :</label>
            <input type="file" name="image" id="article_image" accept="image/*" onchange="previewImage()"><br>
            <div id="imagePreview"></div><br>
            <button type = "submit" data-i18n>Creer l'article</button>
</form>

<?php include 'includes/footer.php'?>

<script>
    function previewImage() {
        const fileInput = document.getElementById('article_image');
        const preview = document.getElementById('imagePreview');
        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 300px; max-height: 300px; border-radius: 5px;">';
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    async function createArticle(event) {
        event.preventDefault();

        let imageValue = "";
        const imageInput = document.getElementById('article_image');
        if (imageInput.files && imageInput.files.length > 0) {
            const uploadFormData = new FormData();
            uploadFormData.append("file", imageInput.files[0]);
            uploadFormData.append("uploadType", "article");

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
        const response = await fetch(base + "/creer_article", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                titre: document.getElementById('article_titre').value,
                image: imageValue,
                description: document.getElementById('article_description').value,
                prix: parseFloat(document.getElementById('article_prix').value),
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
            window.location.href = "gestion_article.php?message=" + data.message;
        } else {
            document.getElementById("admin_err").innerHTML = data.message;
        }
    }

    async function init(){
        const token = localStorage.getItem("token")
        if (!await loginUser("online", token)) return
        adminUser(token)
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