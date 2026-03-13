<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Catalogue</title>
    <style>
        .catalogue-big-group {
            border: 1px solid rgba(0,0,0,.12);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            background: #a19797;
        }

        .catalogue-big-group > h2 {
            margin-top: 0;
            margin-bottom: 0.75rem;
            font-size: 1.25rem;
        }

        .catalogue-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: flex-start;
        }
        .catalogue-title {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1rem auto;  
            justify-content: center;
            background: #6279fe;
            max-width: 250px;
            max-height: 100px;
            min-width: 220px;
            border-radius: 90%;
        }

        .catalogue-card {
            display: flex;
            flex-direction: column;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            width: 260px;
            min-height: 260px;
        }

        .catalogue-card .card-img {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 130px;
            background: linear-gradient(135deg, #eef2ff 0%, #d3e2ff 100%);
            color: #4b5563;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .catalogue-card .card-body {
            padding: 0.75rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .catalogue-card .card-title {
            font-weight: 700;
            font-size: 1.05rem;
            margin: 0;
        }

        .catalogue-card .card-desc {
            margin: 0;
            color: #444;
            line-height: 1.4;
        }

        .catalogue-card .card-meta {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .catalogue-card .card-action {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding-top: 0.5rem;
        }

        .catalogue-card button {
            padding: 0.5rem 0.9rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #2563eb;
            color: #fff;
            font-weight: 600;
        }

        .catalogue-card button:hover {
            background: #1d4ed8;
        }

        .catalogue-card button.btn-quit {
            background: #dc2626;
        }

        .catalogue-card button.btn-quit:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>

<?php include 'header/header.php' ?>

<h1> Catalogue </h1>

<?php if (isset($_SESSION['state']) && isset($_GET['message'])) { 
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}
?>
<h2 class="catalogue-title" >Evenements</h2>
<div class="catalogue-big-group">
    
    <div id="evenements" class="catalogue-group"></div>
</div>
<h2 class="catalogue-title" >Services</h2>
<div class="catalogue-big-group">
    <div id="services" class="catalogue-group"></div>
</div>
<h2 class="catalogue-title" >Articles</h2>
<div class="catalogue-big-group">
    <div id="articles" class="catalogue-group"></div>
</div>

<?php include 'footer/footer.php'; ?>

<script>
async function listCatalogue(token) {
    const base = (window.API_BASE || 'http://localhost:9000');

    const response = await fetch(base + "/evenements", {
        method: "GET",
        headers: {"Token": token}
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
    }

    const response2 = await fetch(base + "/services", {
        method: "GET",
        headers: {"Token": token}
    });

    if (!response2.ok) {
        const text = await response2.text();
        alert(text)
        window.location.href = "erreur.php?code=" + response2.status
        return
    }

    const response3 = await fetch(base + "/articles", {
        method: "GET",
    });

    if (!response3.ok) {
        const text = await response3.text();
        alert(text)
        window.location.href = "erreur.php?code=" + response3.status
        return
    }

    const evenement_list = await response.json();
    const evenement  = document.getElementById("evenements")

    if (evenement_list.message){
        evenement.innerHTML = evenement_list.message
    } else {
        let html = '';
        evenement_list.evenement.forEach(e => {
            const actionLabel = e.rejoindre === "Rejoindre" ? "Rejoindre" : "Quitter";
            const actionState = e.rejoindre === "Rejoindre" ? "join" : "leave";
            const btnClass = e.rejoindre === "Quitter" ? "btn-quit" : "";
            const action = `<button class="${btnClass}" onclick="updateUserEvent('${localStorage.getItem('token')}', 'evenements', '${actionState}', ${e.id})">${actionLabel}</button>`;

            html += `
                <div class="catalogue-card">
                    <div class="card-img">Image</div>
                    <div class="card-body">
                        <div class="card-title">${e.nom}</div>
                        <div class="card-desc">${e.description}</div>
                        <div class="card-meta">${e.date}</div>
                        <div class="card-action">${action}</div>
                    </div>
                </div>
            `;
        });
        evenement.innerHTML = html;
    }

    const service_list = await response2.json();
    const service  = document.getElementById("services")
    
    if (service_list.message){
        service.innerHTML = service_list.message
    } else {
        

        let html = '';
        service_list.service.forEach(s => {
            const actionLabel = s.rejoindre === "Rejoindre" ? "Rejoindre" : (s.rejoindre === "Quitter" ? "Quitter" : "Indisponible");
            const actionState = s.rejoindre === "Rejoindre" ? "join" : "leave";
            const btnClass = s.rejoindre === "Quitter" ? "btn-leave" : "";
            const action = s.rejoindre === "Indisponible" ?
                "<span style='opacity:.6;'>Indisponible</span>" :
                `<button class="${btnClass}" onclick="updateUserEvent('${localStorage.getItem('token')}', 'services', '${actionState}', ${s.id})">${actionLabel}</button>`;

            html += `
                <div class="catalogue-card">
                    <div class="card-img">Image</div>
                    <div class="card-body">
                        <div class="card-title">${s.nom}</div>
                        <div class="card-desc">${s.description}</div>
                        <div class="card-meta">${s.tarif} €</div>
                        <div class="card-action">${action}</div>
                    </div>
                </div>
            `;
        });
        service.innerHTML = html;
    }

    const article_list = await response3.json();
    const article  = document.getElementById("articles")

    if (article_list.message){
        article.innerHTML = article_list.message
    } else {
        let html = '';

        article_list.article.forEach(a => {
            html += `
                <div class="catalogue-card">
                    <div class="card-img">Image</div>
                    <div class="card-body">
                        <div class="card-title">${a.nom}</div>
                        <div class="card-desc">${a.description}</div>
                        <div class="card-meta">${a.prix} €</div>
                    </div>
                </div>
            `;
        });
        article.innerHTML = html;
    }
}

async function updateUserEvent(token, type, state, id) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/" + type + "/" + id, {
        method: "POST",
        headers: {"Content-Type": "application/json", "Token": token},
        body: JSON.stringify({state: state})
    });

    if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        
    const data = await response.json();
    await fetch("ajouter_session_state.php", {method: "POST"});
    const type2 = type == "evenements" ? "Evenement" : "Service"
    const state2 = state == "join" ? " rejoint" : " quitté"
    window.location.search = "?message=" + type2 + state2 + " avec succes"
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        listCatalogue(token);
    }

init()
</script>
</html>
