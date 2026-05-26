<?php
require_once __DIR__ . '/../config.php';

$pageTitle = $pageTitle ?? 'LibraManage';
$searchValue = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - LibraManage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php">LibraManage</a>

        <button class="nav-toggle" type="button" aria-label="Ouvrir le menu" data-nav-toggle>
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="main-nav" data-nav-menu>
            <a class="<?= active_nav('index.php') ?>" href="index.php">Accueil</a>
            <a class="<?= active_nav('livres.php') ?>" href="livres.php">Catalogue</a>
            <a class="<?= active_nav('support.php') ?>" href="support.php">Support</a>
        </nav>

        <div class="header-actions" data-nav-actions>
            <form class="search-form" action="livres.php" method="get">
                <input type="search" name="q" placeholder="Search..." value="<?= e($searchValue) ?>" aria-label="Rechercher un livre">
            </form>

            <?php if (is_logged_in()): ?>
                <span class="user-chip">Bonjour, <?= e(current_user_name()) ?></span>
                <a class="btn btn-outline" href="logout.php">Logout</a>
            <?php else: ?>
                <a class="btn btn-outline" href="login.php">Login</a>
                <a class="btn btn-primary" href="signup.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($flash = get_flash()): ?>
        <div class="flash flash-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>
