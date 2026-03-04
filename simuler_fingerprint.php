<?php
$id = (int) $_GET['id'];
$path = 'empreintes/empreinte_' . $id . '.bin';
if (!file_exists('empreintes')) mkdir('empreintes');
file_put_contents($path, 'FINGERPRINT_SIMULATED');
$pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "");
$pdo->prepare("UPDATE etudiants SET statut_fingerprint = 'Capturé', empreinte_path = ? WHERE id_etudiant = ?")
    ->execute([$path, $id]);
header("Location: agent_etudiants2.php");
exit;
?>
