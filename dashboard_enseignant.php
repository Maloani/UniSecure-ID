<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'enseignant') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Enseignant</title>
</head>
<body>
    <h1>Bienvenue Enseignant, <?= htmlspecialchars($_SESSION['nom_complet']) ?></h1>
    <p>Vous pouvez gérer vos cours, notes et emploi du temps.</p>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>
