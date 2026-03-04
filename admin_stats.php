<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Statistiques étudiants
    $totalEtudiants = $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
    $hommes = $pdo->query("SELECT COUNT(*) FROM etudiants WHERE sexe = 'Masculin'")->fetchColumn();
    $femmes = $pdo->query("SELECT COUNT(*) FROM etudiants WHERE sexe = 'Féminin'")->fetchColumn();
    $departements = $pdo->query("SELECT departement, COUNT(*) as total FROM etudiants GROUP BY departement")->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques paiements
    $totalPaiements = $pdo->query("SELECT COUNT(*) FROM paiements")->fetchColumn();
    $sommePaiements = $pdo->query("SELECT SUM(montant) FROM paiements")->fetchColumn();

    // Statistiques personnels
    $totalPersonnels = $pdo->query("SELECT COUNT(*) FROM personnels")->fetchColumn();

    // Statistiques présences
    $totalPresences = $pdo->query("SELECT COUNT(*) FROM presences")->fetchColumn();

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques administrateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-6">Statistiques Institutionnelles</h1>
	
	<div class="mt-6 space-x-4">
        <a href="dashboard.php" class="text-blue-600 hover:underline">&larr; Retour au tableau de bord</a>
        <a href="admin_stats_graph.php" class="text-indigo-600 hover:underline font-semibold">Voir le graphique</a>
		
    
    </div>
	</br></br>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-lg font-semibold">Total étudiants</h2>
            <p class="text-3xl font-bold text-blue-700"><?= $totalEtudiants ?></p>
        </div>
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-lg font-semibold">Hommes</h2>
            <p class="text-3xl font-bold text-green-700"><?= $hommes ?></p>
        </div>
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-lg font-semibold">Femmes</h2>
            <p class="text-3xl font-bold text-pink-600"><?= $femmes ?></p>
        </div>
        <?php foreach ($departements as $dep): ?>
            <div class="bg-white shadow rounded p-6">
                <h2 class="text-lg font-semibold">Département : <?= htmlspecialchars($dep['departement']) ?></h2>
                <p class="text-2xl font-bold text-purple-600"><?= $dep['total'] ?></p>
            </div>
        <?php endforeach; ?>

        <div class="bg-white shadow rounded p-6">
            <h2 class="text-lg font-semibold">Paiements enregistrés</h2>
            <p class="text-3xl font-bold text-yellow-700"><?= $totalPaiements ?></p>
        </div>
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-lg font-semibold">Montant total payé</h2>
            <p class="text-3xl font-bold text-orange-600">
                <?= number_format($sommePaiements, 2, ',', ' ') ?> FCFA
            </p>
        </div>

        <div class="bg-white shadow rounded p-6">
            <h2 class="text-lg font-semibold">Personnel enregistré</h2>
            <p class="text-3xl font-bold text-gray-800"><?= $totalPersonnels ?></p>
        </div>
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-lg font-semibold">Présences enregistrées</h2>
            <p class="text-3xl font-bold text-teal-600"><?= $totalPresences ?></p>
        </div>
    </div>

    
</body>
</html>
