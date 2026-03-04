<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$options = $pdo->query("SELECT * FROM options ORDER BY nom_option ASC")->fetchAll();

// Recherche + pagination
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Enregistrement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_option = $_POST['id_option'];
    $nom_matiere = trim($_POST['nom_matiere']);
    $date_examen = $_POST['date_examen'];
    $heure_examen = $_POST['heure_examen'];

    // Vérifier s’il y a déjà un doublon exact
    $check = $pdo->prepare("SELECT COUNT(*) FROM examens WHERE id_option = ? AND nom_matiere = ? AND date_examen = ?");
    $check->execute([$id_option, $nom_matiere, $date_examen]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        $error = "⚠️ Cette matière est déjà programmée pour cette option à cette date.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO examens (id_option, nom_matiere, date_examen, heure_examen) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_option, $nom_matiere, $date_examen, $heure_examen]);
        header("Location: agent_examens.php?success=1");
        exit;
    }
}


// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM examens WHERE id_examen = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: agent_examens.php");
    exit;
}
$filtre_option = $_GET['filtre_option'] ?? '';
$filtre_date = $_GET['filtre_date'] ?? '';

// Liste des examens
$sql = "
    SELECT e.*, o.nom_option 
    FROM examens e
    JOIN options o ON e.id_option = o.id_option
    WHERE nom_matiere LIKE :search
";

if ($filtre_option) {
    $sql .= " AND e.id_option = :id_option";
}
if ($filtre_date) {
    $sql .= " AND e.date_examen = :date_examen";
}

$sql .= " ORDER BY date_examen DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if ($filtre_option) {
    $stmt->bindValue(':id_option', $filtre_option, PDO::PARAM_INT);
}
if ($filtre_date) {
    $stmt->bindValue(':date_examen', $filtre_date, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$examens = $stmt->fetchAll();

// Total
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM examens WHERE nom_matiere LIKE ?");
$totalStmt->execute(["%$search%"]);
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Programmation des Examens</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Programmation des Examens</h1>
        <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Retour au Dashboard</a>
    </div>

    <!-- Menu d'action -->
    <div class="flex gap-4 mb-6">
        <a href="export_examens_pdf.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">📄 Exporter PDF</a>
        <a href="export_examens_excel.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">📊 Exporter Excel</a>
        <a href="planning_par_option.php" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">🗓️ Planning par option</a>
    </div>
<?php if (isset($error)): ?>
    <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded mb-4"><?= $error ?></div>
<?php endif; ?>

    <!-- Message succès -->
    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">✅ Examen programmé avec succès.</div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded mb-4">✅ Examen modifié avec succès.</div>
    <?php endif; ?>

    <!-- Formulaire -->
    <form method="post" class="bg-white p-4 rounded shadow-md mb-6 max-w-lg">
        <label class="block mb-2">Option</label>
        <select name="id_option" class="w-full p-2 border rounded mb-3" required>
            <option value="">-- Sélectionner une option --</option>
            <?php foreach ($options as $opt): ?>
                <option value="<?= $opt['id_option'] ?>"><?= htmlspecialchars($opt['nom_option']) ?></option>
            <?php endforeach; ?>
        </select>

        <label class="block mb-2">Nom du cours</label>
        <input type="text" name="nom_matiere" class="w-full p-2 border rounded mb-3" required>

        <label class="block mb-2">Date de l'examen</label>
        <input type="date" name="date_examen" class="w-full p-2 border rounded mb-3" required>

        <label class="block mb-2">Heure de l'examen</label>
        <input type="time" name="heure_examen" class="w-full p-2 border rounded mb-3" required>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Programmer</button>
    </form>

    <!-- Recherche -->
    <form method="get" class="mb-4 flex gap-2">
        <input type="text" name="search" placeholder="Rechercher une matière..." value="<?= htmlspecialchars($search) ?>" class="w-full p-2 border rounded">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Rechercher</button>
    </form>
<form method="get" class="mb-4 flex flex-wrap gap-2 items-end">
    <input type="text" name="search" placeholder="Rechercher matière..." value="<?= htmlspecialchars($search) ?>" class="p-2 border rounded">

    <select name="filtre_option" class="p-2 border rounded">
        <option value="">-- Toutes les options --</option>
        <?php foreach ($options as $opt): ?>
            <option value="<?= $opt['id_option'] ?>" <?= $filtre_option == $opt['id_option'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($opt['nom_option']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="date" name="filtre_date" value="<?= $filtre_date ?>" class="p-2 border rounded">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filtrer</button>
</form>

    <!-- Tableau -->
    <div class="bg-white p-4 rounded shadow-md overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">COURS</th>
                    <th class="p-2 text-left">Option</th>
                    <th class="p-2 text-left">Date</th>
                    <th class="p-2 text-left">Heure</th>
                    <th class="p-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($examens)): ?>
                    <tr><td colspan="5" class="p-2 text-center">Aucun examen trouvé.</td></tr>
                <?php endif; ?>

                <?php foreach ($examens as $ex): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2"><?= htmlspecialchars($ex['nom_matiere']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($ex['nom_option']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($ex['date_examen']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($ex['heure_examen']) ?></td>
                        <td class="p-2 flex gap-2">
                            <a href="modifier_examen.php?id=<?= $ex['id_examen'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">Modifier</a>
                            <a href="?delete=<?= $ex['id_examen'] ?>" onclick="return confirm('Supprimer cet examen ?')" class="bg-red-600 text-white px-3 py-1 rounded text-sm">Supprimer</a>
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
