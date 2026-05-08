<?php include 'includes/api_config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        #dialog_edit_service { padding: 0; border: none; border-radius: 0.375rem; max-width: min(560px, 96vw); width: 100%; }
        #dialog_edit_service::backdrop { background: rgba(33, 37, 41, 0.45); }
    </style>
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>

<main class="container py-4">
    <h1 class="h3 mb-3">Mes services</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 card-title">Nouveau service</h2>
            <form id="form_service" class="row g-2">
                <div class="col-md-6">
                    <label class="form-label" for="inp_nom">Nom</label>
                    <input type="text" class="form-control" id="inp_nom" required maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="inp_tarif">Tarif (€)</label>
                    <input type="number" class="form-control" id="inp_tarif" step="0.01" min="0" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="inp_desc">Description</label>
                    <textarea class="form-control" id="inp_desc" rows="2" required></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="inp_cat">Catégorie (table <code>categorie</code>)</label>
                    <select class="form-select" id="inp_cat">
                        <option value="">Aucune</option>
                    </select>
                    <p id="diag_cat" class="small text-danger mb-0 d-none" role="status"></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="inp_img">Image (optionnel)</label>
                    <input type="file" class="form-control" id="inp_img" accept="image/*">
                </div>
                <div class="col-12 border-top pt-3 mt-2">
                    <h3 class="h6 mb-2">Créer une catégorie</h3>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8 col-lg-6">
                            <label class="form-label" for="inp_nouvelle_cat">Nom de la nouvelle catégorie</label>
                            <input type="text" class="form-control" id="inp_nouvelle_cat" maxlength="100" placeholder="Ex. : bricolage à domicile">
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <button type="button" class="btn btn-outline-secondary w-100" id="btn_creer_categorie">Créer la catégorie</button>
                        </div>
                    </div>
                    <p id="diag_creer_cat" class="small text-danger mb-0 mt-1 d-none" role="status"></p>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Créer le service</button>
                </div>
            </form>
        </div>
    </div>

    <h2 class="h5 mb-2">Mes prestations</h2>
    <div id="liste_services"></div>
</main>

<dialog id="dialog_edit_service">
    <div class="modal-content border-0">
        <div class="modal-header border-bottom py-2">
            <h2 class="h6 mb-0">Modifier le service</h2>
            <button type="button" class="btn-close" onclick="document.getElementById('dialog_edit_service').close()" aria-label="Fermer"></button>
        </div>
        <form id="form_edit_service" class="modal-body py-3">
            <input type="hidden" id="edit_id">
            <div class="mb-2">
                <label class="form-label" for="edit_nom">Nom</label>
                <input type="text" class="form-control form-control-sm" id="edit_nom" required maxlength="150">
            </div>
            <div class="mb-2">
                <label class="form-label" for="edit_tarif">Tarif (€)</label>
                <input type="number" class="form-control form-control-sm" id="edit_tarif" step="0.01" min="0" required>
            </div>
            <div class="mb-2">
                <label class="form-label" for="edit_desc">Description</label>
                <textarea class="form-control form-control-sm" id="edit_desc" rows="3" required></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label" for="edit_cat">Catégorie</label>
                <select class="form-select form-select-sm" id="edit_cat"><option value="">Aucune</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" for="edit_img">Nouvelle image (optionnel)</label>
                <input type="file" class="form-control form-control-sm" id="edit_img" accept="image/*">
                <span class="small text-muted">Laisser vide pour garder l’image actuelle.</span>
            </div>
        </form>
        <div class="modal-footer border-top py-2">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('dialog_edit_service').close()">Annuler</button>
            <button type="submit" form="form_edit_service" class="btn btn-primary btn-sm">Enregistrer</button>
        </div>
    </div>
</dialog>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    function apiBase() {
        return String(window.API_BASE || 'http://localhost:9000').replace(/\/$/, '');
    }

    async function verifierSessionEtPrestataire(token) {
        if (!token) {
            window.location.href = 'connexion.php';
            return false;
        }
        var base = apiBase();
        var response;
        try {
            response = await fetch(base + '/enligne', {
                method: 'GET',
                headers: { Token: token }
            });
        } catch (e) {
            alert("Impossible de joindre l'API.");
            window.location.href = 'erreur.php?code=503';
            return false;
        }
        if (!response.ok) {
            var txt = await response.text();
            alert(txt);
            window.location.href = 'erreur.php?code=' + response.status;
            return false;
        }
        var data;
        try {
            data = await response.json();
        } catch (e2) {
            window.location.href = 'erreur.php?code=' + response.status;
            return false;
        }
        if (data.message == 'Pas identifié') {
            window.location.href = 'index.php';
            return false;
        }
        if (data.role != 'prestataire') {
            alert('Acces reserve aux prestataires.');
            window.location.href = 'erreur.php?code=403';
            return false;
        }
        return true;
    }

    function afficherDiagCat(msg) {
        var d = document.getElementById('diag_cat');
        if (!d) return;
        if (msg) {
            d.textContent = msg;
            d.classList.remove('d-none');
        } else {
            d.textContent = '';
            d.classList.add('d-none');
        }
    }

    function afficherDiagCreerCat(msg) {
        var d = document.getElementById('diag_creer_cat');
        if (!d) return;
        if (msg) {
            d.textContent = msg;
            d.classList.remove('d-none');
        } else {
            d.textContent = '';
            d.classList.add('d-none');
        }
    }

    async function creerCategoriePrestataire() {
        afficherDiagCreerCat('');
        var token = localStorage.getItem('token');
        var inp = document.getElementById('inp_nouvelle_cat');
        var nom = (inp && inp.value) ? inp.value.trim() : '';
        if (!nom) {
            afficherDiagCreerCat('Indiquez un nom pour la catégorie.');
            return;
        }
        var r;
        try {
            r = await fetch(apiBase() + '/prestataire/categorie', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Token: token },
                body: JSON.stringify({ nom: nom })
            });
        } catch (e) {
            afficherDiagCreerCat('Réseau : ' + (e && e.message ? e.message : 'erreur'));
            return;
        }
        var brut = await r.text();
        if (r.status === 201) {
            var data;
            try {
                data = JSON.parse(brut);
            } catch (e2) {
                data = {};
            }
            var newId = (data.id !== undefined && data.id !== null) ? data.id : data.id_categorie;
            inp.value = '';
            var garde = (newId !== undefined && newId !== null) ? String(newId) : '';
            var sel = document.getElementById('inp_cat');
            if (sel && garde) sel.value = garde;
            await chargerCategoriesDansLeFormulaire();
            if (sel && garde) sel.value = garde;
            alert(data.message || 'Catégorie créée.');
            return;
        }
        afficherDiagCreerCat('HTTP ' + r.status + ' : ' + brut.slice(0, 200));
    }

    function remplirSelectDepuisListe(sel, valeurSelectionnee) {
        if (!sel) return;
        sel.innerHTML = '<option value="">Aucune</option>';
        var liste = window._categoriesListe || [];
        liste.forEach(function (c) {
            if (!c || typeof c !== 'object') return;
            var rawId = (c.id !== undefined && c.id !== null) ? c.id : c.id_categorie;
            if (rawId === undefined || rawId === null || rawId === '') return;
            var o = document.createElement('option');
            o.value = String(rawId);
            o.textContent = (c.nom !== undefined && c.nom !== null && String(c.nom) !== '') ? String(c.nom) : ('#' + String(rawId));
            sel.appendChild(o);
        });
        var v = '';
        if (valeurSelectionnee !== undefined && valeurSelectionnee !== null && Number(valeurSelectionnee) > 0) {
            v = String(valeurSelectionnee);
        }
        if (v && [].some.call(sel.options, function (opt) { return opt.value === v; })) {
            sel.value = v;
        }
    }

    async function chargerCategoriesDansLeFormulaire() {
        var token = localStorage.getItem('token') || '';
        var sel = document.getElementById('inp_cat');
        var garde = sel ? sel.value : '';
        if (sel) sel.innerHTML = '<option value="">Aucune</option>';
        afficherDiagCat('');
        var url = apiBase() + '/prestataire/categories';
        var r;
        try {
            r = await fetch(url, { method: 'GET', headers: { Token: token }, cache: 'no-store' });
        } catch (e) {
            afficherDiagCat('Reseau : impossible de joindre ' + url + ' (' + (e && e.message ? e.message : 'erreur') + '). Verifiez API_BASE / Docker / port 9000.');
            return;
        }
        var ct = (r.headers.get('content-type') || '').toLowerCase();
        var brut = await r.text();
        if (!r.ok) {
            afficherDiagCat('HTTP ' + r.status + ' sur /categories. Debut de reponse : ' + brut.slice(0, 120));
            return;
        }
        if (ct.indexOf('json') === -1) {
            afficherDiagCat('Reponse non-JSON (content-type: ' + (r.headers.get('content-type') || '?') + '). Debut : ' + brut.slice(0, 120));
            return;
        }
        var data;
        try {
            data = JSON.parse(brut);
        } catch (e2) {
            afficherDiagCat('JSON invalide. Debut : ' + brut.slice(0, 120));
            return;
        }
        window._categoriesListe = (data && Array.isArray(data.categorie)) ? data.categorie : [];
        remplirSelectDepuisListe(document.getElementById('inp_cat'), garde);
        remplirSelectDepuisListe(document.getElementById('edit_cat'), '');
    }

    async function afficherMesServices() {
        var token = localStorage.getItem('token');
        var zone = document.getElementById('liste_services');
        var r = await fetch(apiBase() + '/prestataire/mes_services', {
            method: 'GET',
            headers: { Token: token }
        });
        if (!r.ok) {
            zone.innerHTML = '<p class="text-danger">Impossible de charger vos services.</p>';
            return;
        }
        var data = await r.json();
        var services = (data && Array.isArray(data.service)) ? data.service : [];
        if (!services.length) {
            zone.innerHTML = '<p class="text-muted">Aucun service pour le moment.</p>';
            return;
        }
        var html = '<div class="table-responsive"><table class="table table-sm table-bordered bg-white"><thead><tr><th>Nom</th><th>Catégorie</th><th>Tarif</th><th>Validation</th><th>Description</th><th></th></tr></thead><tbody>';
        services.forEach(function (s) {
            var sid = (s.id !== undefined && s.id !== null) ? s.id : s.id_service;
            var cat = s.categorie ? String(s.categorie) : '—';
            var desc = (s.description || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            if (desc.length > 80) desc = desc.slice(0, 80) + '…';
            var va = (s.valide_admin === 1 || s.valide_admin === true) ? '<span class="badge text-bg-success">Validé</span>' : '<span class="badge text-bg-warning text-dark">En attente admin</span>';
            html += '<tr><td>' + String(s.nom || '').replace(/</g, '&lt;') + '</td><td>' + cat.replace(/</g, '&lt;') + '</td><td>' + Number(s.tarif).toFixed(2) + ' €</td><td>' + va + '</td><td>' + desc + '</td><td><button type="button" class="btn btn-outline-primary btn-sm js-modifier-service" data-id="' + String(sid) + '">Modifier</button></td></tr>';
        });
        html += '</tbody></table></div>';
        zone.innerHTML = html;
    }

    async function ouvrirEditionService(idService) {
        var token = localStorage.getItem('token');
        if (!window._categoriesListe) {
            await chargerCategoriesDansLeFormulaire();
        }
        var r = await fetch(apiBase() + '/prestataire/mes_services/' + encodeURIComponent(String(idService)), {
            method: 'GET',
            headers: { Token: token }
        });
        if (!r.ok) {
            alert(await r.text() || 'Impossible de charger ce service');
            return;
        }
        var s = await r.json();
        document.getElementById('edit_id').value = String(idService);
        document.getElementById('edit_nom').value = s.nom || '';
        document.getElementById('edit_tarif').value = s.tarif != null ? String(s.tarif) : '';
        document.getElementById('edit_desc').value = s.description || '';
        remplirSelectDepuisListe(document.getElementById('edit_cat'), s.id_categorie);
        window._editImageCourante = (s.image && String(s.image)) || '';
        document.getElementById('edit_img').value = '';
        document.getElementById('dialog_edit_service').showModal();
    }

    document.getElementById('btn_creer_categorie').addEventListener('click', function () {
        creerCategoriePrestataire();
    });

    document.getElementById('liste_services').addEventListener('click', function (ev) {
        var btn = ev.target.closest('.js-modifier-service');
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        if (id) ouvrirEditionService(id);
    });

    document.getElementById('form_edit_service').addEventListener('submit', async function (ev) {
        ev.preventDefault();
        var token = localStorage.getItem('token');
        var id = document.getElementById('edit_id').value;
        var imageNom = window._editImageCourante || '';
        var inputFile = document.getElementById('edit_img');
        if (inputFile.files && inputFile.files[0]) {
            var fd = new FormData();
            fd.append('file', inputFile.files[0]);
            fd.append('uploadType', 'service');
            var up = await fetch('upload_image.php', { method: 'POST', body: fd });
            var upJson = await up.json();
            if (!up.ok || !upJson.success) {
                alert(upJson.message || 'Erreur upload image');
                return;
            }
            imageNom = upJson.fileName || '';
        }
        var idCat = parseInt(document.getElementById('edit_cat').value, 10);
        var corps = {
            nom: document.getElementById('edit_nom').value.trim(),
            description: document.getElementById('edit_desc').value,
            tarif: parseFloat(document.getElementById('edit_tarif').value),
            image: imageNom,
            id_categorie: isNaN(idCat) ? 0 : idCat
        };
        var r = await fetch(apiBase() + '/prestataire/mes_services/' + encodeURIComponent(id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', Token: token },
            body: JSON.stringify(corps)
        });
        if (r.status === 409) {
            var j = await r.json();
            alert(j.message || 'Nom deja utilise');
            return;
        }
        if (!r.ok) {
            alert(await r.text() || 'Erreur mise a jour');
            return;
        }
        var rep = await r.json();
        if (rep.value === 1) {
            document.getElementById('dialog_edit_service').close();
            await afficherMesServices();
            alert(rep.message || 'OK');
        } else {
            alert(rep.message || 'Erreur');
        }
    });

    document.getElementById('form_service').addEventListener('submit', async function (ev) {
        ev.preventDefault();
        var token = localStorage.getItem('token');
        var imageNom = '';
        var inputFile = document.getElementById('inp_img');
        if (inputFile.files && inputFile.files[0]) {
            var fd = new FormData();
            fd.append('file', inputFile.files[0]);
            fd.append('uploadType', 'service');
            var up = await fetch('upload_image.php', { method: 'POST', body: fd });
            var upJson = await up.json();
            if (!up.ok || !upJson.success) {
                alert(upJson.message || 'Erreur upload image');
                return;
            }
            imageNom = upJson.fileName || '';
        }
        var idCat = parseInt(document.getElementById('inp_cat').value, 10);
        var corps = {
            nom: document.getElementById('inp_nom').value.trim(),
            description: document.getElementById('inp_desc').value,
            tarif: parseFloat(document.getElementById('inp_tarif').value),
            image: imageNom,
            id_categorie: isNaN(idCat) ? 0 : idCat
        };
        var r = await fetch(apiBase() + '/prestataire/mes_services', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Token: token },
            body: JSON.stringify(corps)
        });
        if (r.status === 409) {
            var j = await r.json();
            alert(j.message || 'Nom deja utilise');
            return;
        }
        if (!r.ok) {
            alert(await r.text() || 'Erreur creation');
            return;
        }
        var rep = await r.json();
        if (rep.value === 1) {
            document.getElementById('form_service').reset();
            document.getElementById('inp_cat').innerHTML = '<option value="">Aucune</option>';
            await chargerCategoriesDansLeFormulaire();
            await afficherMesServices();
            alert(rep.message || 'OK');
        } else {
            alert(rep.message || 'Erreur');
        }
    });

    async function demarrer() {
            var token = localStorage.getItem('token');
            if (!await verifierSessionEtPrestataire(token)) return;
            await chargerCategoriesDansLeFormulaire();
            await afficherMesServices();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', demarrer);
    } else {
        demarrer();
    }
})();
</script>
</body>
</html>
