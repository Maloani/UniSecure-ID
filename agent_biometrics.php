<?php
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

    // Récupérer les infos de l’étudiant
    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id_etudiant = ?");
    $stmt->execute([$id]);
    $etudiant = $stmt->fetch();

    if (!$etudiant) {
        die("Étudiant introuvable.");
    }

    // Traitement de la capture simulée
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $empreinte_filename = 'empreinte_' . $id . '.bin';
        $empreinte_path = 'empreintes/' . $empreinte_filename;

        // S'assurer que le dossier existe
        $dossier_empreinte = __DIR__ . '/empreintes';
        if (!file_exists($dossier_empreinte)) {
            mkdir($dossier_empreinte, 0777, true);
        }

        // Créer un fichier simulé pour test
        file_put_contents(__DIR__ . '/' . $empreinte_path, 'DATA');

        // Mise à jour base de données
        $pdo->prepare("UPDATE etudiants SET statut_fingerprint = 'Capturé', empreinte_path = ? WHERE id_etudiant = ?")
            ->execute([$empreinte_path, $id]);

        header("Location: agent_etudiants2.php");
        exit;
    }

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Capture d'empreinte</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-bold mb-4 text-center">Capture d'empreinte digitale</h1>

        <div class="mb-4">
            <p><strong>Nom :</strong> <?= htmlspecialchars($etudiant['nomcomplet']) ?></p>
            <p><strong>Matricule :</strong> <?= htmlspecialchars($etudiant['matricule']) ?></p>
            <p><strong>Statut actuel :</strong>
                <?php if ($etudiant['statut_fingerprint'] === 'Capturé'): ?>
                    <span class="text-green-600 font-semibold">✔ Capturé</span>
                <?php else: ?>
                    <span class="text-red-600 font-semibold">✘ Non capturé</span>
                <?php endif; ?>
            </p>
        </div>

        <form method="post" >
    <input type="hidden" name="id_etudiant" value="<?= $etudiant['id_etudiant'] ?>">

            <p class="text-sm text-gray-600 mb-4">Placez le doigt de l'étudiant sur le capteur et cliquez sur le bouton ci-dessous.</p>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Capturer l'empreinte
            </button>
			<a href="simuler_fingerprint.php?id=<?= $id ?>" class="bg-gray-600 text-white px-3 py-1 rounded ml-4">
    Simuler capture sans capteur
</a>

            <a href="agent_etudiants2.php" class="ml-4 text-red-600 hover:underline">Annuler</a>
        </form>
    </div>
</body>
</html>
