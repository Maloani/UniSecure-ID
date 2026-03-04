<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $logs = $pdo->query("SELECT * FROM access_logs ORDER BY horodatage DESC")->fetchAll();

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Accès</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Historique des Accès</h1>

    <div class="mb-4">
        <a href="dashboard.php" class="text-blue-600 hover:underline">&larr; Retour au tableau de bord</a>
    </div>

    <div class="overflow-x-auto bg-white p-4 rounded shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left font-semibold text-sm">Nom</th>
                    <th class="p-3 text-left font-semibold text-sm">Rôle</th>
                    <th class="p-3 text-left font-semibold text-sm">Type d'accès</th>
                    <th class="p-3 text-left font-semibold text-sm">Point d'entrée</th>
                    <th class="p-3 text-left font-semibold text-sm">Horodatage</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($log['nom_complet']) ?></td>
                        <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($log['role']) ?></td>
                        <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($log['type_acces']) ?></td>
                        <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($log['point_entree']) ?></td>
                        <td class="p-3 text-sm text-gray-600"><?= htmlspecialchars($log['horodatage']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
