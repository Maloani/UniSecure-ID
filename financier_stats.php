<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'financier') {
    header('Location: login.php');
    exit;
}

require('fpdf/fpdf.php');

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$annee = $_GET['annee'] ?? date('Y');
$motif = $_GET['motif'] ?? '';

$query = "SELECT MONTH(date_paiement) AS mois, SUM(montant) AS total FROM paiements WHERE 1";
$params = [];

if ($annee) {
    $query .= " AND YEAR(date_paiement) = ?";
    $params[] = $annee;
}
if ($motif) {
    $query .= " AND motif LIKE ?";
    $params[] = "%$motif%";
}
$query .= " GROUP BY mois ORDER BY mois ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$stats_mensuelles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_paiements = $pdo->query("SELECT COUNT(*) FROM paiements")->fetchColumn();
$montant_total = $pdo->query("SELECT SUM(montant) FROM paiements")->fetchColumn();

$mois = [];
$montants = [];
foreach ($stats_mensuelles as $s) {
    $mois[] = date("F", mktime(0, 0, 0, $s['mois'], 10));
    $montants[] = round($s['total'], 2);
}

if (isset($_GET['pdf'])) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,"STATISTIQUES FINANCIERES - $annee",0,1,'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,"Motif : " . ($motif ?: 'Tous'),0,1);
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(60,10,'Mois',1);
    $pdf->Cell(80,10,'Montant Total ($)',1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',12);
    foreach ($stats_mensuelles as $s) {
        $pdf->Cell(60,10,date("F", mktime(0, 0, 0, $s['mois'], 10)),1);
        $pdf->Cell(80,10,number_format($s['total'], 2),1);
        $pdf->Ln();
    }
    $pdf->Output();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques Financières</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Statistiques Financières</h1>
            <a href="dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded">Retour</a>
        </div>

        <form method="get" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="annee" value="<?= htmlspecialchars($annee) ?>" placeholder="Année (ex: 2025)" class="p-2 border rounded">
            <input type="text" name="motif" value="<?= htmlspecialchars($motif) ?>" placeholder="Motif (ex: Inscription)" class="p-2 border rounded">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filtrer</button>
        </form>

        <div class="bg-white p-6 rounded shadow mb-6">
            <p class="text-lg font-semibold">Nombre total de paiements : <span class="text-blue-600"><?= $total_paiements ?></span></p>
            <p class="text-lg font-semibold">Montant total encaissé : <span class="text-green-600"><?= number_format($montant_total, 2) ?> $</span></p>
            <a href="?annee=<?= $annee ?>&motif=<?= urlencode($motif) ?>&pdf=1" class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded">Exporter en PDF</a>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-bold mb-4">Répartition Mensuelle des Paiements (<?= htmlspecialchars($annee) ?>)</h2>
            <canvas id="paiementsChart" height="100"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('paiementsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($mois) ?>,
                datasets: [{
                    label: 'Montant encaissé ($)',
                    data: <?= json_encode($montants) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Montants ($)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Mois'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
