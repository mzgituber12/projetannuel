<?php
include 'includes/api_config.php';

$report_json_path = __DIR__ . '/ml/training_report.json';
$report_text_path = __DIR__ . '/ml/training_report.txt';

$report = null;
$report_text = null;
$error_message = null;
$is_running = false;
$accuracy_percent = null;
$rows_count = null;
$features_count = null;
$dataset_file = null;
$classes = [];

if (file_exists($report_json_path)) {
    $raw = file_get_contents($report_json_path);
    $report = json_decode($raw, true);
    if (!is_array($report)) {
        $error_message = 'Le fichier training_report.json existe mais est invalide.';
    }
} else {
    $error_message = 'Aucun resultat ML trouve pour le moment.';
}

if (file_exists($report_text_path)) {
    $report_text = file_get_contents($report_text_path);
}

if (is_array($report)) {
    $status = isset($report['status']) ? (string)$report['status'] : 'done';
    $is_running = ($status == 'running');

    $accuracy_percent = isset($report['accuracy_percent']) ? $report['accuracy_percent'] : null;
    $rows_count = isset($report['rows']) ? $report['rows'] : null;
    $features_count = isset($report['features_count']) ? $report['features_count'] : null;
    $dataset_file = isset($report['dataset_file']) ? $report['dataset_file'] : null;
    $classes = isset($report['classes']) && is_array($report['classes']) ? $report['classes'] : [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n>Module ML - Résultats</title>
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>

<main class="container py-4">
    <h1 class="mb-4" data-i18n>Module Machine Learning</h1>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a class="btn btn-primary" href="dashboard.php" data-i18n>Dashboard adhérents</a>
        <a class="btn btn-primary" href="dashboard_service.php" data-i18n>Dashboard prestations</a>
        <a class="btn btn-primary" href="dashboard_correlation.php" data-i18n>Dashboard corrélations</a>
    </div>

    <?php if ($error_message !== null): ?>
        <div class="alert alert-warning" role="alert">
            <strong data-i18n>Entraînement en attente.</strong>
            <span data-i18n><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></span>
            <div class="mt-2" data-i18n>Lance les conteneurs avec docker compose up -d --build, puis recharge cette page.</div>
        </div>
    <?php endif; ?>

    <?php if ($is_running): ?>
        <div class="alert alert-info" role="alert">
            <strong data-i18n>Entraînement en cours...</strong>
            <span data-i18n>Le modèle est en train d'être calculé, les métriques finales seront disponibles à la fin.</span>
        </div>
    <?php endif; ?>

    <?php if (is_array($report)): ?>
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted" data-i18n>Précision</div>
                        <div class="display-6 fw-semibold">
                            <?php if ($accuracy_percent !== null): ?>
                                <?php echo htmlspecialchars((string)$accuracy_percent, ENT_QUOTES, 'UTF-8'); ?>%
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted" data-i18n>Lignes dataset</div>
                        <div class="display-6 fw-semibold"><?php echo htmlspecialchars((string)($rows_count ?? '--'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted" data-i18n>Nombre de features</div>
                        <div class="display-6 fw-semibold"><?php echo htmlspecialchars((string)($features_count ?? '--'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted" data-i18n>Dataset utilisé</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($dataset_file ?? '--'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3" data-i18n>Classes prédites</h2>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($classes as $class_name): ?>
                        <span class="badge text-bg-secondary"><?php echo htmlspecialchars((string)$class_name, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-3" data-i18n>Rapport détaillé (classification_report)</h2>
            <?php if ($report_text !== null && trim($report_text) !== ''): ?>
                <pre class="bg-dark text-light p-3 rounded mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($report_text, ENT_QUOTES, 'UTF-8'); ?></pre>
            <?php else: ?>
                <p class="mb-0 text-muted" data-i18n>Le rapport texte n'est pas encore disponible.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>