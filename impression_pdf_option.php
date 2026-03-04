<?php
require('fpdf/fpdf.php');
$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$id_option = $_GET['id_option'] ?? null;
if (!$id_option) exit("ID option manquant.");

$stmt = $pdo->prepare("
    SELECT e.nom_matiere, e.date_examen, e.heure_examen, o.nom_option 
    FROM examens e 
    JOIN options o ON e.id_option = o.id_option 
    WHERE e.id_option = ?
    ORDER BY e.date_examen, e.heure_examen
");
$stmt->execute([$id_option]);
$examens = $stmt->fetchAll();

if (empty($examens)) exit("Aucun examen pour cette option.");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Planning des examens - Option : ' . $examens[0]['nom_option'], 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'COURS', 1);
$pdf->Cell(30, 10, 'Date', 1);
$pdf->Cell(30, 10, 'Heure', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($examens as $e) {
    $pdf->Cell(80, 10, $e['nom_matiere'], 1);
    $pdf->Cell(30, 10, $e['date_examen'], 1);
    $pdf->Cell(30, 10, $e['heure_examen'], 1);
    $pdf->Ln();
}

$pdf->Output('I', 'planning_' . $examens[0]['nom_option'] . '.pdf');
