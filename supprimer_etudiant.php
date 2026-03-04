<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: agent_etudiants.php');
    exit;
}

$id = (int) $_GET['id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Récupérer les données de l'étudiant pour affichage
    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id_etudiant = ?");
    $stmt->execute([$id]);
    $etudiant = $stmt->fetch();

    if (!$etudiant) {
        echo "<p class='text-red-500'>Aucun étudiant trouvé avec cet ID.</p>";
        exit;
    }

    // Suppression validée par formulaire POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id_etudiant = ?");
        $stmt->execute([$id]);

        // Supprimer la photo si elle existe
        $photoPath = __DIR__ . "/app/UniSecure ID/photos_etudiants/" . $etudiant['photo'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }

        header("Location: agent_etudiants.php?success=1");
        exit;
    }

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression de l'étudiant</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
        <h1 class="text-xl font-bold text-red-600 mb-4">Confirmer la suppression</h1>
        <p class="mb-4">Voulez-vous vraiment supprimer l'étudiant suivant ?</p>

        <ul class="mb-6 text-gray-700">
            <li><strong>Nom :</strong> <?= htmlspecialchars($etudiant['nomcomplet']) ?></li>
            <li><strong>Matricule :</strong> <?= htmlspecialchars($etudiant['matricule']) ?></li>
            <li><strong>Sexe :</strong> <?= htmlspecialchars($etudiant['sexe']) ?></li>
            <li><strong>Département :</strong> <?= htmlspecialchars($etudiant['departement']) ?></li>
            <li><strong>Option :</strong> <?= htmlspecialchars($etudiant['options']) ?></li>
        </ul>

        <form method="post" class="flex justify-between">
            <a href="agent_etudiants.php" class="bg-gray-400 text-white px-4 py-2 rounded">Annuler</a>
            <button type="submit" name="confirm" value="1" class="bg-red-600 text-white px-4 py-2 rounded">Confirmer</button>
        </form>
    </div>
</body>
</html>
