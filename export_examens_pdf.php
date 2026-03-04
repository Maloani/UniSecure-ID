<?php
require('fpdf/fpdf.php');
$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdo->query("
    SELECT e.nom_matiere, e.date_examen, e.heure_examen, o.nom_option 
    FROM examens e 
    JOIN options o ON e.id_option = o.id_option 
    ORDER BY o.nom_option, e.date_examen
");
$data = $stmt->fetchAll();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Planning des examens', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, 'Cours', 1);
$pdf->Cell(50, 10, 'Option', 1);
$pdf->Cell(30, 10, 'Date', 1);
$pdf->Cell(30, 10, 'Heure', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($data as $row) {
    $pdf->Cell(60, 10, $row['nom_matiere'], 1);
    $pdf->Cell(50, 10, $row['nom_option'], 1);
    $pdf->Cell(30, 10, $row['date_examen'], 1);
    $pdf->Cell(30, 10, $row['heure_examen'], 1);
    $pdf->Ln();
}

$pdf->Output('D', 'examens.pdf');
