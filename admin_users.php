<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$alert = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if (isset($_GET['toggle']) && isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $newStatus = $_GET['toggle'] === 'activer' ? 'activer' : 'Désactiver';
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        header("Location: admin_users.php");
        exit;
    }

    if (isset($_GET['delete']) && isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin_users.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['edit_id'])) {
            $stmt = $pdo->prepare("UPDATE users SET nom_complet = ?, username = ?, password = ?, role = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $_POST['nom_complet'],
                $_POST['username'],
                $_POST['password'],
                $_POST['role'],
                $_POST['status'],
                $_POST['edit_id']
            ]);
            $alert = "<div class='bg-yellow-100 text-yellow-700 p-3 rounded mb-4'>Utilisateur modifié avec succès.</div>";
        } else {
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $check->execute([$_POST['username']]);
            if ($check->fetchColumn() > 0) {
                $alert = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Nom d'utilisateur déjà existant.</div>";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nom_complet, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['username'],
                    $_POST['password'],
                    $_POST['role'],
                    $_POST['nom_complet'],
                    $_POST['status']
                ]);
                $alert = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Utilisateur ajouté avec succès.</div>";
            }
        }
    }

    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"/>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-6">Gestion des utilisateurs</h1>
	
	 <div class="mt-4">
        <a href="dashboard.php" class="text-blue-600 hover:underline">Retour au tableau de bord</a>
    </div>
    </br>

    <?php echo $alert; ?>

    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="mb-4 bg-blue-600 text-white px-4 py-2 rounded">+ Ajouter un utilisateur</button>

    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded w-full max-w-lg">
            <h2 class="text-xl font-semibold mb-4">Ajouter un utilisateur</h2>
            <form method="post" class="space-y-4">
                <input name="nom_complet" type="text" placeholder="Nom complet" required class="w-full border rounded px-3 py-2">
                <input name="username" type="text" placeholder="Nom d'utilisateur" required class="w-full border rounded px-3 py-2">
                <input name="password" type="text" placeholder="Mot de passe" required class="w-full border rounded px-3 py-2">
                <select name="role" required class="w-full border rounded px-3 py-2">
                    <option value="admin">Administrateur</option>
                    <option value="agent">Agent</option>
                    <option value="enseignant">Enseignant</option>
                    <option value="etudiant">Etudiant</option>
                    <option value="financier">Financier</option>
                </select>
                <select name="status" required class="w-full border rounded px-3 py-2">
                    <option value="activer">Activé</option>
                    <option value="Désactiver">Désactivé</option>
                </select>
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded">Annuler</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded w-full max-w-lg">
            <h2 class="text-xl font-semibold mb-4">Modifier un utilisateur</h2>
            <form method="post" class="space-y-4">
                <input type="hidden" name="edit_id" id="edit_id">
                <input name="nom_complet" id="edit_nom_complet" type="text" placeholder="Nom complet" required class="w-full border rounded px-3 py-2">
                <input name="username" id="edit_username" type="text" placeholder="Nom d'utilisateur" required class="w-full border rounded px-3 py-2">
                <input name="password" id="edit_password" type="text" placeholder="Mot de passe" required class="w-full border rounded px-3 py-2">
                <select name="role" id="edit_role" required class="w-full border rounded px-3 py-2">
                    <option value="admin">Administrateur</option>
                    <option value="agent">Agent</option>
                    <option value="enseignant">Enseignant</option>
                    <option value="etudiant">Etudiant</option>
                    <option value="financier">Financier</option>
                </select>
                <select name="status" id="edit_status" required class="w-full border rounded px-3 py-2">
                    <option value="activer">Activé</option>
                    <option value="Désactiver">Désactivé</option>
                </select>
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 border rounded">Annuler</button>
                    <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="usersTable" class="w-full bg-white rounded shadow">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-3 text-left">Nom complet</th>
                    <th class="p-3 text-left">Nom d'utilisateur</th>
                    <th class="p-3 text-left">Rôle</th>
                    <th class="p-3 text-left">Statut</th>
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="p-3"><?php echo htmlspecialchars($user['nom_complet']); ?></td>
                        <td class="p-3"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="p-3"><?php echo ucfirst($user['role']); ?></td>
                        <td class="p-3 flex items-center gap-2">
                            <?php if ($user['status'] === 'activer'): ?>
                                <span class="text-green-600 font-medium">✔️ Activé</span>
                            <?php else: ?>
                                <span class="text-red-600 font-medium">❌ Désactivé</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 flex gap-2">
                            <button class="bg-yellow-500 text-white px-3 py-1 rounded" onclick='openEditModal(<?php echo json_encode($user); ?>)'>Modifier</button>
                            <?php if ($user['status'] === 'activer'): ?>
                                <button onclick="confirmAction('Désactiver cet utilisateur ?', '?toggle=desactiver&id=<?php echo $user['id']; ?>')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Désactiver</button>
                            <?php else: ?>
                                <button onclick="confirmAction('Activer cet utilisateur ?', '?toggle=activer&id=<?php echo $user['id']; ?>')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">Activer</button>
                            <?php endif; ?>
                            <a href="?delete=true&id=<?php echo $user['id']; ?>" onclick="return confirm('Supprimer cet utilisateur ?');" class="bg-gray-500 text-white px-3 py-1 rounded">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
   

    <script>
        $(document).ready(function () {
            
            $('#usersTable').DataTable({
                dom: 'Bfrtip',
                stateSave: true,
                processing: true,
                deferRender: true,
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Liste_utilisateurs'
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Liste_utilisateurs'
                    },
                    {
                        extend: 'print',
                        title: 'Liste des utilisateurs'
                    }
                ]
            });
        });

        function confirmAction(message, link) {
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmLink').href = link;
            document.getElementById('confirmModal').classList.remove('hidden');
            document.getElementById('confirmModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            document.getElementById('confirmModal').classList.remove('flex');
        }

        function openEditModal(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_nom_complet').value = user.nom_complet;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_password').value = user.password;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.status;
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>

<script>
$(document).ready(function () {
    $('#usersTable').DataTable({
        destroy: true,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', title: 'Liste_utilisateurs' },
            { extend: 'csvHtml5', title: 'Liste_utilisateurs' },
            { extend: 'print', title: 'Liste des utilisateurs' }
        ]
    });
});
</script>
<!-- Modal de confirmation personnalisé -->
<div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-lg w-full max-w-sm text-center">
    <p id="confirmMessage" class="text-lg font-semibold mb-4"></p>
    <div class="flex justify-center gap-4">
      <a id="confirmLink" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" href="#">Oui</a>
      <button onclick="closeModal()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Non</button>
    </div>
  </div>
</div>




</body>
</html>
