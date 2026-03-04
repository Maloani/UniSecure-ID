<?php
// FICHIER : carte_etudiant.php
session_start();

if (!isset($_GET['id'])) {
    die("ID étudiant manquant.");
}

$id = (int) $_GET['id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id_etudiant = ?");
    $stmt->execute([$id]);
    $etudiant = $stmt->fetch();

    if (!$etudiant) {
        die("Étudiant introuvable.");
    }

    require_once 'phpqrcode/qrlib.php';
    $qrDir = 'qr_codes/';
    if (!file_exists($qrDir)) mkdir($qrDir);

    $qrContent = "http://localhost/UniSecure%20ID/details_etudiant.php?id=" . $etudiant['id_etudiant'];
    $qrFile = $qrDir . 'etudiant_' . $etudiant['id_etudiant'] . '.png';
    QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 4);

} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Carte Étudiant</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white shadow-lg rounded-lg p-6 w-[400px] text-center relative border border-gray-300">
    <img src="img/unilis.jpg" alt="Logo" class="mx-auto h-16 mb-2">
    <h2 class="text-lg font-bold text-gray-800 mb-1">Université de Lisala</h2>
    <p class="text-sm text-gray-500 mb-4">Carte d'identité étudiant</p>

    <img src="app/UniSecure ID/photos_etudiants/<?= $etudiant['photo'] ?>" alt="Photo" class="w-24 h-24 object-cover rounded-full mx-auto border mb-4">

    <p><strong>Nom :</strong> <?= htmlspecialchars($etudiant['nomcomplet']) ?></p>
    <p><strong>Matricule :</strong> <?= $etudiant['matricule'] ?></p>
    <p><strong>Département :</strong> <?= $etudiant['departement'] ?></p>
    <p><strong>Option :</strong> <?= $etudiant['options'] ?></p>

    <div class="mt-4">
        <img src="<?= $qrFile ?>" alt="QR Code" class="mx-auto w-24 h-24">
        <p class="text-xs text-gray-500 mt-1">Scan pour vérifier</p>
    </div>

    <div class="mt-4 text-right text-xs italic text-gray-400">
        Cachet officiel
    </div>
</div>
</body>
</html>
