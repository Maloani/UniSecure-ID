<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=examens.xls");

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

echo "<table border='1'>";
echo "<tr><th>Matière</th><th>Option</th><th>Date</th><th>Heure</th></tr>";
foreach ($data as $row) {
    echo "<tr>
            <td>{$row['nom_matiere']}</td>
            <td>{$row['nom_option']}</td>
            <td>{$row['date_examen']}</td>
            <td>{$row['heure_examen']}</td>
          </tr>";
}
echo "</table>";
