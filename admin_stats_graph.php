<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $hommes = $pdo->query("SELECT COUNT(*) FROM etudiants WHERE sexe = 'Masculin'")->fetchColumn();
    $femmes = $pdo->query("SELECT COUNT(*) FROM etudiants WHERE sexe = 'Féminin'")->fetchColumn();
    $departements = $pdo->query("SELECT departement, COUNT(*) as total FROM etudiants GROUP BY departement")->fetchAll(PDO::FETCH_ASSOC);
    $motifs = $pdo->query("SELECT motif, COUNT(*) as total FROM paiements GROUP BY motif")->fetchAll(PDO::FETCH_ASSOC);
    $inscriptions = $pdo->query("SELECT DATE_FORMAT(date_enregistrement, '%Y-%m') as mois, COUNT(*) as total FROM etudiants GROUP BY mois ORDER BY mois ASC")->fetchAll(PDO::FETCH_ASSOC);
    $presences = $pdo->query("SELECT DATE_FORMAT(date_heure, '%Y-%m') as mois, COUNT(*) as total FROM presences GROUP BY mois ORDER BY mois ASC")->fetchAll(PDO::FETCH_ASSOC);
    $inscriptionsParSexe = $pdo->query("SELECT departement, sexe, COUNT(*) as total FROM etudiants GROUP BY departement, sexe")->fetchAll(PDO::FETCH_ASSOC);
    $paiements = $pdo->query("SELECT DATE(date_paiement) as date, SUM(montant) as total FROM paiements GROUP BY date ORDER BY date ASC")->fetchAll(PDO::FETCH_ASSOC);
    $postes = $pdo->query("SELECT poste, COUNT(*) as total FROM personnels GROUP BY poste")->fetchAll(PDO::FETCH_ASSOC);
    $roles = $pdo->query("SELECT role, COUNT(*) as total FROM users GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Graphiques statistiques</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-6">Graphiques des statistiques avancées</h1>

<div class="mt-6 space-x-4">
       
        <a href="admin_stats.php" class="text-indigo-600 hover:underline font-semibold">Voir les statistiques</a>
		
    
    </div>
	</br></br>
    <div class="flex justify-end mb-4 gap-4">
        <button onclick="downloadChartsAsImages()" class="bg-blue-600 text-white px-4 py-2 rounded">📥 Télécharger tous les graphiques</button>
        <button onclick="generatePDF()" class="bg-red-600 text-white px-4 py-2 rounded">📄 Générer un rapport PDF</button>
       
    </div>

    <div id="chartContainer" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="font-semibold mb-2">Genre des étudiants</h2>
            <canvas id="sexChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="font-semibold mb-2">Par département</h2>
            <canvas id="depChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="font-semibold mb-2">Inscriptions par mois</h2>
            <canvas id="inscriptionChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="font-semibold mb-2">Présences du personnel par mois</h2>
            <canvas id="presenceChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="font-semibold mb-2">Inscriptions par sexe et département</h2>
            <canvas id="sexDeptChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="font-semibold mb-2">Paiements journaliers</h2>
            <canvas id="paiementChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="font-semibold mb-2">Personnels par poste</h2>
            <canvas id="posteChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="font-semibold mb-2">Utilisateurs par rôle</h2>
            <canvas id="roleChart"></canvas>
        </div>
    </div>

    <script>
        function downloadChartsAsImages() {
            const charts = document.querySelectorAll('canvas');
            charts.forEach((canvas, index) => {
                const link = document.createElement('a');
                link.download = `chart_${index + 1}.png`;
                link.href = canvas.toDataURL();
                link.click();
            });
        }

        function generatePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            const chartArea = document.getElementById('chartContainer');

            html2canvas(chartArea, { scale: 2 }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const imgProps = doc.getImageProperties(imgData);
                const pdfWidth = doc.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                doc.setFontSize(16);
                doc.text("Université de Lisala - Rapport Statistique", 105, 15, { align: 'center' });
                const date = new Date().toLocaleDateString();
                doc.setFontSize(10);
                doc.text("Date : " + date, 200, 20, { align: 'right' });

                doc.addImage(imgData, 'PNG', 10, 30, pdfWidth - 20, pdfHeight);
                doc.save('rapport_statistiques.pdf');
            });
        }

        function sendPDFbyEmail() {
            alert("📧 Cette fonctionnalité nécessite une implémentation côté serveur (PHP mailer ou SMTP)");
        }
    </script>

    <script>
        new Chart(sexChart, {
            type: 'doughnut',
            data: {
                labels: ['Hommes', 'Femmes'],
                datasets: [{ data: [<?= $hommes ?>, <?= $femmes ?>], backgroundColor: ['#10b981', '#ec4899'] }]
            }
        });

        new Chart(depChart, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($departements, 'departement')) ?>,
                datasets: [{ data: <?= json_encode(array_column($departements, 'total')) ?>, backgroundColor: '#3b82f6' }]
            }
        });

        new Chart(inscriptionChart, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($inscriptions, 'mois')) ?>,
                datasets: [{ data: <?= json_encode(array_column($inscriptions, 'total')) ?>, label: 'Inscriptions', borderColor: '#6366f1', fill: false }]
            }
        });

        new Chart(presenceChart, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($presences, 'mois')) ?>,
                datasets: [{ label: 'Présences', data: <?= json_encode(array_column($presences, 'total')) ?>, borderColor: '#0ea5e9', fill: false }]
            }
        });

        new Chart(sexDeptChart, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_values(array_unique(array_column($inscriptionsParSexe, 'departement')))) ?>,
                datasets: [
                    {
                        label: 'Hommes',
                        data: <?= json_encode(array_values(array_map(function($d) { return $d['sexe'] === 'Masculin' ? $d['total'] : 0; }, $inscriptionsParSexe))) ?>,
                        backgroundColor: '#10b981'
                    },
                    {
                        label: 'Femmes',
                        data: <?= json_encode(array_values(array_map(function($d) { return $d['sexe'] === 'Féminin' ? $d['total'] : 0; }, $inscriptionsParSexe))) ?>,
                        backgroundColor: '#f472b6'
                    }
                ]
            },
            options: { responsive: true, stacked: true }
        });

        new Chart(paiementChart, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($paiements, 'date')) ?>,
                datasets: [{ label: 'Montant', data: <?= json_encode(array_column($paiements, 'total')) ?>, borderColor: '#f97316', fill: false }]
            }
        });

        new Chart(posteChart, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($postes, 'poste')) ?>,
                datasets: [{ data: <?= json_encode(array_column($postes, 'total')) ?>, backgroundColor: '#8b5cf6' }]
            }
        });

        new Chart(roleChart, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($roles, 'role')) ?>,
                datasets: [{ data: <?= json_encode(array_column($roles, 'total')) ?>, backgroundColor: ['#3b82f6','#10b981','#facc15','#f472b6','#a78bfa'] }]
            }
        });
    </script>
</body>
</html>
