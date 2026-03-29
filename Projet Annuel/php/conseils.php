<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conseils</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

<?php include 'includes/header.php' ?>
<h1 class="mt-5 mb-4">Conseils</h1>
<div class="container-lg">
    <div id="conseil" class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4"></div>
</div>

<?php include 'includes/footer.php';?>

<script>
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
            const imageHTML = conseils.image
                ? `<img src="upload/${conseils.image}" alt="Image du conseil" class="card-img-top h-auto" style="height:150px;object-fit:cover;">`
                : `<div class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted" style="height:150px;">Pas d'image</div>`;
            const excerpt = conseils.contenu ? conseils.contenu : "Contenu non disponible";

            html += `
                <div class="col">
                    <article class="card h-100 border-0 shadow-sm">
                        ${imageHTML}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${conseils.titre}</h5>
                            <p class="card-text text-muted small flex-grow-1">${excerpt}</p>
                            <p class="card-text text-muted small mb-2">Publié le ${conseils.date}</p>
                            <a class="btn btn-primary" href="conseil_detail.php?id=${encodeURIComponent(conseils.id)}">Lire le conseil</a>
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

