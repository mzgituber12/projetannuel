<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Catalogue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        .catalogue-carousel .carousel-item {
            padding: 1rem 3rem;
        }

        .catalogue-carousel .carousel-control-prev,
        .catalogue-carousel .carousel-control-next {
            width: 3rem;
        }

        .catalogue-carousel .carousel-control-prev-icon,
        .catalogue-carousel .carousel-control-next-icon {
            background-color: rgba(33, 37, 41, 0.8);
            border-radius: 50%;
            background-size: 60% 60%;
        }

        @media (max-width: 767.98px) {
            .catalogue-carousel .carousel-item {
                padding: 0.75rem 2.25rem;
            }

            .catalogue-carousel .carousel-control-prev,
            .catalogue-carousel .carousel-control-next {
                width: 2.25rem;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php' ?>

<h1 data-i18n> Catalogue </h1>

<?php if (isset($_SESSION['state']) && isset($_GET['message'])) { 
    echo "<h2>" . htmlspecialchars($_GET['message']) . "</h2>";
    unset($_SESSION['state']);
}
?>
<div class="card p-3 shadow-sm">
    <h5 class="mb-3" data-i18n>Recherche rapide dans le catalogue</h5>
    <div class="d-flex gap-2 flex-wrap align-items-end">
        <div class="flex-grow-1" style="min-width: 200px;">
            <label for="searchInput" class="form-label small" data-i18n>Recherche</label>
            <input id="searchInput" type="text" class="form-control form-control-sm" placeholder="Nom, description, type...">
        </div>
        <div style="min-width: 150px;">
            <label for="searchType" class="form-label small" data-i18n>Type</label>
            <select id="searchType" class="form-select form-select-sm">
                <option value="all" data-i18n>Tout</option>
                <option value="service" data-i18n>Service</option>
                <option value="evenement" data-i18n>Evenement</option>
                <option value="prestataire" data-i18n>Prestataire</option>
                <option value="article" data-i18n>Article</option>
            </select>
        </div>
        <div style="min-width: 150px;">
            <label for="categoryFilter" class="form-label small" data-i18n>Categorie</label>
            <select id="categoryFilter" class="form-select form-select-sm">
                <option value="" data-i18n>Toutes</option>
            </select>
        </div>
        <div style="min-width: 130px;">
            <label for="minPriceFilter" class="form-label small" data-i18n>Prix min</label>
            <input id="minPriceFilter" type="number" class="form-control form-control-sm" min="0" step="0.01" placeholder="0">
        </div>
        <div style="min-width: 130px;">
            <label for="maxPriceFilter" class="form-label small" data-i18n>Prix max</label>
            <input id="maxPriceFilter" type="number" class="form-control form-control-sm" min="0" step="0.01" placeholder="500">
        </div>
        <button id="resetFiltersButton" class="btn btn-sm btn-dark" data-i18n>Réinitialiser</button>
    </div>
</div>
<h2 class="h4 mt-5 mb-3" data-i18n>Événements</h2>
<div class="card border-0 mb-4">
    <div id="evenements" class="carousel slide catalogue-carousel" data-bs-interval="false"></div>
</div>
<h2 class="h4 mt-5 mb-3" data-i18n>Services</h2>
<div class="card border-0 mb-4">
    <div id="services" class="carousel slide catalogue-carousel" data-bs-interval="false"></div>
</div>
<h2 class="h4 mt-5 mb-3" data-i18n>Articles</h2>
<div class="card border-0 mb-4">
    <div id="articles" class="carousel slide catalogue-carousel" data-bs-interval="false"></div>
</div>
<h2 class="h4 mt-5 mb-3" data-i18n>Prestataires</h2>
<div class="card border-0 mb-4">
    <div id="prestataires" class="carousel slide catalogue-carousel" data-bs-interval="false"></div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
let evenementsData = [];
let servicesData = [];
let articlesData = [];
let prestatairesData = [];

function cardsPerSlide() {
    if (window.innerWidth < 768) return 1;
    if (window.innerWidth < 992) return 2;
    return 3;
}

function chunkArray(items, size) {
    const chunks = [];
    for (let i = 0; i < items.length; i += size) {
        chunks.push(items.slice(i, i + size));
    }
    return chunks;
}

function renderBootstrapCarousel(containerId, cardsHtml, emptyMessage) {
    const root = document.getElementById(containerId);
    if (!root) return;

    if (!cardsHtml.length) {
        root.innerHTML = `<div class="p-3"><p class="text-muted mb-0">${emptyMessage}</p></div>`;
        return;
    }

    const groups = chunkArray(cardsHtml, cardsPerSlide());
    const inner = groups.map((group, index) => `
        <div class="carousel-item ${index === 0 ? "active" : ""}">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                ${group.join("")}
            </div>
        </div>
    `).join("");

    const controls = groups.length > 1 ? `
        <button class="carousel-control-prev" type="button" data-bs-target="#${containerId}" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden" data-i18n>Précédent</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#${containerId}" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden" data-i18n>Suivant</span>
        </button>
    ` : "";

    root.innerHTML = `
        <div class="carousel-inner">${inner}</div>
        ${controls}
    `;

    if (window.bootstrap && window.bootstrap.Carousel) {
        window.bootstrap.Carousel.getOrCreateInstance(root, { interval: false, ride: false, touch: true });
    }
}

function resolveImageUrl(image) {
    const raw = String(image ?? "").trim();
    if (!raw) return "";
    if (raw.startsWith("http://") || raw.startsWith("https://") || raw.startsWith("/")) {
        return raw;
    }
    return `upload/${encodeURIComponent(raw)}`;
}

function renderCardImage(image, altText) {
    const imageUrl = resolveImageUrl(image);
    if (!imageUrl) return "Image";
    return `<img src="${imageUrl}" alt="${String(altText)}" style="width:100%;height:100%;object-fit:cover;">`;
}

function parsePrice(value) {
    if (value === "" || value == null || value == undefined) return null;
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
}

function renderEvenements(items) {
    let cards = [];
    items.forEach(e => {
        const actionLabel = e.rejoindre == "Rejoindre" ? "Rejoindre" : "Quitter";
        const joinLink = `${window.location.origin}/reservation.php?type=evenement&id=${encodeURIComponent(e.id)}&nom=${encodeURIComponent(e.nom)}&date=${encodeURIComponent(e.date)}&description=${encodeURIComponent(e.description)}&tarif=${encodeURIComponent(e.tarif)}&image=${encodeURIComponent(e.image || "")}`;
        const btnClass = e.rejoindre == "Quitter" ? "btn-outline-danger" : "btn-primary";
        const action = e.rejoindre == "Rejoindre" ?
            `<a class="btn btn-primary w-100" href="${joinLink}">${actionLabel}</a>` :
            `<button class="btn ${btnClass} w-100" onclick="updateUserEvent('${localStorage.getItem('token')}', 'evenements', 'leave', ${e.id})">${actionLabel}</button>`;

        cards.push(`
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:140px;">
                        ${renderCardImage(e.image, `Image de ${e.nom || "cet evenement"}`)}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${String(e.nom)}</h6>
                        <p class="card-text text-muted small flex-grow-1">${String(e.description)}</p>
                        <p class="card-text small text-secondary mb-2">${String(e.date)}</p>
                        ${action}
                    </div>
                </div>
            </div>
        `);
    });
    renderBootstrapCarousel("evenements", cards, "Aucun événement ne correspond aux filtres.");
}

function renderServices(items) {
    let cards = [];
    items.forEach(s => {
        const reserveLink = `${window.location.origin}/reservation.php?type=service&id=${encodeURIComponent(s.id)}&nom=${encodeURIComponent(s.nom)}&description=${encodeURIComponent(s.description || "")}&tarif=${encodeURIComponent(s.tarif)}&image=${encodeURIComponent(s.image || "")}`;
        const quoteLink = `${window.location.origin}/demande_devis_service.php?id=${encodeURIComponent(s.id)}&nom=${encodeURIComponent(s.nom)}&description=${encodeURIComponent(s.description || "")}&tarif=${encodeURIComponent(s.tarif)}&image=${encodeURIComponent(s.image || "")}`;
        let action = `<div class="d-grid gap-2">` +
            `<a class="btn btn-primary w-100" href="${reserveLink}">Réserver</a>` +
            `<a class="btn btn-outline-primary w-100" href="${quoteLink}">Demander un devis</a>`;

        if (s.rejoindre == "Quitter") {
            action += `<button class="btn btn-outline-danger w-100" onclick="updateUserEvent('${localStorage.getItem('token')}', 'services', 'leave', ${s.id})">Annuler</button>`;
        }

        action += `</div>`;

        const categorieText = s.categorie ? `${String(s.categorie)}` : "Non renseignée";
        const prestataireText = s.prestataire ? `${String(s.prestataire)}` : "Non renseigné";

        cards.push(`
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:140px;">
                        ${renderCardImage(s.image, `Image de ${s.nom || "ce service"}`)}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${String(s.nom)}</h6>
                        <p class="card-text text-muted small flex-grow-1">${String(s.description)}</p>
                        <p class="card-text small text-secondary mb-1"><strong>Catégorie:</strong> ${categorieText}</p>
                        <p class="card-text small text-secondary mb-1"><strong>Prestataire:</strong> ${prestataireText}</p>
                        <p class="card-text small text-primary fw-bold mb-2">${String(s.tarif)} €</p>
                        ${action}
                    </div>
                </div>
            </div>
        `);
    });
    renderBootstrapCarousel("services", cards, "Aucun service ne correspond aux filtres.");
}

function renderArticles(items) {
    let cards = [];
    items.forEach(a => {
        cards.push(`
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:140px;">
                        ${renderCardImage(a.image, `Image de ${a.titre || "cet article"}`)}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${String(a.titre)}</h6>
                        <p class="card-text text-muted small flex-grow-1">${String(a.description)}</p>
                        <p class="card-text small text-primary fw-bold">${String(a.prix)} €</p>
                    </div>
                </div>
            </div>
        `);
    });
    renderBootstrapCarousel("articles", cards, "Aucun article ne correspond aux filtres.");
}

function renderPrestataires(items) {
    let cards = [];
    items.forEach(p => {
        const fullName = `${p.prenom || ""} ${p.nom || ""}`.trim() || "Prestataire";
        cards.push(`
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="bg-light d-flex align-items-center justify-content-center text-center p-3" style="height:140px;">
                        <div class="text-muted small">Prestataire</div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${String(fullName)}</h6>
                        <p class="card-text small text-secondary mb-1"><strong>Type:</strong> ${String(p.type || "Non renseigné")}</p>
                        <p class="card-text small text-secondary"><strong>Téléphone:</strong> ${String(p.telephone || "Non renseigné")}</p>
                    </div>
                </div>
            </div>
        `);
    });
    renderBootstrapCarousel("prestataires", cards, "Aucun prestataire ne correspond aux filtres.");
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
        const text = `${a.titre} ${a.description}`.toLowerCase();
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
    let resizeTimeout;
    window.addEventListener("resize", () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => applyFilters(), 150);
    });
    setupFilters();
    listCatalogue(token);
    }

init()
</script>
</html>







