<?php
session_start();
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang_code = $_SESSION['lang'] ?? 'fr';
$lang_path = __DIR__ . "/lang/lang_$lang_code.php";
require file_exists($lang_path) ? $lang_path : __DIR__ . "/lang/lang_fr.php";
?>

<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $lang['title'] ?></title>
    <style>
        body {
            margin: 0; padding: 0;
            font-family: "Segoe UI", sans-serif;
            background: url('img/back3.jpeg') no-repeat center center fixed;
            background-size: cover;
            color: white; height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .overlay { position: absolute; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); z-index: 0; }
        .container {
            z-index: 1; position: relative; text-align: center;
            padding: 3rem; background: rgba(255,255,255,0.1);
            border-radius: 15px; box-shadow: 0 0 25px rgba(0,0,0,0.5);
        }
        .logo { width: 100px; margin-bottom: 1rem; }
        .btn-login {
            margin-top: 2rem; background-color: white; color: #800000;
            padding: 0.75rem 1.5rem; border-radius: 30px; text-decoration: none; font-weight: bold;
        }
        .lang-toggle {
            position: absolute; top: 20px; right: 30px;
        }
        .lang-toggle img {
            width: 25px; height: auto; margin-left: 10px; cursor: pointer;
        }
    </style>
</head>
<body>
<div class="overlay"></div>
<div class="container">
    <img src="img/unilis.jpg" alt="Logo Université de Lisala" class="logo">
    <h1><?= $lang['welcome'] ?></h1>
    <p><?= $lang['description'] ?></p>
    <a href="login.php" class="btn-login"><?= $lang['login_button'] ?></a>
</div>
<div class="lang-toggle">
    <a href="?lang=fr"><img src="img/fr.png" alt="Français"></a>
    <a href="?lang=en"><img src="img/en.jpeg" alt="English"></a>
</div>
</body>
</html>
