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

$etudiants = $pdo->query("SELECT id_etudiant, nomcomplet, matricule FROM etudiants ORDER BY nomcomplet ASC")->fetchAll();

$id_etudiant = $_GET['id_etudiant'] ?? null;
$annee = $_GET['annee'] ?? '';
$motif = $_GET['motif'] ?? '';
$releves = [];

if ($id_etudiant) {
    $query = "SELECT * FROM paiements WHERE id_etudiant = ?";
    $params = [$id_etudiant];

    if ($annee) {
        $query .= " AND YEAR(date_paiement) = ?";
        $params[] = $annee;
    }
    if ($motif) {
        $query .= " AND motif LIKE ?";
        $params[] = "%$motif%";
    }
    $query .= " ORDER BY date_paiement DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $releves = $stmt->fetchAll();

    $etudiant_info = $pdo->prepare("SELECT * FROM etudiants WHERE id_etudiant = ?");
    $etudiant_info->execute([$id_etudiant]);
    $etudiant = $etudiant_info->fetch();

    if (isset($_GET['pdf']) && $etudiant && !empty($releves)) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'UNIVERSITE DE LISALA - RELEVE FINANCIER',0,1,'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,10,'Nom : ' . $etudiant['nomcomplet'],0,1);
        $pdf->Cell(0,10,'Matricule : ' . $etudiant['matricule'],0,1);
        $pdf->Cell(0,10,'Département : ' . $etudiant['departement'],0,1);
        $pdf->Cell(0,10,'Option : ' . $etudiant['options'],0,1);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(60,10,'Date',1);
        $pdf->Cell(80,10,'Motif',1);
        $pdf->Cell(40,10,'Montant ($)',1);
        $pdf->Ln();

        $pdf->SetFont('Arial','',12);
        $total = 0;
        foreach ($releves as $r) {
            $pdf->Cell(60,10, $r['date_paiement'],1);
            $pdf->Cell(80,10, $r['motif'],1);
            $pdf->Cell(40,10, number_format($r['montant'], 2),1,0,'R');
            $pdf->Ln();
            $total += $r['montant'];
        }
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(140,10,'Total paye',1);
        $pdf->Cell(40,10, number_format($total, 2),1,0,'R');

        $pdf->Output();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Relevé Financier Étudiant</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-4 flex justify-between items-center no-print">
            <h1 class="text-2xl font-bold">Relevé Financier Étudiant</h1>
            <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded">Retour</a>
        </div>

        <form method="get" class="mb-6 no-print grid grid-cols-1 md:grid-cols-4 gap-4">
            <select name="id_etudiant" class="p-2 border rounded" required>
                <option value="">-- Sélectionner un étudiant --</option>
                <?php foreach ($etudiants as $e): ?>
                    <option value="<?= $e['id_etudiant'] ?>" <?= ($e['id_etudiant'] == $id_etudiant ? 'selected' : '') ?>>
                        <?= htmlspecialchars($e['nomcomplet']) ?> (<?= htmlspecialchars($e['matricule']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="annee" placeholder="Année ex: 2025" value="<?= htmlspecialchars($annee) ?>" class="p-2 border rounded">
            <input type="text" name="motif" placeholder="Motif (ex: Inscription)" value="<?= htmlspecialchars($motif) ?>" class="p-2 border rounded">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Afficher</button>
        </form>

        <?php if (!empty($releves) && isset($etudiant)): ?>
            <div class="bg-white p-6 rounded shadow">
                <div class="mb-4">
                    <h2 class="text-xl font-bold">Informations Étudiant</h2>
                    <p><strong>Nom :</strong> <?= htmlspecialchars($etudiant['nomcomplet']) ?></p>
                    <p><strong>Matricule :</strong> <?= htmlspecialchars($etudiant['matricule']) ?></p>
                    <p><strong>Département :</strong> <?= htmlspecialchars($etudiant['departement']) ?></p>
                    <p><strong>Option :</strong> <?= htmlspecialchars($etudiant['options']) ?></p>
                </div>

                <h3 class="text-lg font-semibold mb-2">Relevé des paiements</h3>
                <table class="w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2 text-left">Date</th>
                            <th class="border p-2 text-left">Motif</th>
                            <th class="border p-2 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; foreach ($releves as $r): $total += $r['montant']; ?>
                            <tr>
                                <td class="border p-2"><?= htmlspecialchars($r['date_paiement']) ?></td>
                                <td class="border p-2"><?= htmlspecialchars($r['motif']) ?></td>
                                <td class="border p-2 text-right"><?= number_format($r['montant'], 2) ?> $</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="font-bold">
                            <td colspan="2" class="border p-2 text-right">Total payé</td>
                            <td class="border p-2 text-right"><?= number_format($total, 2) ?> $</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-6 text-center no-print">
                    <a href="?id_etudiant=<?= $id_etudiant ?>&annee=<?= $annee ?>&motif=<?= urlencode($motif) ?>&pdf=1" target="_blank" class="bg-green-600 text-white px-6 py-2 rounded">Télécharger en PDF</a>
                </div>
            </div>
        <?php elseif ($id_etudiant): ?>
            <div class="bg-yellow-100 text-yellow-800 p-4 rounded">Aucun paiement trouvé pour cet étudiant selon les filtres.</div>
        <?php endif; ?>
    </div>
</body>
</html>
