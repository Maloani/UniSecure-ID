<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $etudiants = $pdo->query("SELECT * FROM etudiants ORDER BY nomcomplet ASC")->fetchAll();
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Étudiants</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <h1 class="text-2xl font-bold mb-4 text-gray-800">Liste des Étudiants pour la capture d'Empreinte digitale</h1>
	
	 <div class="mt-6">
        <a href="dashboard.php" class="text-blue-600 hover:underline">&larr; Retour au tableau de bord</a>
    </div>
    <div class="mb-6">
        <input type="text" id="searchInput" placeholder="Rechercher un nom, matricule ou téléphone..." class="w-full md:w-1/2 p-2 border rounded shadow-sm">
    </div>

    <div class="overflow-x-auto bg-white p-4 rounded shadow-md">
        <table id="etudiantTable" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left text-sm font-semibold">Nom complet</th>
                    <th class="p-3 text-left text-sm font-semibold">Sexe</th>
                   
                    <th class="p-3 text-left text-sm font-semibold">Matricule</th>
                  
                    <th class="p-3 text-left text-sm font-semibold">Photo</th>
                   
					<th class="p-3 text-left font-semibold text-sm">Empreinte</th>
					<th class="p-3 text-left font-semibold text-sm">Biométrie</th>
                
					

                </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-gray-100">
                <?php foreach ($etudiants as $e): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 text-sm"><?= htmlspecialchars($e['nomcomplet']) ?></td>
                        <td class="p-3 text-sm"><?= htmlspecialchars($e['sexe']) ?></td>
                      
                        <td class="p-3 text-sm"><?= htmlspecialchars($e['matricule']) ?></td>
                      
                        <td class="p-3">
                            <img src="app/UniSecure ID/photos_etudiants/<?= htmlspecialchars($e['photo']) ?>" class="w-16 h-16 object-cover rounded">
                        </td>
						
						<td class="p-3 text-sm text-gray-700">
							<?php if ($e['statut_fingerprint'] === 'Capturé'): ?>
								<span class="text-green-600 font-semibold">✔ Capturé</span>
							<?php else: ?>
								<span class="text-red-600 font-semibold">✘ Non capturé</span>
							<?php endif; ?>
						</td>

						<td class="p-3">
							<?php if ($e['statut_fingerprint'] === 'Non capturé'): ?>
								<a href="agent_biometrics.php?id=<?= $e['id_etudiant'] ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-sm">Capturer</a>
							<?php else: ?>
								<a href="agent_biometrics.php?id=<?= $e['id_etudiant'] ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">Revoir</a>
							<?php endif; ?>
						</td>

                       
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center">
        <button id="prevBtn" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">← Précédent</button>
        <button id="nextBtn" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Suivant →</button>
    </div>

   

<script>
const rowsPerPage = 10;
let currentPage = 1;
const tableBody = document.getElementById('tableBody');
const allRows = [...tableBody.rows];
const searchInput = document.getElementById('searchInput');

function renderTable() {
    const search = searchInput.value.toLowerCase();
    let filteredRows = allRows.filter(row =>
        row.innerText.toLowerCase().includes(search)
    );

    const start = (currentPage - 1) * rowsPerPage;
    const paginated = filteredRows.slice(start, start + rowsPerPage);

    tableBody.innerHTML = "";
    paginated.forEach(row => tableBody.appendChild(row));
}

document.getElementById('prevBtn').addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        renderTable();
    }
});
document.getElementById('nextBtn').addEventListener('click', () => {
    const search = searchInput.value.toLowerCase();
    const totalRows = allRows.filter(row => row.innerText.toLowerCase().includes(search)).length;
    if (currentPage * rowsPerPage < totalRows) {
        currentPage++;
        renderTable();
    }
});
searchInput.addEventListener('input', () => {
    currentPage = 1;
    renderTable();
});

renderTable();
</script>

</body>
</html>
