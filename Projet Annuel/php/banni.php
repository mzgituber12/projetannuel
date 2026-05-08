<?php include 'includes/api_config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte suspendu ou banni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="police.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h1 class="h4 mb-0">Accès restreint</h1>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Statut :</strong> <span id="statut_ban">-</span></p>
                    <p class="mb-2"><strong>Type de sanction :</strong> <span id="type_ban">-</span></p>
                    <p class="mb-2"><strong>Motif :</strong> <span id="motif_ban">-</span></p>
                    <p class="mb-4"><strong>Fin prévue :</strong> <span id="fin_ban">Non définie</span></p>
                    <a href="connexion.php" class="btn btn-outline-primary">Retour connexion</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function labelStatut(statut) {
        if (statut === "suspendu") return "Suspendu";
        if (statut === "banni") return "Banni";
        return "Inconnu";
    }

    function labelType(type) {
        if (type === "temp") return "Bannissement temporaire";
        if (type === "perm") return "Bannissement définitif";
        if (type === "warn") return "Avertissement";
        return type || "Non renseigné";
    }

    const params = new URLSearchParams(window.location.search);
    const statut = params.get("statut") || "banni";
    const type = params.get("type") || "";
    const motif = params.get("motif") || "Aucun motif renseigné";
    const fin = params.get("fin") || "";

    document.getElementById("statut_ban").textContent = labelStatut(statut);
    document.getElementById("type_ban").textContent = labelType(type);
    document.getElementById("motif_ban").textContent = motif;
    document.getElementById("fin_ban").textContent = fin || "Non définie";
</script>
</body>
</html>
