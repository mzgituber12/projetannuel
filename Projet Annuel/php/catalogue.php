<?php session_start(); include 'includes/api_config.php'; ?>
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

        .button-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.5rem 0.9rem;
            border-radius: 4px;
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }

        .button-link:hover {
            background: #1d4ed8;
        }

        .catalogue-card button.btn-leave {
            background: #dc2626;
        }

        .catalogue-card button.btn-leave:hover {
            background: #b91c1c;
        }

        .catalogue-search-shell {
            max-width: 1100px;
            margin: 1rem auto 1.5rem auto;
            background: linear-gradient(90deg, #dde9ff 0%, #f4f8ff 45%, #e9f0ff 100%);
            border: 1px solid #c7d2fe;
            border-radius: 14px;
            padding: 1rem;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.12);
        }

        .catalogue-search-shell h3 {
            margin: 0 0 0.75rem 0;
            font-size: 1rem;
            color: #1e3a8a;
            letter-spacing: 0.2px;
        }

        .catalogue-filters {
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.85);
            padding: 0.9rem;
            display: flex;
            align-items: flex-end;
            gap: 0.75rem;
            overflow-x: auto;
        }

        .catalogue-filter-grid {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.75rem;
            align-items: end;
            min-width: max-content;
            flex: 1;
        }

        .catalogue-filter-field {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            min-width: 180px;
        }

        .catalogue-filter-field label {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .catalogue-filter-field input,
        .catalogue-filter-field select {
            padding: 0.55rem 0.65rem;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 0.95rem;
        }

        .catalogue-filter-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 0;
            min-width: max-content;
        }

        .catalogue-filter-actions button {
            border: none;
            border-radius: 6px;
            background: #111827;
            color: #fff;
            padding: 0.55rem 0.85rem;
            cursor: pointer;
        }

        .catalogue-filter-actions button:hover {
            background: #1f2937;
        }

        .catalogue-empty {
            color: #374151;
            font-style: italic;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php' ?>

<h1> Catalogue </h1>

<?php if (isset($_SESSION['state']) && isset($_GET['message'])) { 
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}
?>
<div class="catalogue-search-shell">
    <h3>Recherche rapide dans le catalogue</h3>
    <div class="catalogue-filters">
        <div class="catalogue-filter-grid">
            <div class="catalogue-filter-field">
                <label for="searchInput">Recherche</label>
                <input id="searchInput" type="text" placeholder="Nom, description, type, telephone...">
            </div>
            <div class="catalogue-filter-field">
                <label for="searchType">Type de recherche</label>
                <select id="searchType">
                    <option value="all">Tout</option>
                    <option value="service">Service</option>
                    <option value="evenement">Evenement</option>
                    <option value="prestataire">Prestataire</option>
                    <option value="article">Article</option>
                </select>
            </div>
            <div class="catalogue-filter-field">
                <label for="categoryFilter">Categorie (services)</label>
                <select id="categoryFilter">
                    <option value="">Toutes</option>
                </select>
            </div>
            <div class="catalogue-filter-field">
                <label for="minPriceFilter">Prix minimum</label>
                <input id="minPriceFilter" type="number" min="0" step="0.01" placeholder="0">
            </div>
            <div class="catalogue-filter-field">
                <label for="maxPriceFilter">Prix maximum</label>
                <input id="maxPriceFilter" type="number" min="0" step="0.01" placeholder="500">
            </div>
        </div>
        <div class="catalogue-filter-actions">
            <button id="resetFiltersButton" type="button">Reinitialiser les filtres</button>
        </div>
    </div>
</div>
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
<h2 class="catalogue-title" >Prestataires</h2>
<div class="catalogue-big-group">
    <div id="prestataires" class="catalogue-group"></div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let evenementsData = [];
let servicesData = [];
let articlesData = [];
let prestatairesData = [];

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

function parsePrice(value) {
    if (value === "" || value == null || value == undefined) return null;
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
}

function renderEvenements(items) {
    const evenement = document.getElementById("evenements");
    if (!items.length) {
        evenement.innerHTML = '<p class="catalogue-empty">Aucun evenement ne correspond aux filtres.</p>';
        return;
    }

    let html = '';
    items.forEach(e => {
        const actionLabel = e.rejoindre == "Rejoindre" ? "Rejoindre" : "Quitter";
        const joinLink = `${window.location.origin}/reservation.php?type=evenement&id=${encodeURIComponent(e.id)}&nom=${encodeURIComponent(e.nom)}&date=${encodeURIComponent(e.date)}&description=${encodeURIComponent(e.description)}&tarif=${encodeURIComponent(e.tarif)}`;
        const btnClass = e.rejoindre == "Quitter" ? "btn-leave" : "";
        const action = e.rejoindre == "Rejoindre" ?
            `<a class="button-link" href="${joinLink}">${actionLabel}</a>` :
            `<button class="${btnClass}" onclick="updateUserEvent('${localStorage.getItem('token')}', 'evenements', 'leave', ${e.id})">${actionLabel}</button>`;

        html += `
            <div class="catalogue-card">
                <div class="card-img">Image</div>
                <div class="card-body">
                    <div class="card-title">${escapeHtml(e.nom)}</div>
                    <div class="card-desc">${escapeHtml(e.description)}</div>
                    <div class="card-meta">${escapeHtml(e.date)}</div>
                    <div class="card-action">${action}</div>
                </div>
            </div>
        `;
    });
    evenement.innerHTML = html;
}

function renderServices(items) {
    const service = document.getElementById("services");
    if (!items.length) {
        service.innerHTML = '<p class="catalogue-empty">Aucun service ne correspond aux filtres.</p>';
        return;
    }

    let html = '';
    items.forEach(s => {
        const actionLabel = s.rejoindre == "Rejoindre" ? "Reserver" : (s.rejoindre == "Quitter" ? "Annuler" : "Indisponible");
        const joinLink = `${window.location.origin}/reservation.php?type=service&id=${encodeURIComponent(s.id)}&nom=${encodeURIComponent(s.nom)}&tarif=${encodeURIComponent(s.tarif)}`;
        const btnClass = s.rejoindre == "Quitter" ? "btn-leave" : "";
        const actionState = s.rejoindre == "Quitter" ? "leave" : "join";
        const action = s.rejoindre == "Rejoindre" ?
            `<a class="button-link" href="${joinLink}">${actionLabel}</a>` :
            `<button class="${btnClass}" onclick="updateUserEvent('${localStorage.getItem('token')}', 'services', '${actionState}', ${s.id})">${actionLabel}</button>`;

        const categorieText = s.categorie ? `Categorie : ${escapeHtml(s.categorie)}` : "Categorie : non renseignee";
        const prestataireText = s.prestataire ? `Prestataire : ${escapeHtml(s.prestataire)}` : "Prestataire : non renseigne";

        html += `
            <div class="catalogue-card">
                <div class="card-img">Image</div>
                <div class="card-body">
                    <div class="card-title">${escapeHtml(s.nom)}</div>
                    <div class="card-desc">${escapeHtml(s.description)}</div>
                    <div class="card-meta">${categorieText}</div>
                    <div class="card-meta">${prestataireText}</div>
                    <div class="card-meta">${escapeHtml(s.tarif)} €</div>
                    <div class="card-action">${action}</div>
                </div>
            </div>
        `;
    });
    service.innerHTML = html;
}

function renderArticles(items) {
    const article = document.getElementById("articles");
    if (!items.length) {
        article.innerHTML = '<p class="catalogue-empty">Aucun article ne correspond aux filtres.</p>';
        return;
    }

    let html = '';
    items.forEach(a => {
        html += `
            <div class="catalogue-card">
                <div class="card-img">Image</div>
                <div class="card-body">
                    <div class="card-title">${escapeHtml(a.nom)}</div>
                    <div class="card-desc">${escapeHtml(a.description)}</div>
                    <div class="card-meta">${escapeHtml(a.prix)} €</div>
                </div>
            </div>
        `;
    });
    article.innerHTML = html;
}

function renderPrestataires(items) {
    const prestataire = document.getElementById("prestataires");
    if (!items.length) {
        prestataire.innerHTML = '<p class="catalogue-empty">Aucun prestataire ne correspond aux filtres.</p>';
        return;
    }

    let html = '';
    items.forEach(p => {
        const fullName = `${p.prenom || ""} ${p.nom || ""}`.trim() || "Prestataire";
        html += `
            <div class="catalogue-card">
                <div class="card-img">Prestataire</div>
                <div class="card-body">
                    <div class="card-title">${escapeHtml(fullName)}</div>
                    <div class="card-meta">Type : ${escapeHtml(p.type || "non renseigne")}</div>
                    <div class="card-meta">Telephone : ${escapeHtml(p.telephone || "non renseigne")}</div>
                </div>
            </div>
        `;
    });
    prestataire.innerHTML = html;
}

function applyFilters() {
    const search = document.getElementById("searchInput").value.trim().toLowerCase();
    const searchType = document.getElementById("searchType").value;
    const categoryFilter = document.getElementById("categoryFilter").value;
    const minPrice = parsePrice(document.getElementById("minPriceFilter").value);
    const maxPrice = parsePrice(document.getElementById("maxPriceFilter").value);

    const matchPrice = (value) => {
        if (minPrice != null && value < minPrice) return false;
        if (maxPrice != null && value > maxPrice) return false;
        return true;
    };

    const filteredEvenements = evenementsData.filter((e) => {
        if (searchType == "service" || searchType == "prestataire" || searchType == "article") return false;
        const text = `${e.nom} ${e.description} ${e.date}`.toLowerCase();
        if (search && !text.includes(search)) return false;
        return matchPrice(Number(e.tarif));
    });

    const filteredServices = servicesData.filter((s) => {
        if (searchType === "evenement" || searchType == "prestataire" || searchType == "article") return false;

        if (categoryFilter && String(s.id_categorie) != String(categoryFilter) && (s.categorie || "") != categoryFilter) {
            return false;
        }

        if (!matchPrice(Number(s.tarif))) return false;

        const serviceText = `${s.nom} ${s.description} ${s.categorie || ""}`.toLowerCase();

        if (search) {
            if (searchType == "service") return serviceText.includes(search);
            return serviceText.includes(search);
        }

        return true;
    });

    const filteredArticles = articlesData.filter((a) => {
        if (searchType != "all" && searchType != "article") return false;
        if (!search) return true;
        const text = `${a.nom} ${a.description}`.toLowerCase();
        return text.includes(search);
    });

    const filteredPrestataires = prestatairesData.filter((p) => {
        if (searchType !== "all" && searchType !== "prestataire") return false;
        if (!search) return true;
        const text = `${p.nom || ""} ${p.prenom || ""} ${p.type || ""} ${p.telephone || ""}`.toLowerCase();
        return text.includes(search);
    });

    renderEvenements(filteredEvenements);
    renderServices(filteredServices);
    renderArticles(filteredArticles);
    renderPrestataires(filteredPrestataires);
}

function setupFilters() {
    ["searchInput", "searchType", "categoryFilter", "minPriceFilter", "maxPriceFilter"].forEach((id) => {
        document.getElementById(id).addEventListener("input", applyFilters);
        document.getElementById(id).addEventListener("change", applyFilters);
    });

    document.getElementById("resetFiltersButton").addEventListener("click", () => {
        document.getElementById("searchInput").value = "";
        document.getElementById("searchType").value = "all";
        document.getElementById("categoryFilter").value = "";
        document.getElementById("minPriceFilter").value = "";
        document.getElementById("maxPriceFilter").value = "";
        applyFilters();
    });
}

async function loadCategories(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const categorySelect = document.getElementById("categoryFilter");

    try {
        const response = await fetch(base + "/categories", {
            method: "GET"
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        if (!payload.categorie || !Array.isArray(payload.categorie)) {
            return;
        }

        payload.categorie.forEach((c) => {
            const option = document.createElement("option");
            option.value = String(c.id);
            option.textContent = c.nom;
            categorySelect.appendChild(option);
        });
    } catch (_) {
    }
}

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

    const response4 = await fetch(base + "/prestataires", {
        method: "GET",
    });

    if (!response4.ok) {
        const text = await response4.text();
        alert(text)
        window.location.href = "erreur.php?code=" + response4.status
        return
    }

    function extractList(payload, key) {
    if (payload.message) {
        return [];
    }

    const value = payload[key];
    if (Array.isArray(value)) {
        return value;
    }
    return [];
}

    const evenementList = await response.json();
    evenementsData = extractList(evenementList, "evenement");

    const serviceList = await response2.json();
    servicesData = extractList(serviceList, "service");

    const articleList = await response3.json();
    articlesData = extractList(articleList, "article");

    const prestataireList = await response4.json();
    prestatairesData = extractList(prestataireList, "prestataire");
    await loadCategories(token);
    applyFilters();
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
    setupFilters();
    listCatalogue(token);
    }

init()
</script>
</html>






