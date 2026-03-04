<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'financier') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: financier_enregistrer.php');
    exit;
}

// Récupérer le paiement existant
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE id = ?");
$stmt->execute([$id]);
$paiement = $stmt->fetch();

if (!$paiement) {
    echo "<p>Paiement introuvable.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = trim($_POST['montant']);
    $motif = trim($_POST['motif']);
    if ($montant && $motif) {
        $update = $pdo->prepare("UPDATE paiements SET montant = ?, motif = ? WHERE id = ?");
        $update->execute([$montant, $motif, $id]);
        header("Location: financier_enregistrer.php?updated=1");
        exit;
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Paiement</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-xl mx-auto bg-white shadow-md p-6 rounded">
        <h1 class="text-xl font-bold mb-4">Modifier le Paiement</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-600 mb-2"><?= $error ?></p>
        <?php endif; ?>
        <form method="post">
            <div class="mb-4">
                <label class="block text-sm font-semibold">Montant</label>
                <input type="number" name="montant" value="<?= htmlspecialchars($paiement['montant']) ?>" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold">Motif</label>
                <input type="text" name="motif" value="<?= htmlspecialchars($paiement['motif']) ?>" class="w-full p-2 border rounded" required>
            </div>
            <div class="flex justify-between">
                <a href="financier_enregistrer.php" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Enregistrer</button>
            </div>
        </form>
    </div>
</body>
</html>
