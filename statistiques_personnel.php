<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

require_once("fpdf/fpdf.php");

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$mode = $_GET['mode'] ?? '';
$search = $_GET['search'] ?? '';
$mois_filtre = $_GET['mois'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtrage SQL
$whereClause = "WHERE 1";
$params = [];

if ($search !== '') {
    $whereClause .= " AND (nom_complet LIKE ? OR DATE(date_heure) LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($mois_filtre !== '') {
    $whereClause .= " AND DATE_FORMAT(date_heure, '%Y-%m') = ?";
    $params[] = $mois_filtre;
}

// Récupération des données
$sql = "SELECT * FROM presences $whereClause ORDER BY nom_complet ASC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$presences = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM presences $whereClause");
$countStmt->execute($params);
$totalPresences = $countStmt->fetchColumn();
$totalPages = ceil($totalPresences / $limit);

// Graphique global
$graphData = $pdo->query("
    SELECT DATE_FORMAT(date_heure, '%Y-%m') AS mois, COUNT(*) AS total
    FROM presences
    GROUP BY mois
    ORDER BY mois
")->fetchAll(PDO::FETCH_ASSOC);

// Export PDF (uniquement les données filtrées)
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    class PDF extends FPDF {
        function Header() {
            if (file_exists('logo_universite.png')) {
                $this->Image('logo_universite.png',10,6,30);
            }
            $this->SetFont('Arial','B',14);
            $this->Cell(0,10,'UNIVERSITE DE LISALA',0,1,'C');
            $this->SetFont('Arial','',12);
            $this->Cell(0,10,'STATISTIQUE DE PRESENCES DU PERSONNEL',0,1,'C');
            $this->Ln(10);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(60,10,'Nom',1);
    $pdf->Cell(65,10,'Date d\'arrivee',1);
    $pdf->Cell(65,10,'Date de depart',1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',11);

    // Récupération de TOUTES les présences du mois filtré
    $pdfWhere = $whereClause . " LIMIT 1000"; // sécurité anti-surcharge
    $pdfStmt = $pdo->prepare("SELECT * FROM presences $pdfWhere");
    $pdfStmt->execute($params);
    $allPresences = $pdfStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allPresences as $p) {
        $pdf->Cell(60,10,utf8_decode($p['nom_complet']),1);
        $pdf->Cell(65,10,$p['date_heure'],1);
        $pdf->Cell(65,10,$p['date_depart'],1);
        $pdf->Ln();
    }

    $pdf->Output('D', 'statistiques_personnel_'.$mois_filtre.'.pdf');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques de présence - Personnel</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #f9f9f9; }
        h2 { color: #2c3e50; }
        .btn {
            background-color: chocolate;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            margin-right: 10px;
            cursor: pointer;
        }
        .btn:hover { background-color: #a0522d; }
        .form-select, .input-text {
            padding: 8px;
            font-size: 16px;
            margin: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #eee;
        }
        .pagination {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<a href="agent_presences.php" class="btn">← Retour</a>
<h2>Statistiques de présence - Personnel</h2>

<form method="GET">
    <label>Mode d’affichage :
        <select name="mode" class="form-select" onchange="this.form.submit()">
            <option value="">-- Choisir --</option>
            <option value="graphique" <?= $mode === 'graphique' ? 'selected' : '' ?>>Graphique</option>
            <option value="liste" <?= $mode === 'liste' ? 'selected' : '' ?>>Affichage (Liste)</option>
        </select>
    </label>
</form>

<?php if ($mode === 'graphique'): ?>
    <canvas id="presenceChart" height="100"></canvas>
    <script>
        const ctx = document.getElementById('presenceChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($graphData, 'mois')) ?>,
                datasets: [{
                    label: 'Présences par mois',
                    data: <?= json_encode(array_column($graphData, 'total')) ?>,
                    backgroundColor: 'chocolate'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Présences' }},
                    x: { title: { display: true, text: 'Mois' }}
                }
            }
        });
    </script>

<?php elseif ($mode === 'liste'): ?>

    <form method="GET">
        <input type="hidden" name="mode" value="liste">
        <input type="text" name="search" class="input-text" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher par nom ou date">
        <select name="mois" class="form-select" onchange="this.form.submit()">
            <option value="">-- Tous les mois --</option>
            <?php
            foreach ($graphData as $m) {
                $val = $m['mois'];
                $label = ucfirst(strftime('%B %Y', strtotime($val)));
                echo "<option value='$val'" . ($mois_filtre === $val ? ' selected' : '') . ">$label</option>";
            }
            ?>
        </select>
        <button type="submit" class="btn">🔍 Rechercher</button>
    </form>

    <form method="GET">
        <input type="hidden" name="mode" value="liste">
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <input type="hidden" name="mois" value="<?= htmlspecialchars($mois_filtre) ?>">
        <button type="submit" name="export" value="pdf" class="btn">📄 Exporter ce mois en PDF</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Date d’arrivée</th>
                <th>Date de départ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($presences as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nom_complet']) ?></td>
                    <td><?= htmlspecialchars($p['date_heure']) ?></td>
                    <td><?= htmlspecialchars($p['date_depart']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($presences)): ?>
                <tr><td colspan="3">Aucune présence trouvée pour ce mois.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?mode=liste&search=<?= urlencode($search) ?>&mois=<?= $mois_filtre ?>&page=<?= $page - 1 ?>" class="btn">← Précédent</a>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?mode=liste&search=<?= urlencode($search) ?>&mois=<?= $mois_filtre ?>&page=<?= $page + 1 ?>" class="btn">Suivant →</a>
        <?php endif; ?>
    </div>

<?php else: ?>
    <p style="color: #555;">Veuillez sélectionner un mode d’affichage.</p>
<?php endif; ?>

</body>
</html>
