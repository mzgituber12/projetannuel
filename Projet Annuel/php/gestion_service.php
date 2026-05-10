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
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'?>

<div class='container mt-5'>
    <h1 data-i18n class='mb-custom text-center ms-4' style='font-size:50px'>Gestion des services</h1>
    <?php
    if (isset($_SESSION['state']) && isset($_GET['message'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_GET['message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['state']);
    }?>

<div class="container-fluid mt-4">
    <div class="mb-4 d-flex flex-wrap gap-2 align-items-center">
        <a href='creer_service.php' class='btn btn-primary'><i class="bi bi-plus-circle"></i> <span data-i18n>Créer un nouveau service</span></a>
    </div>

    <div class="card p-3 shadow-sm mb-4">
        <h5 class="mb-2" data-i18n>Validation des catégories</h5>
        <p class="small text-muted mb-2" data-i18n>Les catégories créées par un prestataire restent masquées du catalogue public tant qu’elles ne sont pas validées.</p>
        <div id="categories_admin_zone"><span class="text-muted small">Chargement…</span></div>
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

<div class="mb-4"></div>

</div>

<?php include("includes/footer.php") ?>

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
        let html = "<div class='table-responsive'><table class='table table-hover'><thead class='table-success'><tr><th data-i18n>Image</th><th data-i18n>Nom du service</th><th data-i18n>Catégorie</th><th data-i18n>Description</th><th data-i18n>Tarif</th><th data-i18n>Validation</th><th data-i18n>Actions</th></tr></thead><tbody>";
        list.forEach((serv) => {
            const actions = "<div class='d-flex flex-wrap gap-2 align-items-center'>" +
                "<a href='modifier_service.php?id=" + serv.id + "' class='btn btn-sm btn-warning' data-i18n>Modifier</a>" +
                "<a href='#' onclick='supprimer_service(" + serv.id + ", " + JSON.stringify(serv.nom) + "); return false;' class='btn btn-sm btn-danger' data-i18n>Supprimer</a></div>";
            const imageHtml = renderImageHtml(serv.image, `Image de ${serv.nom}`);
            const desc = (serv.description || '').length > 40 ? String(serv.description).slice(0, 40) + "..." : String(serv.description);
            const catLabel = serv.categorie ? String(serv.categorie) : "<span class='text-muted'>—</span>";
            const va = (serv.valide_admin === 1 || serv.valide_admin === true) ? "<span class='badge text-bg-success'>Validé</span>" : "<span class='badge text-bg-warning text-dark'>En attente</span>";
            html += "<tr><td>" + imageHtml + "</td><td>" + String(serv.nom) + "</td><td>" + catLabel + "</td><td>" + desc + "</td><td>" + String(serv.tarif) + " €</td><td>" + va + "</td><td>" + actions + "</td></tr>";
        });
        html += "</tbody></table></div>";
        container.innerHTML = html;
    }

    async function loadCategoriesForFilters(token) {
        const base = (window.API_BASE || 'http://localhost:9000');
        const categorySelect = document.getElementById("categoryFilter");
        try {
            const response = await fetch(base + "/list_categories", {
                method: "GET",
                headers: { Token: token || "" },
            });
            if (!response.ok) return;
            const payload = await response.json();
            if (!payload.categorie || !Array.isArray(payload.categorie)) return;
            while (categorySelect.options.length > 1) {
                categorySelect.remove(1);
            }
            payload.categorie.forEach((c) => {
                const option = document.createElement("option");
                option.value = String(c.id);
                const pend = (c.valide_admin === 0 || c.valide_admin === false) ? " (en attente)" : "";
                option.textContent = (c.nom || "") + pend;
                categorySelect.appendChild(option);
            });
        } catch (e) {}
    }

    async function chargerCategoriesAdmin(token) {
        const base = (window.API_BASE || 'http://localhost:9000');
        const zone = document.getElementById("categories_admin_zone");
        if (!zone) return;
        try {
            const response = await fetch(base + "/list_categories", {
                method: "GET",
                headers: { Token: token || "" },
            });
            if (!response.ok) {
                zone.innerHTML = "<span class='text-danger small'>Impossible de charger les catégories.</span>";
                return;
            }
            const payload = await response.json();
            const list = payload.categorie || [];
            if (!list.length) {
                zone.innerHTML = "<span class='text-muted small'>Aucune catégorie.</span>";
                return;
            }
            let html = "<div class='table-responsive'><table class='table table-sm table-bordered mb-0'><thead><tr><th>Nom</th><th>État</th><th>Action</th></tr></thead><tbody>";
            list.forEach((c) => {
                const ok = (c.valide_admin === 1 || c.valide_admin === true);
                const badge = ok ? "<span class='badge text-bg-success'>Validée</span>" : "<span class='badge text-bg-warning text-dark'>En attente</span>";
                const btn = ok
                    ? "<button type='button' class='btn btn-sm btn-outline-secondary' data-cat-invalidate='" + c.id + "'>Retirer validation</button>"
                    : "<button type='button' class='btn btn-sm btn-primary' data-cat-validate='" + c.id + "'>Valider</button>";
                html += "<tr><td>" + String(c.nom || "").replace(/</g, "&lt;") + "</td><td>" + badge + "</td><td>" + btn + "</td></tr>";
            });
            html += "</tbody></table></div>";
            zone.innerHTML = html;
            zone.querySelectorAll("[data-cat-validate]").forEach((btn) => {
                btn.addEventListener("click", () => patchCategorieAdmin(token, btn.getAttribute("data-cat-validate"), 1));
            });
            zone.querySelectorAll("[data-cat-invalidate]").forEach((btn) => {
                btn.addEventListener("click", () => patchCategorieAdmin(token, btn.getAttribute("data-cat-invalidate"), 0));
            });
        } catch (e) {
            zone.innerHTML = "<span class='text-danger small'>Erreur réseau.</span>";
        }
    }

    async function patchCategorieAdmin(token, id, valide) {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/modifier_categorie/" + encodeURIComponent(id), {
            method: "PATCH",
            headers: { "Content-Type": "application/json", Token: token || "" },
            body: JSON.stringify({ valide_admin: valide }),
        });
        if (!response.ok) {
            alert(await response.text() || "Erreur");
            return;
        }
        await chargerCategoriesAdmin(token);
        await loadCategoriesForFilters(token);
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
        await loadCategoriesForFilters(token);
        await chargerCategoriesAdmin(token);
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

