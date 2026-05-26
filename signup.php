<?php
$pageTitle = 'Sign Up';
require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    redirect('livres.php');
}

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (!verify_csrf()) {
        $error = 'Session expiree. Reessayez.';
    } elseif (db() === null) {
        $error = 'Connexion a la base de donnees impossible.';
    } elseif ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            $checkStmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $checkStmt->bind_param('s', $email);
            $checkStmt->execute();

            if ($checkStmt->get_result()->fetch_assoc()) {
                $error = 'Cet email est deja utilise.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $name, $email, $hash);
                $stmt->execute();

                session_regenerate_id(true);
                $_SESSION['user_id'] = db()->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;

                set_flash('success', 'Compte cree avec succes.');
                redirect('livres.php');
            }
        } catch (mysqli_sql_exception $exception) {
            $error = 'Impossible de creer le compte maintenant.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="auth-shell">
    <section class="auth-card">
        <h1>Sign Up</h1>
        <p>Creez votre compte pour emprunter les livres disponibles.</p>

        <?php if ($error !== ''): ?>
            <div class="form-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="form-grid" action="signup.php" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Nom complet</label>
                <input class="form-control" id="name" type="text" name="name" value="<?= e($name) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" id="email" type="email" name="email" value="<?= e($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input class="form-control" id="password" type="password" name="password" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input class="form-control" id="confirm_password" type="password" name="confirm_password" minlength="6" required>
            </div>

            <button class="btn btn-primary btn-block" type="submit">Sign Up</button>
        </form>

        <p class="auth-alt">Deja inscrit ? <a href="login.php">Login</a></p>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
