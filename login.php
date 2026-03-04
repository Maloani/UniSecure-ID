<?php
session_start();

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang_code = $_SESSION['lang'] ?? 'fr';
$lang_path = __DIR__ . "/lang/lang_$lang_code.php";
require file_exists($lang_path) ? $lang_path : __DIR__ . "/lang/lang_fr.php";

$message = '';
$success = false;
$redirect_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? ''; // assure-toi que le champ HTML est bien name="username"
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=unisecureid_db;charset=utf8", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // 🔍 Vérifie bien que 'Activé' est bien orthographié comme dans ta base
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'activer'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && $password === $user['password']) { // comparaison directe (⚠️ attention aux espaces)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nom_complet'] = $user['nom_complet'];
                $success = true;

                switch ($user['role']) {
                    case 'admin': $redirect_url = 'dashboard.php'; break;
                    case 'agent': $redirect_url = 'dashboard.php'; break;
                    case 'enseignant': $redirect_url = 'dashboard.php'; break;
                    case 'etudiant': $redirect_url = 'dashboard.php'; break;
                    case 'financier': $redirect_url = 'dashboard.php'; break;
                    default: $redirect_url = 'index.php'; break;
                }

                echo "<script>
                    setTimeout(function() {
                        window.location.href = '$redirect_url';
                    }, 2500);
                </script>";
            } else {
                $message = $lang['error_invalid'] ?? "Identifiants incorrects.";
            }
        } catch (PDOException $e) {
            $message = "Erreur de connexion à la base de données.";
        }
    } else {
        $message = $lang['error_required'] ?? "Veuillez remplir tous les champs.";
    }
}
?>




<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang_code) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $lang['login_title'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
	.lang-toggle-inside {
    text-align: center;
    margin-top: 15px;
}

.lang-toggle-inside img {
    width: 25px;
    margin: 0 10px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.lang-toggle-inside img:hover {
    transform: scale(1.1);
}

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
			background: url('img/back3.jpeg') no-repeat center center fixed;
            background-size: cover;
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            display: flex;
            flex-direction: row;
            background: white;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }

        .left-panel {
            flex: 1;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .left-panel img {
            max-width: 100%;
            height: auto;
        }

        .right-panel {
            flex: 1;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            animation: fadeIn 1.2s ease-in;
        }

        .right-panel h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input {
            padding: 12px;
            margin-bottom: 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle input {
            padding-right: 40px;
        }

        .password-toggle span {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
        }

        button {
            padding: 12px;
            background-color: #007BFF;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .lang-toggle {
            position: absolute;
            top: 15px;
            right: 20px;
        }

        .lang-toggle img {
            width: 25px;
            margin-left: 10px;
            cursor: pointer;
        }

        .home-link {
            margin-top: 20px;
            text-align: center;
        }

        .home-link a {
            text-decoration: none;
            color: #007BFF;
            font-size: 14px;
        }

        .home-link a:hover {
            text-decoration: underline;
        }

        @media screen and (max-width: 768px) {
            .container {
                flex-direction: column;
                text-align: center;
            }

            .left-panel, .right-panel {
                width: 100%;
            }

            .right-panel {
                padding: 20px;
            }

            .lang-toggle {
                position: static;
                text-align: center;
                margin: 10px auto;
            }
        }
    </style>
</head>
<body>



<div class="container">
    <div class="left-panel">
        <img src="img/illustration.png" alt="Connexion Illustration">
    </div>
			<div class="lang-toggle-inside">
                <a href="?lang=fr"><img src="img/fr.png" alt="Français"></a>
                <a href="?lang=en"><img src="img/en.jpeg" alt="English"></a>
            </div>
    <div class="right-panel">
        <h2><?= $lang['login_title'] ?></h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php elseif ($success): ?>
            <div class="success">✅ Connexion réussie ! Redirection en cours...</div>
        <?php endif; ?>

        <form method="post">
		
            <input type="text" name="username" placeholder="<?= $lang['username'] ?>" required>

            <div class="password-toggle">
                <input type="password" name="password" id="password" placeholder="<?= $lang['password'] ?>" required>
                <span onclick="togglePassword()">👁️</span>
            </div>

            <button type="submit"><?= $lang['submit'] ?></button>

            <!-- Logos de langue à l'intérieur du formulaire -->
            
        </form>

        <div class="home-link">
            <a href="index.php">← <?= $lang_code === 'fr' ? 'Retour à l’accueil' : 'Back to home' ?></a>
        </div>
    </div>
</div>


<script>
function togglePassword() {
    const pwd = document.getElementById("password");
    pwd.type = (pwd.type === "password") ? "text" : "password";
}
</script>

</body>
</html>
