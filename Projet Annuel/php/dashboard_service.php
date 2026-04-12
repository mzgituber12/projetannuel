<?php
$stats_services = [];

$csv_path = __DIR__ . "/dataset.csv";

if (file_exists($csv_path) && ($handle = fopen($csv_path, "r")) != FALSE) {
    $headers = fgetcsv($handle, 1000, ",");

    while (($data = fgetcsv($handle, 1000, ",")) != FALSE) {
        if (count($headers) !== count($data)) continue;

        $row = array_combine($headers, $data);
        $service = $row['target_service'];

        if (!isset($stats_services[$service])) {
            $stats_services[$service] = [
                'inscriptions' => 0,
                'revenus' => 0,
                'somme_annulations' => 0,
                'somme_satisfaction' => 0
            ];
        }

        $stats_services[$service]['inscriptions']++;
        $stats_services[$service]['revenus'] += (float)$row['depense_totale_estimee'];
        $stats_services[$service]['somme_annulations'] += (float)$row['taux_annulation'];
        $stats_services[$service]['somme_satisfaction'] += (float)$row['score_satisfaction'];
    }
    fclose($handle);
}

uasort($stats_services, function($a, $b) {
    return $b['inscriptions'] <=> $a['inscriptions'];
});

$labels = [];
$popularite = [];
$revenus = [];
$taux_annulation_moyen = [];
$satisfaction_moyenne = [];

foreach ($stats_services as $service => $data) {
    $labels[] = $service;
    $popularite[] = $data['inscriptions'];
    $revenus[] = round($data['revenus'], 2);

    $satisfaction_moyenne[] = round($data['somme_satisfaction'] / $data['inscriptions'], 1);
    $taux_annulation_moyen[] = round(($data['somme_annulations'] / $data['inscriptions']) * 100, 1);
}

$json_labels = json_encode($labels);
$json_pop = json_encode($popularite);
$json_rev = json_encode($revenus);
$json_satis = json_encode($satisfaction_moyenne);
$json_annul = json_encode($taux_annulation_moyen);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n>Dashboard Silver Happy - Succès des Prestations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light py-4">
    <main class="container">
        <h1 class="text-center mb-4" data-i18n>Tableau de Bord : Succès et Qualité des Prestations</h1>

        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 text-center mb-3" data-i18n>Popularité (Volume d'inscriptions)</h3>
                        <div class="position-relative w-100" style="height: 300px;">
                <canvas id="graphiquePopularite"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 text-center mb-3" data-i18n>Répartition du Chiffre d'Affaires (€)</h3>
                        <div class="position-relative w-100" style="height: 300px;">
                <canvas id="graphiqueRevenus"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 text-center mb-3" data-i18n>Score de Satisfaction Moyen (sur 5)</h3>
                        <div class="position-relative w-100" style="height: 300px;">
                <canvas id="graphiqueSatisfaction"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 text-center mb-3" data-i18n>Taux d'Annulation Moyen (%)</h3>
                        <div class="position-relative w-100" style="height: 300px;">
                <canvas id="graphiqueAnnulations"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="mt-4">
        <a href="index_module_ml.php" class="btn btn-outline-primary" data-i18n>Retour index ML</a>
    </div>
    <script>
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;

        const labelsServices = <?php echo $json_labels; ?>;

        new Chart(document.getElementById('graphiquePopularite'), {
            type: 'bar',
            data: {
                labels: labelsServices,
                datasets: [{
                    label: 'Nombre de réservations',
                    data: <?php echo $json_pop; ?>,
                    backgroundColor: '#36d8d8',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } }
            }
        });

        new Chart(document.getElementById('graphiqueRevenus'), {
            type: 'doughnut',
            data: {
                labels: labelsServices,
                datasets: [{
                    data: <?php echo $json_rev; ?>,
                    backgroundColor: ['#cf2d50', '#148dde', '#f6bc2a', '#36d8d8', '#834dee']
                }]
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) { return context.label + ': ' + context.raw + ' €'; }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('graphiqueSatisfaction'), {
            type: 'bar',
            data: {
                labels: labelsServices,
                datasets: [{
                    label: 'Note moyenne / 5',
                    data: <?php echo $json_satis; ?>,
                    backgroundColor: '#36d8d8',
                    borderRadius: 4
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, max: 5 }
                },
                plugins: { legend: { display: false } }
            }
        });

        new Chart(document.getElementById('graphiqueAnnulations'), {
            type: 'line',
            data: {
                labels: labelsServices,
                datasets: [{
                    label: '% d\'annulation',
                    data: <?php echo $json_annul; ?>,
                    borderColor: '#cf2d50',
                    backgroundColor: '#cf2d5033',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>