<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Paramètres statiques
$config = [
    'site_name' => 'Université de Lisala',
    'admin_email' => 'contact@universitedelisala.ac.cd',
    'school_year' => '2024-2025',
    'max_file_upload_mb' => 10,
    'maintenance_mode' => false,
    'language_default' => 'fr',
    'timezone' => 'Africa/Kinshasa',
    'campus_location' => 'Lisala, République Démocratique du Congo',
    'academic_calendar_url' => 'https://universitedelisala.ac.cd/calendrier-academique',
    'support_phone' => '+243 812 345 678',
    'logo_url' => 'img/unilis.jpg'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration du Système</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Configuration de l’Université</h1>
            <img src="<?= $config['logo_url'] ?>" alt="Logo" class="h-12">
        </div>

        <div class="space-y-3 text-sm">
            <p><strong>Nom du site :</strong> <?= $config['site_name'] ?></p>
            <p><strong>Email administrateur :</strong> <?= $config['admin_email'] ?></p>
            <p><strong>Téléphone support :</strong> <?= $config['support_phone'] ?></p>
            <p><strong>Année scolaire :</strong> <?= $config['school_year'] ?></p>
            <p><strong>Taille max. upload :</strong> <?= $config['max_file_upload_mb'] ?> Mo</p>
            <p><strong>Mode maintenance :</strong> <?= $config['maintenance_mode'] ? 'Activé' : 'Désactivé' ?></p>
            <p><strong>Langue par défaut :</strong> <?= strtoupper($config['language_default']) ?></p>
            <p><strong>Fuseau horaire :</strong> <?= $config['timezone'] ?></p>
            <p><strong>Campus :</strong> <?= $config['campus_location'] ?></p>
            <p><strong>Calendrier académique :</strong> 
                <a href="<?= $config['academic_calendar_url'] ?>" class="text-blue-600 underline" target="_blank">Voir le calendrier</a>
            </p>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="dashboard.php" class="text-blue-600 hover:underline">&larr; Retour au tableau de bord</a>
    </div>

</body>
</html>
