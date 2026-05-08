<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>
<script src="admin.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title id ="page_title"></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="police.css">
</head>
<body>

<?php include 'includes/header.php'?>
<h1 id="admin_title"></h1>
<h2 id ="admin_err"></h2>

<div id="resultat"></div>
<?php include 'includes/footer.php'?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
    let categoriesCache = [];

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    async function loadCategories() {
        const base = (window.API_BASE || "http://localhost:9000");
        const token = localStorage.getItem("token");
        const response = await fetch(base + "/list_categories", {
            method: "GET",
            headers: { Token: token || "" },
        });
        if (!response.ok) {
            categoriesCache = [];
            return;
        }
        const payload = await response.json();
        categoriesCache = payload.categorie || [];
    }

    function categorySelectHtml(selectedId) {
        let opts = '<option value="">Aucune catégorie</option>';
        const sid = selectedId != null && selectedId != "" ? String(selectedId) : "";
        categoriesCache.forEach((c) => {
            const sel = String(c.id) == sid ? " selected" : "";
            const resultat_validation = (c.valide_admin == 0 || c.valide_admin == false) ? " (en attente validation)" : "";
            opts += "<option value=\"" + String(c.id) + "\"" + sel + ">" + escapeHtml(c.nom) + resultat_validation + "</option>";
        });
        return "<label data-i18n>Catégorie :</label><select id=\"service_id_categorie\" class=\"form-select\" style=\"max-width:420px;\">" + opts + "</select><br><br>";
    }

    async function creerNouvelleCategorieModifier() {
        const nom = document.getElementById("nouvelle_categorie_nom").value.trim();
        if (!nom) {
            document.getElementById("admin_err").innerHTML = "Indiquez un nom pour la catégorie.";
            return;
        }
        const base = (window.API_BASE || "http://localhost:9000");
        const response = await fetch(base + "/creer_categorie", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ nom: nom }),
        });
        if (!response.ok) {
            const text = await response.text();
            document.getElementById("admin_err").innerHTML = text || "Erreur lors de la création de la catégorie.";
            return;
        }
        const cat = await response.json();
        const sel = document.getElementById("service_id_categorie");
        const opt = document.createElement("option");
        opt.value = String(cat.id);
        opt.textContent = cat.nom;
        sel.appendChild(opt);
        sel.value = String(cat.id);
        categoriesCache.push({ id: cat.id, nom: cat.nom });
        document.getElementById("nouvelle_categorie_nom").value = "";
        document.getElementById("admin_err").innerHTML = "";
    }

    async function updateService(event) {
        event.preventDefault();
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/modifier_service/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "PATCH",
            headers: {"Content-Type": "application/json", "Token": localStorage.getItem("token") || ""},
            body: JSON.stringify({
                id: parseInt(document.getElementById("service_id").value, 10),
                nom: document.getElementById('service_nom').value,
                description: document.getElementById('service_description').value,
                tarif: parseFloat(document.getElementById('service_tarif').value),
                id_categorie: parseInt(document.getElementById('service_id_categorie').value, 10) || 0,
                valide_admin: document.getElementById('service_valide_admin').checked ? 1 : 0
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

    async function search_service() {
        const base = (window.API_BASE || 'http://localhost:9000');
        const response = await fetch(base + "/gestion_service_id/" + <?php echo json_encode($_GET["id"]); ?>, {
            method: "GET",
        });
        if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }
        const data = await response.json();

        document.getElementById("page_title").innerHTML = "Modifier le service " + escapeHtml(data.nom);
        document.getElementById('admin_title').innerHTML = "Modification du service " + escapeHtml(data.nom);
        if(data.id == 0 || !data.id) {
            document.getElementById("resultat").innerHTML = "Aucun service trouvé";
        } else {
            await loadCategories();
            const catBlock = categorySelectHtml(data.id_categorie);
            const checkedAdmin = data.valide_admin ? " checked" : "";
            document.getElementById("resultat").innerHTML = `<form onsubmit="updateService(event)">
            <label data-i18n>ID :</label>
            <input type="number" name="id" id="service_id" value="${data.id}" readonly> <span data-i18n>Pas modifiable</span> <br><br>
            <label data-i18n>Nom :</label>
            <input type="text" name="nom" id="service_nom" value="${escapeHtml(data.nom)}" required><br><br>
            <label data-i18n>Description :</label>
            <textarea name="description" id="service_description" required></textarea><br><br>${catBlock}
            <label data-i18n>Tarif :</label>
            <input type="number" name="tarif" id="service_tarif" value="${data.tarif}" step="0.01" required><br><br>
            <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="service_valide_admin"${checkedAdmin}>
            <label class="form-check-label" for="service_valide_admin" data-i18n>Service validé (visible dans le catalogue)</label></div>
            <button type="submit" class="btn btn-primary" data-i18n>Confirmer les modifications</button>
            </form>`;
            document.getElementById("service_description").value = data.description != null ? data.description : "";
            }
        }

    async function init(){
        const token = localStorage.getItem("token")
        if (!await loginUser("online", token)) return
        if (!await adminUser(token)) return
        search_service();
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
