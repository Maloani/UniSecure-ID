<?php
// FICHIER : details_etudiant.php

session_start();

if (!isset($_GET['id'])) {
    die("Identifiant de l'étudiant manquant.");
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
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'Étudiant</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Informations de l'étudiant</h1>
        <div class="flex items-center mb-4">
            <img src="app/UniSecure ID/photos_etudiants/<?php echo htmlspecialchars($etudiant['photo']); ?>" alt="Photo" class="w-32 h-32 object-cover rounded border mr-4">
            <div>
                <p><strong>Nom :</strong> <?= htmlspecialchars($etudiant['nomcomplet']) ?></p>
                <p><strong>Sexe :</strong> <?= htmlspecialchars($etudiant['sexe']) ?></p>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($etudiant['telephone']) ?></p>
                <p><strong>Matricule :</strong> <?= htmlspecialchars($etudiant['matricule']) ?></p>
                <p><strong>Département :</strong> <?= htmlspecialchars($etudiant['departement']) ?></p>
                <p><strong>Option :</strong> <?= htmlspecialchars($etudiant['options']) ?></p>
                <p><strong>Enregistré le :</strong> <?= htmlspecialchars($etudiant['date_enregistrement']) ?></p>
            </div>
        </div>
        <a href="javascript:window.print()" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded">Imprimer</a>
    </div>
</body>
</html>
