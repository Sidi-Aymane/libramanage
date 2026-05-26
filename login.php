<?php
$pageTitle = 'Login';
require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    redirect('livres.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!verify_csrf()) {
        $error = 'Session expiree. Reessayez.';
    } elseif (db() === null) {
        $error = 'Connexion a la base de donnees impossible.';
    } elseif ($email === '' || $password === '') {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $stmt = db()->prepare('SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                set_flash('success', 'Bienvenue sur LibraManage.');
                redirect('livres.php');
            }

            $error = 'Email ou mot de passe incorrect.';
        } catch (mysqli_sql_exception $exception) {
            $error = 'Impossible de se connecter maintenant.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="auth-shell">
    <section class="auth-card">
        <h1>Login</h1>
        <p>Connectez-vous pour emprunter les livres disponibles.</p>

        <?php if ($error !== ''): ?>
            <div class="form-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="form-grid" action="login.php" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" id="email" type="email" name="email" value="<?= e($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input class="form-control" id="password" type="password" name="password" required>
            </div>

            <button class="btn btn-primary btn-block" type="submit">Login</button>
        </form>

        <p class="auth-alt">Pas encore de compte ? <a href="signup.php">Sign Up</a></p>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
