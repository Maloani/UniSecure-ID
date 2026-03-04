<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'financier') {
    header('Location: login.php');
    exit;
}

require('fpdf/fpdf.php');

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$etudiants = $pdo->query("SELECT id_etudiant, nomcomplet, matricule FROM etudiants ORDER BY nomcomplet ASC")->fetchAll();

$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_etudiant'])) {
    $id_etudiant = trim($_POST['id_etudiant']);
    $montant = trim($_POST['montant']);
    $motif = trim($_POST['motif']);
    $date_paiement = date('Y-m-d H:i:s');

    if ($id_etudiant && $montant && $motif) {
        $stmt = $pdo->prepare("INSERT INTO paiements (id_etudiant, montant, date_paiement, motif) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_etudiant, $montant, $date_paiement, $motif]);
        $id_paiement = $pdo->lastInsertId();

        $etudiant = $pdo->prepare("SELECT nomcomplet, matricule FROM etudiants WHERE id_etudiant = ?");
        $etudiant->execute([$id_etudiant]);
        $e = $etudiant->fetch();

        $recuHtml = base64_encode(json_encode([
            'nomcomplet' => $e['nomcomplet'],
            'matricule' => $e['matricule'],
            'montant' => number_format($montant, 2),
            'motif' => $motif,
            'date' => $date_paiement
        ]));

        header("Location: financier_enregistrer.php?success=1&recu=$recuHtml");
        exit;
    } else {
        $error = "Veuillez remplir tous les champs requis.";
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM paiements WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: financier_enregistrer.php");
    exit;
}

if (isset($_GET['print'])) {
    $stmt = $pdo->prepare("SELECT p.*, e.nomcomplet, e.matricule FROM paiements p JOIN etudiants e ON p.id_etudiant = e.id_etudiant WHERE p.id = ?");
    $stmt->execute([$_GET['print']]);
    $data = $stmt->fetch();

    if ($data) {
        $recuData = base64_encode(json_encode([
            'nomcomplet' => $data['nomcomplet'],
            'matricule' => $data['matricule'],
            'montant' => number_format($data['montant'], 2),
            'motif' => $data['motif'],
            'date' => $data['date_paiement']
        ]));
        header("Location: financier_enregistrer.php?recu=$recuData");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT p.*, e.nomcomplet, e.matricule FROM paiements p JOIN etudiants e ON p.id_etudiant = e.id_etudiant WHERE e.nomcomplet LIKE :search OR e.matricule LIKE :search ORDER BY date_paiement DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$paiements = $stmt->fetchAll();

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM paiements p JOIN etudiants e ON p.id_etudiant = e.id_etudiant WHERE e.nomcomplet LIKE ? OR e.matricule LIKE ?");
$totalStmt->execute(["%$search%", "%$search%"]);
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $limit);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Paiements Étudiants</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            .recu-container {
                background-image: url('logo_filigrane.png');
                background-repeat: no-repeat;
                background-position: center;
                background-size: 300px;
                opacity: 1;
            }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

    <div class="flex justify-between items-center mb-4 no-print">
        <h1 class="text-2xl font-bold">Gestion des Paiements Étudiants</h1>
        <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Retour au Dashboard</a>
    </div>

    <?php if (isset($_GET['recu'])):
        $recuData = json_decode(base64_decode($_GET['recu']), true); ?>
        <div class="mb-4 text-green-700 font-semibold no-print">
            ✅ Reçu prêt. <button onclick="window.print();" class="ml-2 underline text-blue-600">Imprimer ce reçu</button>
        </div>
        <div class="recu-container bg-white p-6 mb-6 rounded shadow-md text-center">
            <img src="img/unilis.jpg" alt="Logo Université" class="mx-auto mb-2 w-20">
            <h2 class="text-xl font-bold">Université de Lisala</h2>
            <h3 class="text-lg font-semibold mb-4">Reçu de Paiement</h3>
            <p><strong>Nom complet :</strong> <?= htmlspecialchars($recuData['nomcomplet']) ?></p>
            <p><strong>Matricule :</strong> <?= htmlspecialchars($recuData['matricule']) ?></p>
            <p><strong>Montant :</strong> <?= htmlspecialchars($recuData['montant']) ?> $</p>
            <p><strong>Motif :</strong> <?= htmlspecialchars($recuData['motif']) ?></p>
            <p><strong>Date :</strong> <?= htmlspecialchars($recuData['date']) ?></p>
            <br><br>
            <p style="margin-top:50px;"><strong>Signature du Responsable</strong></p>
        </div>
    <?php endif; ?>

    <form method="post" class="bg-white p-4 rounded shadow-md mb-6 max-w-lg no-print">
        <select name="id_etudiant" class="w-full p-2 border mb-2 rounded" required>
            <option value="">-- Sélectionner un étudiant --</option>
            <?php foreach ($etudiants as $e): ?>
                <option value="<?= $e['id_etudiant'] ?>">
                    <?= htmlspecialchars($e['nomcomplet']) ?> (<?= htmlspecialchars($e['matricule']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" step="0.01" name="montant" placeholder="Montant" class="w-full p-2 border mb-2 rounded" required>
        <input type="text" name="motif" placeholder="Motif du paiement" class="w-full p-2 border mb-4 rounded" required>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Enregistrer</button>
    </form>

    <form method="get" class="mb-4 flex gap-2 no-print">
        <input type="text" name="search" placeholder="Recherche par nom ou matricule..." value="<?= htmlspecialchars($search) ?>" class="w-full p-2 border rounded">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Rechercher</button>
    </form>

    <div class="bg-white p-4 rounded shadow-md overflow-x-auto no-print">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Nom complet</th>
                    <th class="p-2 text-left">Matricule</th>
                    <th class="p-2 text-left">Montant</th>
                    <th class="p-2 text-left">Date Paiement</th>
                    <th class="p-2 text-left">Motif</th>
                    <th class="p-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paiements)): ?>
                    <tr><td colspan="6" class="p-2 text-center">Aucun paiement trouvé.</td></tr>
                <?php endif; ?>
                <?php foreach ($paiements as $p): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2"><?= htmlspecialchars($p['nomcomplet']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($p['matricule']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($p['montant']) ?> $</td>
                        <td class="p-2"><?= htmlspecialchars($p['date_paiement']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($p['motif']) ?></td>
                        <td class="p-2 flex gap-2">
                            <a href="?print=<?= $p['id'] ?>" class="bg-green-600 text-white px-3 py-1 rounded text-sm">Imprimer</a>
                            <a href="modifier_paiement.php?id=<?= $p['id'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">Modifier</a>
                            <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Supprimer ce paiement ?')" class="bg-red-600 text-white px-3 py-1 rounded text-sm">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-center gap-4 no-print">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Précédent</a>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Suivant</a>
        <?php endif; ?>
    </div>

</body>
</html>
