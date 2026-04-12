<?php
$tranches_age = ['60-70 ans' => 0, '71-80 ans' => 0, '81-90 ans' => 0, '91+ ans' => 0];
$repartition_langues = [];
$repartition_sexe = ['F' => 0, 'H' => 0];
$repartition_abonnements = [];

$csv_path = __DIR__ . "/dataset.csv";

if (file_exists($csv_path) && ($handle = fopen($csv_path, "r")) != FALSE) {
    $headers = fgetcsv($handle, 1000, ",");

    while (($data = fgetcsv($handle, 1000, ",")) != FALSE) {
        if (count($headers) != count($data)) continue; 

        $row = array_combine($headers, $data); 

        $age = (int)$row['age'];
        if ($age <= 70) $tranches_age['60-70 ans']++;
        elseif ($age <= 80) $tranches_age['71-80 ans']++;
        elseif ($age <= 90) $tranches_age['81-90 ans']++;
        else $tranches_age['91+ ans']++;

        $langue = strtoupper($row['langue']);
        if (isset($repartition_langues[$langue])) {
            $repartition_langues[$langue] = $repartition_langues[$langue] + 1;
        } else {
            $repartition_langues[$langue] = 1;
        }

        $sexe = $row['sexe'];
        if (isset($repartition_sexe[$sexe])) $repartition_sexe[$sexe]++;

        $abo = $row['type_abonnement'];
        if (isset($repartition_abonnements[$abo])) {
            $repartition_abonnements[$abo] = $repartition_abonnements[$abo] + 1;
        } else {
            $repartition_abonnements[$abo] = 1;
        }
    }
    fclose($handle);
}

$data = [
    'age' => ['labels' => json_encode(array_keys($tranches_age)), 'valeurs' => json_encode(array_values($tranches_age))],
    'langue' => ['labels' => json_encode(array_keys($repartition_langues)), 'valeurs' => json_encode(array_values($repartition_langues))],
    'sexe' => ['labels' => json_encode(['Femmes', 'Hommes']), 'valeurs' => json_encode(array_values($repartition_sexe))],
    'abo' => ['labels' => json_encode(array_keys($repartition_abonnements)), 'valeurs' => json_encode(array_values($repartition_abonnements))]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n>Dashboard Silver Happy - Clients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light py-4">
    <main class="container">
        <h1 class="text-center mb-4" data-i18n>Tableau de Bord : Profil de nos Seniors</h1>

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-3" data-i18n>Pyramide des Âges</h3>
                        <div class="position-relative w-100" style="height: 300px;">
                <canvas id="graphiqueAge"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-3" data-i18n>Répartition Linguistique</h3>
                        <div class="position-relative w-100" style="height: 300px;">
                <canvas id="graphiqueLangue"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-3" data-i18n>Répartition Hommes/Femmes</h3>
                        <div class="position-relative w-100" style="height: 300px;">
                <canvas id="graphiqueSexe"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-3" data-i18n>Types d'Abonnements</h3>
                        <div class="position-relative w-100" style="height: 300px;">
                <canvas id="graphiqueAbo"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;

        new Chart(document.getElementById('graphiqueAge'), {
            type: 'bar',
            data: {
                labels: <?php echo $data['age']['labels']; ?>,
                datasets: [{
                    label: 'Nombre d\'utilisateurs',
                    data: <?php echo $data['age']['valeurs']; ?>,
                    backgroundColor: '#36d8d8',
                    borderRadius: 5
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('graphiqueLangue'), {
            type: 'pie',
            data: {
                labels: <?php echo $data['langue']['labels']; ?>,
                datasets: [{
                    data: <?php echo $data['langue']['valeurs']; ?>,
                    backgroundColor: ['#cf2d50', '#148dde', '#f6bc2a', '#36d8d8', '#834dee']
                }]
            }
        });

        new Chart(document.getElementById('graphiqueSexe'), {
            type: 'doughnut',
            data: {
                labels: <?php echo $data['sexe']['labels']; ?>,
                datasets: [{
                    data: <?php echo $data['sexe']['valeurs']; ?>,
                    backgroundColor: ['#f6bc2a', '#148dde']
                }]
            }
        });

        new Chart(document.getElementById('graphiqueAbo'), {
            type: 'bar',
            data: {
                labels: <?php echo $data['abo']['labels']; ?>,
                datasets: [{
                    label: 'Adhérents',
                    data: <?php echo $data['abo']['valeurs']; ?>,
                    backgroundColor: '#36d8d8',
                    borderRadius: 5
                }]
            },
            options: { 
                indexAxis: 'y', 
                plugins: { legend: { display: false } } 
            }
        });
    </script>
    <div class="mt-4">
        <a href="index_module_ml.php" class="btn btn-outline-primary" data-i18n>Retour index ML</a>
    </div>
</body>
</html>
