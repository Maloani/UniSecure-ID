<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if (!isset($_GET['id'])) {
    header("Location: agent_enseignants.php");
    exit;
}

$id = $_GET['id'];

// Mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE enseignants SET nom_complet = ?, telephone = ?, email = ? WHERE id_enseignant = ?");
    $stmt->execute([$_POST['nom_complet'], $_POST['telephone'], $_POST['email'], $id]);
    header("Location: agent_enseignants.php");
    exit;
}

// Charger données actuelles
$stmt = $pdo->prepare("SELECT * FROM enseignants WHERE id_enseignant = ?");
$stmt->execute([$id]);
$enseignant = $stmt->fetch();

if (!$enseignant) {
    header("Location: agent_enseignants.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Enseignant</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-4">Modifier Enseignant</h1>

    <!-- Formulaire Modification -->
    <form method="post" class="bg-white p-4 rounded shadow-md max-w-lg">
        <input type="text" name="nom_complet" value="<?= htmlspecialchars($enseignant['nom_complet']) ?>" class="w-full p-2 border mb-2 rounded" required>
        <input type="text" name="telephone" value="<?= htmlspecialchars($enseignant['telephone']) ?>" class="w-full p-2 border mb-2 rounded" required>
        <input type="email" name="email" value="<?= htmlspecialchars($enseignant['email']) ?>" class="w-full p-2 border mb-4 rounded" required>
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Sauvegarder</button>
        <a href="agent_enseignants.php" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Annuler</a>
    </form>
</body>
</html>
