<?php
// capture_fingerprint.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("ID de l'étudiant manquant.");
}

$id = (int) $_GET['id'];

// Logique de capture à intégrer avec module Arduino (simulation ici)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Exemple : mise à jour du statut dans la base de données
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $pdo->prepare("UPDATE etudiants SET statut_fingerprint = 'capturé' WHERE id_etudiant = ?");
        $stmt->execute([$id]);

        header("Location: agent_biometrics.php");
        exit;
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Capture Empreinte</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-xl font-bold mb-4">Capture de l'empreinte digitale</h1>
    <form method="post">
        <p class="mb-4">Cliquez sur le bouton ci-dessous pour simuler la capture de l'empreinte digitale de l'étudiant #<?= $id ?>.</p>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Capturer l'empreinte</button>
        <a href="agent_biometrics.php" class="ml-4 text-red-600">Annuler</a>
    </form>
</body>
</html>
