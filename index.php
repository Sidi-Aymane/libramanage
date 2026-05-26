<?php
$pageTitle = 'Accueil';
require_once __DIR__ . '/config.php';

$stats = [
    'books' => 0,
];

if (db() !== null) {
    try {
        $result = db()->query('SELECT COUNT(*) AS total FROM livres');
        $row = $result->fetch_assoc();
        $stats['books'] = (int) ($row['total'] ?? 0);
    } catch (mysqli_sql_exception $exception) {
        $stats = ['books' => 0];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="home-hero">
    <div class="hero-inner">
        <h1 class="hero-title">Smart Library Borrowing</h1>
        <p class="hero-subtitle">
            Un site moderne pour explorer le catalogue, verifier la disponibilite des livres
            et gerer vos emprunts en toute simplicite.
        </p>

        <div class="hero-cta">
            <a class="btn btn-primary" href="livres.php">Explorer les livres</a>
            <?php if (!is_logged_in()): ?>
                <a class="btn btn-outline" href="signup.php">Creer un compte</a>
            <?php endif; ?>
        </div>

        <section class="feature-grid" aria-label="Fonctionnalites principales">
            <a class="glass-card" href="livres.php">
                <h2>Books</h2>
                <p><?= $stats['books'] > 0 ? e((string) $stats['books']) . ' livres dans le catalogue' : 'Browse and discover books easily' ?></p>
            </a>

            <a class="glass-card" href="support.php">
                <h2>Support</h2>
                <p>Contactez-nous pour une question ou un probleme</p>
            </a>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
