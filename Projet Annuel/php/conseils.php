<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Conseils</title>
    <style>
        .mb-custom{
            margin-bottom: 2.3rem
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php' ?>

<div class="container mt-5 mb-4">
<h1 class="text-center mb-4" style="font-size:50px" data-i18n>Conseils</h1>
    <p class="text-center mb-custom" data-i18n>Découvrez une sélection de conseils proposés par nos collaborateurs issus de différents domaines.  
    <br>Évaluez-les sur 5 selon leur intérêt et leur pertinence, et explorez ceux qui vous semblent les plus utiles.</p>
<div class="container-lg">
    <div id="conseil" class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3"></div>
</div>
</div>

<?php include 'includes/footer.php';?>

<script>
    function modifImageUrl(image) {
    const contenue = String(image ?? "").trim();
    if (!contenue) return "";
    if (contenue.startsWith("http://") || contenue.startsWith("https://") || contenue.startsWith("/")) {
        return contenue;
    }
    return `upload/${encodeURIComponent(contenue)}`;
}

function renduBoutiqueImage(image) {
    const imageUrl = modifImageUrl(image);

    return `<img 
                src="${imageUrl || 'noimage.avif'}" 
                alt="Image du conseil" 
                style="width:100%;height:100%;object-fit:cover;"
            >`;
}

async function listconseils(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/conseils", {
        method: "GET",
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
    
    const data = await response.json();
    const tab_conseil = document.getElementById("conseil")
    if (data.message){
        tab_conseil.innerHTML = "<p>" + data.message + "</p>"
    } else {
        let html = "";
        data.conseil.forEach(conseils => {
            const rawContenu = conseils.contenu || "";
            const excerpt = rawContenu
                ? (rawContenu.length > 30 ? rawContenu.substring(0, 30) + "..." : rawContenu)
                : "Contenu non disponible";

            html += `
                <div class="col">
                    <article class="card h-100 border-0 shadow-sm">
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:230px;">
                            ${renduBoutiqueImage(conseils.image)}
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${conseils.titre}</h5>
                            <p class="card-text text-muted small flex-grow-1">${excerpt}</p>
                            <p class="card-text text-muted small mb-2">Publié le ${conseils.date}</p>
                            <a class="btn btn-primary" href="conseil_detail.php?id=${encodeURIComponent(conseils.id)}" data-i18n>Lire le conseil</a>
                        </div>
                    </article>
                </div>
            `;
        });
        tab_conseil.innerHTML = html;
    }
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        listconseils(token);
    }

init()
</script>
</body>
</html>

