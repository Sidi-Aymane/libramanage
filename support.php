<?php
$pageTitle = 'Support';
require_once __DIR__ . '/config.php';

$success = '';
$error = '';
$name = is_logged_in() ? current_user_name() : '';
$email = $_SESSION['user_email'] ?? '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if (!verify_csrf()) {
        $error = 'Session expiree. Reessayez.';
    } elseif ($name === '' || $email === '' || $subject === '' || $message === '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $success = 'Votre message a ete envoye. Nous vous repondrons rapidement.';
        $subject = '';
        $message = '';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="support-shell">
    <section class="page-intro">
        <h1>Support</h1>
        <p>Une question ? Contactez-nous, nous repondons rapidement.</p>
    </section>

    <section class="support-form-wrap">
        <?php if ($success !== ''): ?>
            <div class="flash-inline flash-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="form-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="form-grid" action="support.php" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Nom complet</label>
                <input class="form-control" id="name" type="text" name="name" placeholder="Votre nom..." value="<?= e($name) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" id="email" type="email" name="email" placeholder="Votre email..." value="<?= e($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="subject">Sujet</label>
                <select class="form-control" id="subject" name="subject" required>
                    <option value="">-- Choisir un sujet --</option>
                    <option value="Compte" <?= $subject === 'Compte' ? 'selected' : '' ?>>Compte</option>
                    <option value="Emprunt" <?= $subject === 'Emprunt' ? 'selected' : '' ?>>Emprunt</option>
                    <option value="Livre indisponible" <?= $subject === 'Livre indisponible' ? 'selected' : '' ?>>Livre indisponible</option>
                    <option value="Autre" <?= $subject === 'Autre' ? 'selected' : '' ?>>Autre</option>
                </select>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea class="form-control" id="message" name="message" placeholder="Decrivez votre probleme..." required><?= e($message) ?></textarea>
            </div>

            <button class="btn btn-primary btn-block" type="submit">Envoyer le message</button>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
