<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: agent_departement_register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE departements SET nom_departement=? WHERE id_departement=?");
    $stmt->execute([$_POST['nom_departement'], $id]);
    header("Location: agent_departement_register.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM departements WHERE id_departement=?");
$stmt->execute([$id]);
$dep = $stmt->fetch();

if (!$dep) {
    header("Location: agent_departement_register.php");
    exit;
}
?>

<!-- HTML Modification -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Département</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <form method="post" class="bg-white p-4 rounded shadow-md max-w-lg">
        <input type="text" name="nom_departement" value="<?= htmlspecialchars($dep['nom_departement']) ?>" class="w-full p-2 border mb-4 rounded" required>
        <button class="bg-green-600 text-white px-4 py-2 rounded">Modifier</button>
        <a href="agent_departement_register.php" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded">Annuler</a>
    </form>
</body>
</html>
