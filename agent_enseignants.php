<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$alert = "";

$search = $_GET['search'] ?? '';
$page = max($_GET['page'] ?? 1, 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtrage et pagination
$sql = "SELECT * FROM enseignants WHERE nom_complet LIKE :search ORDER BY nom_complet ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$enseignants = $stmt->fetchAll();

// Compter total pour pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM enseignants WHERE nom_complet LIKE :search");
$totalStmt->execute(['search' => "%$search%"]);
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO enseignants (nom_complet, telephone, email) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['nom_complet'], $_POST['telephone'], $_POST['email']]);
    header("Location: agent_enseignants.php");
    exit;
}

// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM enseignants WHERE id_enseignant = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: agent_enseignants.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Enseignants</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Gestion des Enseignants</h1>
        <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Retour au Dashboard</a>
    </div>

    <!-- Formulaire d'ajout -->
    <form method="post" class="bg-white p-4 rounded shadow-md mb-6 max-w-lg">
        <input type="text" name="nom_complet" placeholder="Nom complet" class="w-full p-2 border mb-2 rounded" required>
        <input type="text" name="telephone" placeholder="Téléphone" class="w-full p-2 border mb-2 rounded" required>
        <input type="email" name="email" placeholder="Email" class="w-full p-2 border mb-4 rounded" required>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Enregistrer</button>
    </form>

    <!-- Barre de recherche -->
    <form method="get" class="mb-4 flex gap-2">
        <input type="text" name="search" placeholder="Rechercher par nom..." value="<?= htmlspecialchars($search) ?>" class="w-full p-2 border rounded">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Rechercher</button>
    </form>

    <!-- Tableau des enseignants -->
    <div class="bg-white p-4 rounded shadow-md overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Nom complet</th>
                    <th class="p-2 text-left">Téléphone</th>
                    <th class="p-2 text-left">Email</th>
                    <th class="p-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($enseignants)): ?>
                    <tr>
                        <td colspan="4" class="p-2 text-center">Aucun enseignant trouvé.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($enseignants as $ens): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2"><?= htmlspecialchars($ens['nom_complet']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($ens['telephone']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($ens['email']) ?></td>
                        <td class="p-2 flex gap-2">
                            <a href="modifier_enseignant.php?id=<?= $ens['id_enseignant'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">Modifier</a>
                            <a href="?delete=<?= $ens['id_enseignant'] ?>" onclick="return confirm('Confirmer la suppression ?')" class="bg-red-600 text-white px-3 py-1 rounded text-sm">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-center gap-4">
        <?php if($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Précédent</a>
        <?php endif; ?>

        <?php if($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Suivant</a>
        <?php endif; ?>
    </div>
</body>
</html>
