<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Enregistrement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO departements (nom_departement) VALUES (?)");
    $stmt->execute([$_POST['nom_departement']]);
    header("Location: agent_departement_register.php");
    exit;
}

// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM departements WHERE id_departement = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: agent_departement_register.php");
    exit;
}

// Récupération paginée avec recherche
$stmt = $pdo->prepare("SELECT * FROM departements WHERE nom_departement LIKE :search ORDER BY nom_departement LIMIT :limit OFFSET :offset");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$departements = $stmt->fetchAll();

// Compte total pour pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM departements WHERE nom_departement LIKE ?");
$totalStmt->execute(["%$search%"]);
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Départements</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Gestion des Départements</h1>
        <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Retour au Dashboard</a>
    </div>

    <!-- Formulaire ajout -->
    <form method="post" class="bg-white p-4 rounded shadow-md mb-6 max-w-lg">
        <input type="text" name="nom_departement" placeholder="Nom du département" class="w-full p-2 border mb-2 rounded" required>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Enregistrer</button>
    </form>

    <!-- Recherche -->
    <form method="get" class="mb-4 flex gap-2">
        <input type="text" name="search" placeholder="Rechercher un département..." value="<?= htmlspecialchars($search) ?>" class="w-full p-2 border rounded">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Rechercher</button>
    </form>

    <!-- Tableau des départements -->
    <div class="bg-white p-4 rounded shadow-md overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Nom du département</th>
                    <th class="p-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($departements)): ?>
                    <tr>
                        <td colspan="2" class="p-2 text-center">Aucun département trouvé.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($departements as $dep): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2"><?= htmlspecialchars($dep['nom_departement']) ?></td>
                        <td class="p-2 flex gap-2">
                            <a href="modifier_departement.php?id=<?= $dep['id_departement'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">Modifier</a>
                            <a href="?delete=<?= $dep['id_departement'] ?>" onclick="return confirm('Supprimer ce département ?')" class="bg-red-600 text-white px-3 py-1 rounded text-sm">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-center gap-4">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Précédent</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Suivant</a>
        <?php endif; ?>
    </div>

</body>
</html>
