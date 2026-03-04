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
    <title>Choix du type de présence</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f3;
            text-align: center;
            padding: 50px;
        }
        h1 {
            color: #333;
        }
        .btn-container {
            margin-top: 40px;
        }
        .btn {
            background-color: chocolate;
            color: white;
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            margin: 20px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #a0522d;
        }
        .top-link {
            position: absolute;
            top: 20px;
            left: 20px;
        }
    </style>
</head>
<body>

    <div class="top-link">
        <a href="dashboard.php" class="btn">← Retour au tableau de bord</a>
    </div>

    <h1>Consulter les statistiques de présences</h1>
    <p>Veuillez choisir le groupe dont vous souhaitez consulter les présences :</p>

    <div class="btn-container">
        <a href="statistiques_personnel.php" class="btn">Personnel (Agents)</a>
        <a href="statistiques_etudiants.php" class="btn">Étudiants</a>
    </div>

</body>
</html>
