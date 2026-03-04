<?php
$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$options = $pdo->query("SELECT * FROM options ORDER BY nom_option ASC")->fetchAll();
$selected = $_GET['id_option'] ?? null;

if ($selected) {
    $stmt = $pdo->prepare("
        SELECT * FROM examens 
        WHERE id_option = ? 
        ORDER BY date_examen, heure_examen
    ");
    $stmt->execute([$selected]);
    $examens = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planning par Option</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white p-6">
    <h1 class="text-2xl font-bold mb-4">Planning des Examens par Option</h1>

    <!-- Bouton retour -->
    <a href="agent_examens.php" class="inline-block bg-gray-500 text-white px-4 py-2 mb-4 rounded hover:bg-gray-600">
        ⬅️ Retour à la gestion des examens
    </a>

    <!-- Sélection de l’option -->
    <form method="get" class="mb-4">
        <label class="font-medium">Choisir une option :</label>
        <select name="id_option" class="border p-2 rounded" onchange="this.form.submit()">
            <option value="">-- Sélectionnez --</option>
            <?php foreach ($options as $opt): ?>
                <option value="<?= $opt['id_option'] ?>" <?= $opt['id_option'] == $selected ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt['nom_option']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Résultats -->
    <?php if ($selected): ?>
        <?php if (empty($examens)): ?>
            <p>Aucun examen programmé pour cette option.</p>
        <?php else: ?>
            <table class="w-full border text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">Cours</th>
                        <th class="border p-2">Date</th>
                        <th class="border p-2">Heure</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($examens as $ex): ?>
                        <tr>
                            <td class="border p-2"><?= htmlspecialchars($ex['nom_matiere']) ?></td>
                            <td class="border p-2"><?= $ex['date_examen'] ?></td>
                            <td class="border p-2"><?= $ex['heure_examen'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>

