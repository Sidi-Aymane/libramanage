<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'libramanage');

$conn = null;

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    $conn = null;
}

function db(): ?mysqli
{
    global $conn;
    return $conn;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_name(): string
{
    return $_SESSION['user_name'] ?? '';
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']);
}

function active_nav(string $page): string
{
    return basename($_SERVER['PHP_SELF']) === $page ? 'is-active' : '';
}

function require_database(): void
{
    if (db() === null) {
        set_flash('error', 'Connexion a la base de donnees impossible. Importez database.sql puis verifiez config.php.');
        redirect('index.php');
    }
}

function bind_dynamic_params(mysqli_stmt $stmt, string $types, array &$params): void
{
    if ($types === '' || $params === []) {
        return;
    }

    $bindValues = [$types];

    foreach ($params as &$param) {
        $bindValues[] = &$param;
    }

    call_user_func_array([$stmt, 'bind_param'], $bindValues);
}
