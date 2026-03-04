<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Administrateur</title>
</head>
<body>
    <h1>Bienvenue Administrateur, <?= htmlspecialchars($_SESSION['nom_complet']) ?></h1>
    <p>Vous avez un accès complet à toutes les fonctionnalités.</p>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>
