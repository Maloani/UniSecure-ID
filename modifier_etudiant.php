<?php
// FICHIER : modifier_etudiant.php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("ID de l'étudiant manquant.");
}

$id = (int) $_GET['id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("UPDATE etudiants SET nomcomplet = ?, sexe = ?, telephone = ?, matricule = ?, departement = ?, options = ? WHERE id_etudiant = ?");
        $stmt->execute([
            $_POST['nomcomplet'],
            $_POST['sexe'],
            $_POST['telephone'],
            $_POST['matricule'],
            $_POST['departement'],
            $_POST['options'],
            $id
        ]);
        header("Location: agent_etudiants.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id_etudiant = ?");
    $stmt->execute([$id]);
    $etudiant = $stmt->fetch();

    if (!$etudiant) {
        die("Étudiant introuvable.");
    }

} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Étudiant</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-xl bg-white rounded shadow p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Modifier les informations de l'étudiant</h1>
        <form method="post" class="space-y-4">
            <div>
                <label class="block font-semibold">Nom complet :</label>
                <input type="text" name="nomcomplet" value="<?= htmlspecialchars($etudiant['nomcomplet']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block font-semibold">Sexe :</label>
                <select name="sexe" class="w-full border rounded px-3 py-2">
                    <option value="Masculin" <?= $etudiant['sexe'] === 'Masculin' ? 'selected' : '' ?>>Masculin</option>
                    <option value="Féminin" <?= $etudiant['sexe'] === 'Féminin' ? 'selected' : '' ?>>Féminin</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold">Téléphone :</label>
                <input type="text" name="telephone" value="<?= htmlspecialchars($etudiant['telephone']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block font-semibold">Matricule :</label>
                <input type="text" name="matricule" value="<?= htmlspecialchars($etudiant['matricule']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block font-semibold">Département :</label>
                <input type="text" name="departement" value="<?= htmlspecialchars($etudiant['departement']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block font-semibold">Option :</label>
                <input type="text" name="options" value="<?= htmlspecialchars($etudiant['options']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="flex justify-end gap-4">
                <a href="agent_etudiants.php" class="px-4 py-2 border rounded text-red-600">Annuler</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Enregistrer</button>
            </div>
        </form>
    </div>
</body>
</html>
