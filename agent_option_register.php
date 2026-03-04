<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Récupérer départements pour le <select>
$departements = $pdo->query("SELECT * FROM departements ORDER BY nom_departement ASC")->fetchAll();

// Recherche et pagination
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Enregistrement
// Enregistrement avec vérification de doublon
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_option = trim($_POST['nom_option']);
    $id_departement = $_POST['id_departement'];

    // Vérifier s’il existe déjà une option avec le même nom dans le même département
    $check = $pdo->prepare("SELECT COUNT(*) FROM options WHERE nom_option = ? AND id_departement = ?");
    $check->execute([$nom_option, $id_departement]);
    $exists = $check->fetchColumn();

    if ($exists) {
        $error = "⚠️ Cette option existe déjà pour ce département.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO options (nom_option, id_departement) VALUES (?, ?)");
        $stmt->execute([$nom_option, $id_departement]);
        header("Location: agent_option_register.php?success=1");
        exit;
    }
}


// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM options WHERE id_option = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: agent_option_register.php");
    exit;
}

// Liste paginée avec recherche
$stmt = $pdo->prepare("
    SELECT o.*, d.nom_departement 
    FROM options o 
    JOIN departements d ON o.id_departement = d.id_departement 
    WHERE o.nom_option LIKE :search 
    ORDER BY o.nom_option 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$options = $stmt->fetchAll();

// Compter total
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM options WHERE nom_option LIKE ?");
$totalStmt->execute(["%$search%"]);
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Options</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Gestion des Options</h1>
        <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Retour au Dashboard</a>
    </div>

    <!-- Formulaire ajout -->
    <form method="post" class="bg-white p-4 rounded shadow-md mb-6 max-w-lg">
        <input type="text" name="nom_option" placeholder="Nom de l'option" class="w-full p-2 border mb-2 rounded" required>
        <select name="id_departement" class="w-full p-2 border mb-4 rounded" required>
            <option value="">-- Sélectionner un département --</option>
            <?php foreach ($departements as $dep): ?>
                <option value="<?= $dep['id_departement'] ?>"><?= htmlspecialchars($dep['nom_departement']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Enregistrer</button>
    </form>

    <!-- Recherche -->
    <form method="get" class="mb-4 flex gap-2">
        <input type="text" name="search" placeholder="Rechercher une option..." value="<?= htmlspecialchars($search) ?>" class="w-full p-2 border rounded">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Rechercher</button>
    </form>

    <!-- Tableau -->
    <div class="bg-white p-4 rounded shadow-md overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Nom de l'option</th>
                    <th class="p-2 text-left">Département associé</th>
                    <th class="p-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($options)): ?>
                    <tr><td colspan="3" class="p-2 text-center">Aucune option trouvée.</td></tr>
                <?php endif; ?>

                <?php foreach ($options as $opt): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2"><?= htmlspecialchars($opt['nom_option']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($opt['nom_departement']) ?></td>
                        <td class="p-2 flex gap-2">
                            <a href="modifier_option.php?id=<?= $opt['id_option'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">Modifier</a>
                            <a href="?delete=<?= $opt['id_option'] ?>" onclick="return confirm('Supprimer cette option ?')" class="bg-red-600 text-white px-3 py-1 rounded text-sm">Supprimer</a>
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
