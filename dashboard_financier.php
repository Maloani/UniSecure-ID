<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'financier') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Financier</title>
</head>
<body>
    <h1>Bienvenue Financier, <?= htmlspecialchars($_SESSION['nom_complet']) ?></h1>
    <p>Vous avez accès aux paiements, reçus et états financiers.</p>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>
