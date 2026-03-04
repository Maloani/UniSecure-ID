<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

if (!isset($_POST['id_etudiant'])) {
    die("ID de l'étudiant non fourni.");
}

$id = (int) $_POST['id_etudiant'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Mise à jour de l'état biométrique simulé (empreinte capturée)
    $stmt = $pdo->prepare("UPDATE etudiants SET statut_fingerprint = 'Capturé' WHERE id_etudiant = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Empreinte enregistrée avec succès.";
    header("Location: agent_etudiants2.php");
    exit;

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
