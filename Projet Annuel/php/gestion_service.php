<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Gestion des services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class="container-fluid mt-4">
    <h1 class="mb-4" data-i18n>Gestion des services</h1>
    <?php
    if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_GET['message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['state']);
    }?>
    <div class="mb-4">
        <a href='creer_service.php' class='btn btn-primary'><i class="bi bi-plus-circle"></i> <span data-i18n>Créer un nouveau service</span></a>
    </div>

    <div class="card p-3 shadow-sm mb-4">
        <h5 class="mb-3" data-i18n>Recherche dans la liste des services</h5>
        <div class="d-flex gap-2 flex-wrap align-items-end">
            <div class="flex-grow-1" style="min-width: 200px;">
                <label for="searchInput" class="form-label small" data-i18n>Recherche</label>
                <input id="searchInput" type="text" class="form-control form-control-sm" placeholder="Nom, description, catégorie...">
            </div>
            <div style="min-width: 150px;">
                <label for="categoryFilter" class="form-label small" data-i18n>Catégorie</label>
                <select id="categoryFilter" class="form-select form-select-sm">
                    <option value="" data-i18n>Toutes</option>
                </select>
            </div>
            <div style="min-width: 130px;">
                <label for="minPriceFilter" class="form-label small" data-i18n>Tarif min</label>
                <input id="minPriceFilter" type="number" class="form-control form-control-sm" min="0" step="0.01" placeholder="0">
            </div>
            <div style="min-width: 130px;">
                <label for="maxPriceFilter" class="form-label small" data-i18n>Tarif max</label>
                <input id="maxPriceFilter" type="number" class="form-control form-control-sm" min="0" step="0.01" placeholder="500">
            </div>
            <button type="button" id="resetFiltersButton" class="btn btn-sm btn-dark" data-i18n>Réinitialiser</button>
        </div>
    </div>

    <h2 class="mt-5 mb-3" data-i18n>Liste des services</h2>
    <div id="services"></div>
</div>
<?php include("includes/footer.php") ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
    let servicesData = [];

    function parsePrice(value) {
        if (value === "" || value == null || value === undefined) return null;
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : null;
    }

    function renderImageHtml(image, altText) {
        const file = String(image ?? "").trim();
        if (!file) return "<em>Pas d'image</em>";
        const src = file.startsWith("http://") || file.startsWith("https://") || file.startsWith("/")
            ? file
            : `upload/${encodeURIComponent(file)}`;
        return `<img src="${src}" alt="${String(altText)}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">`;
    }

    async function supprimer_service(id, nom){
        const confirmation = confirm("Êtes-vous sûr de vouloir supprimer le service " + nom + " ?");
        if (!confirmation){
            return;
        } else {
            const base = (window.API_BASE || 'http://localhost:9000');
            const response = await fetch(base + "/supprimer_service/" + id, {
                method: "DELETE",
            });
            if (!response.ok){
                const text = await response.text();
                alert(text)
                window.location.href = "erreur.php?code=" + response.status
                return;
            }
            await fetch("ajouter_session_state.php", {method: "POST"});
            window.location.href = window.location.pathname + "?message=Service " + nom + " supprimé avec succes" ;
            }
    }

    function applyServiceFilters() {
        const search = document.getElementById("searchInput").value.trim().toLowerCase();
        const categoryFilter = document.getElementById("categoryFilter").value;
        const minPrice = parsePrice(document.getElementById("minPriceFilter").value);
        const maxPrice = parsePrice(document.getElementById("maxPriceFilter").value);

        const matchPrice = (value) => {
            if (minPrice != null && value < minPrice) return false;
            if (maxPrice != null && value > maxPrice) return false;
            return true;
        };

        const filtered = servicesData.filter((s) => {
            if (categoryFilter && String(s.id_categorie) !== String(categoryFilter) && String(s.categorie || "") !== String(categoryFilter)) {
                return false;
            }
            if (!matchPrice(Number(s.tarif))) return false;
            const serviceText = (s.nom + " " + (s.description || "") + " " + (s.categorie || "")).toLowerCase();
            if (search && !serviceText.includes(search)) return false;
            return true;
        });

        renderServicesTable(filtered);
    }

    function renderServicesTable(list) {
        const container = document.getElementById("services");
        if (!list.length) {
            container.innerHTML = "<div class='alert alert-warning' data-i18n>Aucun service ne correspond aux filtres.</div>";
            return;
        }
        let html = "<div class='table-responsive'><table class='table table-hover'><thead class='table-success'><tr><th data-i18n>Image</th><th data-i18n>Nom du service</th><th data-i18n>Catégorie</th><th data-i18n>Description</th><th data-i18n>Tarif</th><th data-i18n>Actions</th></tr></thead><tbody>";
        list.forEach((serv) => {
            const actions = "<div class='d-flex flex-wrap gap-2 align-items-center'>" +
                "<a href='modifier_service.php?id=" + serv.id + "' class='btn btn-sm btn-warning' data-i18n>Modifier</a>" +
                "<a href='#' onclick='supprimer_service(" + serv.id + ", " + JSON.stringify(serv.nom) + "); return false;' class='btn btn-sm btn-danger' data-i18n>Supprimer</a></div>";
            const imageHtml = renderImageHtml(serv.image, `Image de ${serv.nom}`);
            const desc = (serv.description || '').length > 40 ? String(serv.description).slice(0, 40) + "..." : String(serv.description);
            const catLabel = serv.categorie ? String(serv.categorie) : "<span class='text-muted'>—</span>";
            html += "<tr><td>" + imageHtml + "</td><td>" + String(serv.nom) + "</td><td>" + catLabel + "</td><td>" + desc + "</td><td>" + String(serv.tarif) + " €</td><td>" + actions + "</td></tr>";
        });
        html += "</tbody></table></div>";
        container.innerHTML = html;
    }

    async function loadCategoriesForFilters() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const categorySelect = document.getElementById("categoryFilter");
        try {
            const response = await fetch(base + "/categories", { method: "GET" });
            if (!response.ok) return;
            const payload = await response.json();
            if (!payload.categorie || !Array.isArray(payload.categorie)) return;
            while (categorySelect.options.length > 1) {
                categorySelect.remove(1);
            }
            payload.categorie.forEach((c) => {
                const option = document.createElement("option");
                option.value = String(c.id);
                option.textContent = c.nom;
                categorySelect.appendChild(option);
            });
        } catch (e) {}
    }

    function setupServiceFilterListeners() {
        ["searchInput", "categoryFilter", "minPriceFilter", "maxPriceFilter"].forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener("input", applyServiceFilters);
            el.addEventListener("change", applyServiceFilters);
        });
        const resetBtn = document.getElementById("resetFiltersButton");
        if (resetBtn) {
            resetBtn.addEventListener("click", () => {
                document.getElementById("searchInput").value = "";
                document.getElementById("categoryFilter").value = "";
                document.getElementById("minPriceFilter").value = "";
                document.getElementById("maxPriceFilter").value = "";
                applyServiceFilters();
            });
        }
    }

    async function listService(token) {
        const base = (window.API_BASE || 'http://localhost:9000');

        const response = await fetch(base + "/list_services", {
            method: "GET",
            headers: {"Token": token}
        });

        if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }
        const service_list = await response.json();
        const container = document.getElementById("services");

        if (service_list.message) {
            servicesData = [];
            container.innerHTML = "<div class='alert alert-info'>" + service_list.message + "</div>";
        } else {
            servicesData = service_list.service || [];
            applyServiceFilters();
        }
    }

    async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        setupServiceFilterListeners();
        await loadCategoriesForFilters();
        await listService(token);
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

