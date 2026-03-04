<?php
session_start();
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$nom = htmlspecialchars($_SESSION['nom_complet']);

$menus = [
    'admin' => [
        ['label' => 'Gérer les utilisateurs', 'icon' => 'users', 'page' => 'admin_users.php'],
        ['label' => 'Voir les statistiques', 'icon' => 'bar-chart', 'page' => 'admin_stats.php'],
        ['label' => 'Configurer le système', 'icon' => 'cog', 'page' => 'admin_config.php']
    ],

  'agent' => [
    ['label' => '<i class="fas fa-fingerprint text-blue-600"></i> Enregistrement biométrique', 'page' => 'agent_etudiants2.php'],
    ['label' => '<i class="fas fa-users text-green-600"></i> Liste des étudiants', 'page' => 'agent_etudiants.php'],
    ['label' => '<i class="fas fa-chalkboard-teacher text-indigo-600"></i> Enregistrer les enseignants', 'page' => 'agent_enseignants.php'],
   
    ['label' => '<i class="fas fa-building text-amber-700"></i> Enregistrer un département', 'page' => 'agent_departement_register.php'],
    ['label' => '<i class="fas fa-th-list text-emerald-600"></i> Enregistrer une option', 'page' => 'agent_option_register.php'],
	 ['label' => '<i class="fas fa-book-medical text-purple-600"></i> Enregistrer un cours', 'page' => 'agent_cours.php'],

    ['label' => '<i class="fas fa-calendar-plus text-pink-600"></i> Programmer les examens', 'page' => 'agent_examens.php'],
    ['label' => '<i class="fas fa-calendar-day text-teal-600"></i> Visualiser les présences', 'page' => 'agent_presences.php'],
 
],



    'enseignant' => [
        ['label' => 'Mes cours', 'icon' => 'book-open', 'page' => 'enseignant_cours.php'],
        ['label' => 'Saisir les présences', 'icon' => 'calendar-check', 'page' => 'enseignant_presences.php'],
        ['label' => 'Attribuer les notes', 'icon' => 'clipboard-list', 'page' => 'enseignant_notes.php'],
        ['label' => 'Consulter les étudiants', 'icon' => 'user-graduate', 'page' => 'enseignant_etudiants.php']
    ],

    'etudiant' => [
        ['label' => 'Mon espace académique', 'icon' => 'book', 'page' => 'etudiant_dashboard.php'],
        ['label' => 'Historique des présences', 'icon' => 'calendar-alt', 'page' => 'etudiant_presences.php'],
        ['label' => 'Mes notes', 'icon' => 'clipboard', 'page' => 'etudiant_notes.php'],
        ['label' => 'Mes paiements', 'icon' => 'money-check-alt', 'page' => 'etudiant_paiements.php']
    ],

    'financier' => [
        ['label' => 'Enregistrer un paiement', 'icon' => 'cash-register', 'page' => 'financier_enregistrer.php'],
        ['label' => 'Relevés par étudiant', 'icon' => 'file-invoice-dollar', 'page' => 'financier_releves.php'],
        ['label' => 'Statistiques financières', 'icon' => 'chart-line', 'page' => 'financier_stats.php']
    ]
];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - <?php echo ucfirst($role); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body class="bg-gray-100">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="bg-white w-64 md:w-64 shadow-lg hidden md:block">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Bienvenue <?php echo ucfirst($role); ?></h2>
            <p class="text-sm text-gray-500"><?php echo $nom; ?></p>
        </div>
        <nav class="mt-4">
            <ul class="space-y-2 px-4">
                <?php foreach ($menus[$role] as $item): ?>
						<li>
							<a href="<?php echo $item['page']; ?>" class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700">
								<?php if (isset($item['icon'])): ?>
									<i data-lucide="<?= $item['icon'] ?>" class="w-5 h-5"></i>
									<span><?= htmlspecialchars($item['label']) ?></span>
								<?php else: ?>
									<?= $item['label'] ?>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>

                <li>
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-2 rounded text-red-600 hover:bg-red-100">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        Se déconnecter
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Mobile menu toggle -->
    <div class="md:hidden p-4">
        <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="bg-white px-4 py-2 rounded shadow">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
    </div>

    <!-- Mobile Sidebar -->
    <aside id="mobileMenu" class="bg-white w-full absolute top-0 left-0 p-4 shadow-lg z-10 hidden">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800"><?php echo ucfirst($role); ?></h2>
                <p class="text-sm text-gray-500"><?php echo $nom; ?></p>
            </div>
            <button onclick="document.getElementById('mobileMenu').classList.add('hidden')">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <ul class="space-y-2">
            <?php foreach ($menus[$role] as $item): ?>
    <li>
        <a href="<?php echo $item['page']; ?>" class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700">
            <?php if (isset($item['icon'])): ?>
                <i data-lucide="<?= $item['icon'] ?>" class="w-5 h-5"></i>
                <span><?= htmlspecialchars($item['label']) ?></span>
            <?php else: ?>
                <?= $item['label'] ?>
            <?php endif; ?>
        </a>
    </li>
<?php endforeach; ?>

            <li>
                <a href="logout.php" class="flex items-center gap-3 px-4 py-2 rounded text-red-600 hover:bg-red-100">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    Se déconnecter
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            Tableau de bord - <?php echo ucfirst($role); ?>
        </h1>
        <p class="text-gray-600">
            Utilisez le menu pour accéder à vos fonctionnalités.
        </p>
    </main>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>
