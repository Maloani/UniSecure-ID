<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Récupérer enseignants et options
$enseignants = $pdo->query("SELECT * FROM enseignants ORDER BY nom_complet")->fetchAll(PDO::FETCH_ASSOC);
$options = $pdo->query("SELECT * FROM options ORDER BY nom_option")->fetchAll(PDO::FETCH_ASSOC);

// Traitement ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom_cours'] ?? '';
    $desc = $_POST['description'] ?? '';
    $id_ens = $_POST['id_enseignant'] ?? null;
    $id_opt = $_POST['id_option'] ?? null;
    $id_cours = $_POST['id_cours'] ?? '';

    if ($nom && $id_ens && $id_opt) {
        if ($id_cours) {
            $stmt = $pdo->prepare("UPDATE cours SET nom_cours = ?, description = ?, id_enseignant = ?, id_option = ? WHERE id_cours = ?");
            $stmt->execute([$nom, $desc, $id_ens, $id_opt, $id_cours]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cours (nom_cours, description, id_enseignant, id_option) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $desc, $id_ens, $id_opt]);
        }
        header("Location: agent_cours.php");
        exit;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM cours WHERE id_cours = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: agent_cours.php");
    exit;
}

// Pré-remplissage pour modification
$edit_cours = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM cours WHERE id_cours = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_cours = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Filtrage par option
$filtre_option = $_GET['filtre_option'] ?? '';
$whereOption = '';
$params = [];

if ($filtre_option !== '') {
    $whereOption = "WHERE c.id_option = ?";
    $params[] = $filtre_option;
}

// Liste des cours
$stmt = $pdo->prepare("
    SELECT c.id_cours, c.nom_cours, c.description, e.nom_complet AS enseignant, o.nom_option AS option_name
    FROM cours c
    LEFT JOIN enseignants e ON c.id_enseignant = e.id_enseignant
    LEFT JOIN options o ON c.id_option = o.id_option
    $whereOption
    ORDER BY c.nom_cours ASC
");
$stmt->execute($params);
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des cours</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 30px; }
        h2 { color: #333; }
        form { margin-bottom: 30px; background: white; padding: 20px; border-radius: 10px; }
        input, select, textarea { padding: 8px; width: 100%; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #eee; }
        .btn {
            padding: 8px 15px;
            background-color: chocolate;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover { background-color: #a0522d; }
        a.btn-danger {
            background-color: darkred;
            padding: 6px 10px;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        a.btn-danger:hover { background-color: red; }
        a.btn-edit {
            background-color: #2c3e50;
            padding: 6px 10px;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        a.btn-edit:hover { background-color: #34495e; }
        .filter-bar {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
  <div class="top-link">
        <a href="dashboard.php" class="btn">← Retour au tableau de bord</a>
    </div>
<h2><?= $edit_cours ? "Modifier un cours" : "Ajouter un cours" ?></h2>

<form method="POST">
    <input type="hidden" name="id_cours" value="<?= $edit_cours['id_cours'] ?? '' ?>">

    <label>Nom du cours</label>
    <input type="text" name="nom_cours" value="<?= htmlspecialchars($edit_cours['nom_cours'] ?? '') ?>" required>

    <label>Description</label>
    <textarea name="description"><?= htmlspecialchars($edit_cours['description'] ?? '') ?></textarea>

    <label>Attribuer à un enseignant</label>
    <select name="id_enseignant" required>
        <option value="">-- Choisir un enseignant --</option>
        <?php foreach ($enseignants as $ens): ?>
            <option value="<?= $ens['id_enseignant'] ?>" <?= (isset($edit_cours['id_enseignant']) && $edit_cours['id_enseignant'] == $ens['id_enseignant']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ens['nom_complet']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Attribuer à une option</label>
    <select name="id_option" required>
        <option value="">-- Choisir une option --</option>
        <?php foreach ($options as $opt): ?>
            <option value="<?= $opt['id_option'] ?>" <?= (isset($edit_cours['id_option']) && $edit_cours['id_option'] == $opt['id_option']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($opt['nom_option']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn"><?= $edit_cours ? "Mettre à jour" : "Enregistrer" ?></button>
    <?php if ($edit_cours): ?>
        <a href="agent_cours.php" class="btn">Annuler</a>
    <?php endif; ?>
</form>

<h2>Liste des cours enregistrés</h2>

<!-- Barre de filtre par option -->
<div class="filter-bar">
    <form method="GET">
        <label for="filtre_option">Filtrer par option :</label>
        <select name="filtre_option" onchange="this.form.submit()" style="padding: 6px;">
            <option value="">-- Toutes les options --</option>
            <?php foreach ($options as $opt): ?>
                <option value="<?= $opt['id_option'] ?>" <?= ($filtre_option == $opt['id_option']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt['nom_option']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Nom du cours</th>
            <th>Description</th>
            <th>Enseignant</th>
            <th>Option</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cours as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['nom_cours']) ?></td>
                <td><?= nl2br(htmlspecialchars($c['description'])) ?></td>
                <td><?= htmlspecialchars($c['enseignant']) ?></td>
                <td><?= htmlspecialchars($c['option_name']) ?></td>
                <td>
                    <a href="?edit=<?= $c['id_cours'] ?>" class="btn-edit">Modifier</a>
                    <a href="?delete=<?= $c['id_cours'] ?>" class="btn-danger" onclick="return confirm('Supprimer ce cours ?');">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($cours)): ?>
            <tr><td colspan="5">Aucun cours trouvé pour ce filtre.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
