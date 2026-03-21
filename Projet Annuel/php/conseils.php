<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conseils</title>
    <style>
        .conseils-shell {
            max-width: 1100px;
            margin: 1rem auto;
            padding: 1rem;
            border: 1px solid rgba(0,0,0,.1);
            border-radius: 12px;
            background: #f4f8ff;
        }

        .conseils-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .conseil-card {
            display: flex;
            flex-direction: column;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        @media (max-width: 1100px) {
            .conseils-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 800px) {
            .conseils-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .conseils-grid {
                grid-template-columns: 1fr;
            }
        }

        .conseil-image {
            height: 150px;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .conseil-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .conseil-image span {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .conseil-body {
            padding: 0.9rem;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }

        .conseil-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
        }

        .conseil-excerpt {
            margin: 0;
            color: #374151;
            line-height: 1.35;
        }

        .conseil-date {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .conseil-link {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin-top: 0.35rem;
            text-decoration: none;
            background: #2563eb;
            color: #fff;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-weight: 600;
        }

        .conseil-link:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php' ?>
<h1>Conseils</h1>
<div class="conseils-shell">
    <div id="conseil" class="conseils-grid"></div>
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
                ? `<img src="upload/${conseils.image}" alt="Image du conseil">`
                : "<span>Pas d'image</span>";
            const excerpt = conseils.contenu ? conseils.contenu : "Contenu non disponible";

            html += `
                <article class="conseil-card">
                    <div class="conseil-image">${imageHTML}</div>
                    <div class="conseil-body">
                        <h3 class="conseil-title">${conseils.titre}</h3>
                        <p class="conseil-excerpt">${excerpt}</p>
                        <span class="conseil-date">Publié le ${conseils.date}</span>
                        <a class="conseil-link" href="conseil_detail.php?id=${encodeURIComponent(conseils.id)}">Lire le conseil</a>
                    </div>
                </article>
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

