<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title data-i18n>Détail du conseil</title>
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

<div class="container mt-5 mb-4 text-center">
<h1 id="title" class="mb-custom" style="font-size:50px" data-i18n></h1>
<div class="container-lg">
    <div class="container-lg" id="conseilDetail" data-i18n>Chargement...</div>
</div>
</div>

<?php include 'includes/footer.php';?>

<script>
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");

async function loadConseilDetail(token) {
    const container = document.getElementById("conseilDetail");

    if (!id) {
        container.innerHTML = '<div class="alert alert-danger">ID du conseil manquant.</div>';
        return;
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/conseils/' + encodeURIComponent(id), {
        method: 'GET'
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

    const conseil = await response.json();
    const imageSrc = conseil.image
    ? 'upload/' + encodeURIComponent(conseil.image)
    : 'noimage.avif';

    const imageHtml = `
        <img 
            class="img-fluid rounded mb-4"
            style="height:420px;object-fit:cover;"
            src="${imageSrc}"
            alt="Image du conseil"
        >
    `;

    document.getElementById("title").innerHTML = "Détail du conseil : " + conseil.titre
    
    container.innerHTML = `
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-4">
                    ${imageHtml}
                </div>
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <h2 class="h3 mb-2">
                            <strong>${String(conseil.titre)}</strong>
                        </h2>
                        <p class="text-muted small mb-3">
                            Publié le ${String(conseil.date || '')}
                        </p>
                        <div class="text-secondary mb-4 mt-4"
                        style="word-break:break-word;">
                            ${String(conseil.contenu || '')}
                        </div>
                        <a class="btn btn-primary" href="conseils.php" data-i18n>
                            Retour à la page conseils
                        </a>
                    </div>
                    <div class="col-12 col-lg-6">
                        <h4 class="h3 mb-2">
                            Notez le conseil : ${String(conseil.titre)}
                        </h4>
                        <p id="notemoyenne" class="text-muted small mb-3">
                            Aucune note pour ce conseil
                        </p>
                        <div id="vote_1">
                            <div class="mb-4 mt-4 d-flex justify-content-center gap-4 align-items-center w-100">
                                <label class="d-flex align-items-center gap-2">
                                    <input type="radio" name="note" value=0>
                                    0
                                </label>
                                <label class="d-flex align-items-center gap-2">
                                    <input type="radio" name="note" value=1>
                                    1
                                </label>
                                <label class="d-flex align-items-center gap-2">
                                    <input type="radio" name="note" value=2>
                                    2
                                </label>
                                <label class="d-flex align-items-center gap-2">
                                    <input type="radio" name="note" value=3>
                                    3
                                </label>
                                <label class="d-flex align-items-center gap-2">
                                    <input type="radio" name="note" value=4>
                                    4
                                </label>
                                <label class="d-flex align-items-center gap-2">
                                    <input type="radio" name="note" value=5>
                                    5
                                </label>
                            </div>
                            <button class="btn btn-success" onclick="envoyerNote(localStorage.getItem('token'))">
                                Envoyer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    verifvote(token)
}

async function envoyerNote(token){
    const radios = document.getElementsByName("note");

    let note = null;

    for (let i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            note = radios[i].value;
            break;
        }
    }

    if (!note){
        alert("Veuillez selectionner une note")
        return
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/conseils-note', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Token': token },
        body: JSON.stringify({
            "id_conseil":Number(id),
            "note":Number(note)
        })
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

        window.location.reload()
}

async function verifvote(token){
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/conseils-note', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Token': token },
        body: JSON.stringify({
            "id_conseil":Number(id),
            "note":-1
        })
    });

    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
        }

    const data = await response.json()
    if (data.moyenne >= 0){
        document.getElementById("notemoyenne").innerHTML = "Note moyenne pour ce conseil : " + data.moyenne.toFixed(2)
    }
    if (data.message == "L'utilisateur a déjà voté"){
        document.getElementById("vote_1").innerHTML = `
        <div class="mb-4 mt-4 fs-5 d-flex justify-content-center gap-4 align-items-center w-100">
        <strong>Vous avez déja noté ce conseil</strong>
        <button onclick='annulervote("${token}")' class="btn btn-danger btn-sm">
        Annuler le vote
        </button>
        </div>
        Votre note pour ce conseil : ${data.note}`
    }
}

async function annulervote(token){
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/annuler-note', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json', 'Token': token },
        body: JSON.stringify({
            "id_conseil":Number(id),
        })
    });
    if (!response.ok) {
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return
    }
    window.location.reload()
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        loadConseilDetail(token);
    }

init()
</script>
</body>
</html>

