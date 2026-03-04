<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Agent</title>
</head>
<body>
    <h1>Bienvenue Agent, <?= htmlspecialchars($_SESSION['nom_complet']) ?></h1>
    <p>Vous avez accès aux opérations liées à la gestion quotidienne.</p>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>
