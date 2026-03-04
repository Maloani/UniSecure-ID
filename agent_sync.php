<?php
// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur de connexion : ' . $e->getMessage()]);
    exit;
}

// Détection du mode : pull (demande de données) ou push (envoi de données)
$mode = $_GET['mode'] ?? 'push';

// ------------------------
// MODE PULL (serveur -> local)
// ------------------------
if ($mode === 'pull') {
    $agents = $pdo->query("SELECT * FROM agents ORDER BY nom_complet ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'agents' => $agents]);
    exit;
}

// ------------------------
// MODE PUSH (local -> serveur)
// ------------------------
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['id_agent'], $input['nom_complet'], $input['email'], $input['statut'])) {
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes']);
    exit;
}

$id_agent    = htmlspecialchars($input['id_agent']);
$nom_complet = htmlspecialchars($input['nom_complet']);
$email       = htmlspecialchars($input['email']);
$statut      = htmlspecialchars($input['statut']);

// Vérifie si l'agent existe déjà
$stmt = $pdo->prepare("SELECT id_agent FROM agents WHERE id_agent = ?");
$stmt->execute([$id_agent]);

if ($stmt->rowCount() > 0) {
    // Mise à jour
    $update = $pdo->prepare("UPDATE agents SET nom_complet = ?, email = ?, statut = ? WHERE id_agent = ?");
    $update->execute([$nom_complet, $email, $statut, $id_agent]);
    echo json_encode(['status' => 'success', 'message' => 'Mise à jour réussie']);
} else {
    // Insertion
    $insert = $pdo->prepare("INSERT INTO agents (id_agent, nom_complet, email, statut) VALUES (?, ?, ?, ?)");
    $insert->execute([$id_agent, $nom_complet, $email, $statut]);
    echo json_encode(['status' => 'success', 'message' => 'Ajout réussi']);
}
?>
