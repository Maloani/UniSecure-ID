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
    header("Location: agent_option_register.php");
    exit;
}

// Récupération de l’option à modifier
$stmt = $pdo->prepare("SELECT * FROM options WHERE id_option = ?");
$stmt->execute([$id]);
$option = $stmt->fetch();

if (!$option) {
    header("Location: agent_option_register.php");
    exit;
}

// Récupérer tous les départements
$departements = $pdo->query("SELECT * FROM departements ORDER BY nom_departement ASC")->fetchAll();

// Mise à jour après soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE options SET nom_option = ?, id_departement = ? WHERE id_option = ?");
    $stmt->execute([$_POST['nom_option'], $_POST['id_departement'], $id]);
    header("Location: agent_option_register.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une option</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="mb-4">
        <h1 class="text-2xl font-bold">Modifier l'option</h1>
    </div>

    <form method="post" class="bg-white p-4 rounded shadow-md max-w-lg">
        <label class="block mb-2 font-medium">Nom de l'option</label>
        <input type="text" name="nom_option" value="<?= htmlspecialchars($option['nom_option']) ?>" class="w-full p-2 border mb-4 rounded" required>

        <label class="block mb-2 font-medium">Département associé</label>
        <select name="id_departement" class="w-full p-2 border mb-4 rounded" required>
            <option value="">-- Sélectionner un département --</option>
            <?php foreach ($departements as $dep): ?>
                <option value="<?= $dep['id_departement'] ?>" <?= $dep['id_departement'] == $option['id_departement'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dep['nom_departement']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Enregistrer les modifications</button>
            <a href="agent_option_register.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Annuler</a>
        </div>
    </form>

</body>
</html>
