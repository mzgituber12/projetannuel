<?php session_start(); include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Creer un service</title>
</head>
<body>

<?php include 'includes/header.php'?>

<h1>Creer un service</h1>
<h2 id="admin_err"></h2>

<form onsubmit="createService(event)">
            <label>Nom :</label>
            <input type="text" name="nom" id="service_nom" placeholder="Nom" required><br><br>
            <label>Description :</label>
            <textarea name="description" id="service_description" placeholder="Description" required></textarea><br><br>
            <label>Tarif :</label>
            <input type="number" name="tarif" id="service_tarif" placeholder="Tarif" step="0.01" required><br><br>
            <label>Image (optionnel) :</label>
            <input type="file" name="image" id="service_image" accept="image/*" onchange="previewImage()"><br>
            <div id="imagePreview"></div><br>
            <button type="submit">Creer le service</button>
</form>

<?php include 'includes/footer.php'?>

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