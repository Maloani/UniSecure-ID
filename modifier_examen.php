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
if (!$id) header("Location: agent_examens.php");

// Récupération de l'examen
$stmt = $pdo->prepare("SELECT * FROM examens WHERE id_examen = ?");
$stmt->execute([$id]);
$examen = $stmt->fetch();
if (!$examen) header("Location: agent_examens.php");

// Récupération des options
$options = $pdo->query("SELECT * FROM options ORDER BY nom_option ASC")->fetchAll();

// Mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE examens SET nom_matiere = ?, date_examen = ?, heure_examen = ?, id_option = ? WHERE id_examen = ?");
    $stmt->execute([
        $_POST['nom_matiere'],
        $_POST['date_examen'],
        $_POST['heure_examen'],
        $_POST['id_option'],
        $id
    ]);
    header("Location: agent_examens.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Examen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-100">
    <h1 class="text-2xl font-bold mb-4">Modifier un Examen</h1>
    <form method="post" class="bg-white p-4 rounded shadow max-w-lg">
        <label class="block mb-2 font-medium">Cours</label>
        <input type="text" name="nom_matiere" value="<?= htmlspecialchars($examen['nom_matiere']) ?>" class="w-full p-2 border mb-4 rounded" required>

        <label class="block mb-2 font-medium">Date</label>
        <input type="date" name="date_examen" value="<?= $examen['date_examen'] ?>" class="w-full p-2 border mb-4 rounded" required>

        <label class="block mb-2 font-medium">Heure</label>
        <input type="time" name="heure_examen" value="<?= $examen['heure_examen'] ?>" class="w-full p-2 border mb-4 rounded" required>

        <label class="block mb-2 font-medium">Option</label>
        <select name="id_option" class="w-full p-2 border mb-4 rounded" required>
            <?php foreach ($options as $opt): ?>
                <option value="<?= $opt['id_option'] ?>" <?= $opt['id_option'] == $examen['id_option'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt['nom_option']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="bg-green-600 text-white px-4 py-2 rounded">Mettre à jour</button>
        <a href="agent_examens.php" class="ml-2 bg-gray-400 text-white px-4 py-2 rounded">Annuler</a>
    </form>
</body>
</html>
