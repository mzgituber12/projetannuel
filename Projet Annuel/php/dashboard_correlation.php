<?php
$report_path = __DIR__ . "/ml/correlation_report.json";
$report = null;
$error_message = null;

if (file_exists($report_path)) {
    $report_content = file_get_contents($report_path);
    if ($report_content !== false) {
        $decoded = json_decode($report_content, true);
        if (is_array($decoded)) {
            $report = $decoded;
        }
    }
}

if (!is_array($report)) {
    $error_message = "L'analyse de corrélation n'est pas encore générée.";
}

$total_rows = isset($report["rows"]) ? (int)$report["rows"] : 0;
$numeric_features = isset($report["numeric_features"]) && is_array($report["numeric_features"]) ? $report["numeric_features"] : [];
$categorical_features = isset($report["categorical_features"]) && is_array($report["categorical_features"]) ? $report["categorical_features"] : [];
$target_classes_count = isset($report["target_classes_count"]) ? (int)$report["target_classes_count"] : 0;
$matrix_labels = $numeric_features;
$matrix_rows = isset($report["pearson_matrix"]) && is_array($report["pearson_matrix"]) ? $report["pearson_matrix"] : [];
$eta_scores = isset($report["eta_scores"]) && is_array($report["eta_scores"]) ? $report["eta_scores"] : [];
$cramers_scores = isset($report["cramers_scores"]) && is_array($report["cramers_scores"]) ? $report["cramers_scores"] : [];
$generated_at = isset($report["generated_at"]) ? (string)$report["generated_at"] : "--";

$eta_labels = [];
$eta_values = [];
foreach ($eta_scores as $item) {
    $eta_labels[] = isset($item["feature"]) ? $item["feature"] : "";
    $eta_values[] = isset($item["score"]) ? (float)$item["score"] : 0.0;
}

$cramers_labels = [];
$cramers_values = [];
foreach ($cramers_scores as $item) {
    $cramers_labels[] = isset($item["feature"]) ? $item["feature"] : "";
    $cramers_values[] = isset($item["score"]) ? (float)$item["score"] : 0.0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n>Dashboard Corrélations - Features ML</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="police.css">
</head>
<body class="bg-light py-4">
<main class="container">
    <?php if ($error_message != null): ?>
        <div class="alert alert-warning" role="alert">
            <strong data-i18n>Analyse non disponible.</strong>
            <span data-i18n><?php echo htmlspecialchars($error_message, ENT_QUOTES, "UTF-8"); ?></span>
            <div class="mt-2">docker compose run --rm ml_trainer python /app/ml/correlation.py</div>
        </div>
    <?php endif; ?>

    <h1 class="text-center mb-4" data-i18n>Corrélations et Sélection de Features</h1>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted" data-i18n>Lignes analysées</div>
                    <div class="display-6 fw-semibold"><?php echo htmlspecialchars((string)$total_rows, ENT_QUOTES, "UTF-8"); ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted" data-i18n>Features numériques</div>
                    <div class="display-6 fw-semibold"><?php echo htmlspecialchars((string)count($numeric_features), ENT_QUOTES, "UTF-8"); ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted" data-i18n>Features catégorielles</div>
                    <div class="display-6 fw-semibold"><?php echo htmlspecialchars((string)count($categorical_features), ENT_QUOTES, "UTF-8"); ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted" data-i18n>Classes target_service</div>
                    <div class="display-6 fw-semibold"><?php echo htmlspecialchars((string)$target_classes_count, ENT_QUOTES, "UTF-8"); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-light border mb-4" role="alert">
        <strong data-i18n>Dernière génération :</strong>
        <?php echo htmlspecialchars($generated_at, ENT_QUOTES, "UTF-8"); ?>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3" data-i18n>Matrice de corrélation Pearson (numérique)</h2>
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0" id="correlationTable">
                    <thead>
                        <tr>
                            <th scope="col">&nbsp;</th>
                            <?php foreach ($matrix_labels as $label): ?>
                                <th scope="col"><?php echo htmlspecialchars((string)$label, ENT_QUOTES, "UTF-8"); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matrix_labels as $row_index => $row_label): ?>
                            <tr>
                                <th scope="row"><?php echo htmlspecialchars((string)$row_label, ENT_QUOTES, "UTF-8"); ?></th>
                                <?php $row_values = isset($matrix_rows[$row_index]) && is_array($matrix_rows[$row_index]) ? $matrix_rows[$row_index] : []; ?>
                                <?php foreach ($row_values as $value): ?>
                                    <td class="corr-cell" data-value="<?php echo htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8"); ?>">
                                        <?php echo htmlspecialchars(number_format((float)$value, 2), ENT_QUOTES, "UTF-8"); ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h5 text-center mb-3" data-i18n>Score Eta² : numériques vs target_service</h3>
                    <div class="position-relative w-100" style="height: 320px;">
                        <canvas id="graphEta"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h5 text-center mb-3" data-i18n>Score Cramér's V : catégorielles vs target_service</h3>
                    <div class="position-relative w-100" style="height: 320px;">
                        <canvas id="graphCramers"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="index_module_ml.php" class="btn btn-outline-primary" data-i18n>Retour index ML</a>
    </div>
</main>

<script>
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;

    const etaLabels = <?php echo json_encode($eta_labels); ?>;
    const etaValues = <?php echo json_encode($eta_values); ?>;
    const cramersLabels = <?php echo json_encode($cramers_labels); ?>;
    const cramersValues = <?php echo json_encode($cramers_values); ?>;

    new Chart(document.getElementById('graphEta'), {
        type: 'bar',
        data: {
            labels: etaLabels,
            datasets: [{
                label: 'Eta²',
                data: etaValues,
                backgroundColor: '#36d8d8',
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            scales: { x: { beginAtZero: true, max: 1 } },
            plugins: { legend: { display: false } }
        }
    });

    new Chart(document.getElementById('graphCramers'), {
        type: 'bar',
        data: {
            labels: cramersLabels,
            datasets: [{
                label: "Cramer's V",
                data: cramersValues,
                backgroundColor: '#148dde',
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            scales: { x: { beginAtZero: true, max: 1 } },
            plugins: { legend: { display: false } }
        }
    });

    function colorForCorrelation(value) {
        const v = Math.max(-1, Math.min(1, value));
        if (v >= 0) {
            const alpha = 0.1 + (0.55 * v);
            return `rgba(20, 141, 222, ${alpha})`;
        }
        const alpha = 0.1 + (0.55 * Math.abs(v));
        return `rgba(207, 45, 80, ${alpha})`;
    }

    document.querySelectorAll('.corr-cell').forEach((cell) => {
        const value = parseFloat(cell.dataset.value);
        cell.style.backgroundColor = colorForCorrelation(value);
    });
</script>
</body>
</html>