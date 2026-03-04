<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Étudiant</title>
</head>
<body>
    <h1>Bienvenue Étudiant, <?= htmlspecialchars($_SESSION['nom_complet']) ?></h1>
    <p>Vous pouvez consulter vos cours, notes et emploi du temps.</p>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>
