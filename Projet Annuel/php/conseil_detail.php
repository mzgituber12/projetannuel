<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Détail du conseil</title>
    <style>
        .conseil-detail-shell {
            max-width: 900px;
            width: min(900px, calc(100% - 2rem));
            margin: 1.2rem auto;
            padding: 1.2rem;
            border: 1px solid rgba(0,0,0,.1);
            border-radius: 12px;
            background: #f8fbff;
            box-sizing: border-box;
        }

        .conseil-detail-image {
            width: 100%;
            max-height: 420px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #dbe3ff;
            background: #eef2ff;
        }

        .conseil-detail-title {
            margin: 1rem 0 0.6rem 0;
            font-size: 1.8rem;
            font-weight: 800;
            color: #111827;
        }

        .conseil-detail-date {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .conseil-detail-content {
            margin-top: 1rem;
            color: #1f2937;
            line-height: 1.7;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .conseil-back-wrap {
            margin-top: 1.4rem;
            display: flex;
            justify-content: flex-start;
        }

        .conseil-back-btn {
            text-decoration: none;
            background: #2563eb;
            color: #fff;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-weight: 700;
        }

        .conseil-back-btn:hover {
            background: #1d4ed8;
        }

        .conseil-error {
            color: #b91c1c;
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 0.7rem;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php' ?>

<h1>Détail du conseil</h1>
<div class="conseil-detail-shell" id="conseilDetail">Chargement...</div>

<?php include 'includes/footer.php';?>

<script>
function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

async function loadConseilDetail() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");
    const container = document.getElementById("conseilDetail");

    if (!id) {
        container.innerHTML = '<p class="conseil-error">ID du conseil manquant.</p>';
        return;
    }

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + '/conseils/' + encodeURIComponent(id), {
        method: 'GET'
    });

    if (!response.ok) {
        const text = await response.text();
        container.innerHTML = '<p class="conseil-error">' + escapeHtml(text || 'Impossible de charger ce conseil.') + '</p>';
        return;
    }

    const conseil = await response.json();
    const imageHtml = conseil.image
        ? '<img class="conseil-detail-image" src="upload/' + encodeURIComponent(conseil.image) + '" alt="Image du conseil">'
        : '<div class="conseil-detail-image" style="display:flex;align-items:center;justify-content:center;color:#6b7280;">Pas d\'image</div>';

    container.innerHTML =
        imageHtml +
        '<h2 class="conseil-detail-title"><strong>' + escapeHtml(conseil.titre) + '</strong></h2>' +
        '<div class="conseil-detail-date">Publié le ' + escapeHtml(conseil.date || '') + '</div>' +
        '<div class="conseil-detail-content">' + escapeHtml(conseil.contenu || '') + '</div>' +
        '<div class="conseil-back-wrap"><a class="conseil-back-btn" href="conseils.php">Retour à la page conseils</a></div>';
}

window.addEventListener('DOMContentLoaded', loadConseilDetail);
</script>
</body>
</html>
