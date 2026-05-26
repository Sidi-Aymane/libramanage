<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('livres.php');
}

if (!is_logged_in()) {
    set_flash('error', 'Connectez-vous pour emprunter un livre.');
    redirect('login.php');
}

if (!verify_csrf()) {
    set_flash('error', 'Session expiree. Reessayez.');
    redirect('livres.php');
}

require_database();

$action = (string) ($_POST['action'] ?? '');
$userId = current_user_id();

try {
    if ($action === 'borrow') {
        $bookId = (int) ($_POST['book_id'] ?? 0);

        if ($bookId <= 0) {
            set_flash('error', 'Livre introuvable.');
            redirect('livres.php');
        }

        db()->begin_transaction();

        $stmt = db()->prepare('SELECT id, title, availability FROM livres WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $bookId);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();

        if (!$book) {
            db()->rollback();
            set_flash('error', 'Livre introuvable.');
            redirect('livres.php');
        }

        if ((int) $book['availability'] !== 1) {
            db()->rollback();
            set_flash('error', 'Livre non disponible');
            redirect('livres.php');
        }

        $checkStmt = db()->prepare("SELECT id FROM emprunts WHERE user_id = ? AND livre_id = ? AND status = 'borrowed' LIMIT 1");
        $checkStmt->bind_param('ii', $userId, $bookId);
        $checkStmt->execute();

        if ($checkStmt->get_result()->fetch_assoc()) {
            db()->rollback();
            set_flash('error', 'Vous avez deja emprunte ce livre.');
            redirect('livres.php');
        }

        $insertStmt = db()->prepare("INSERT INTO emprunts (user_id, livre_id, date_emprunt, status) VALUES (?, ?, NOW(), 'borrowed')");
        $insertStmt->bind_param('ii', $userId, $bookId);
        $insertStmt->execute();

        $updateStmt = db()->prepare('UPDATE livres SET availability = 0 WHERE id = ?');
        $updateStmt->bind_param('i', $bookId);
        $updateStmt->execute();

        db()->commit();
        set_flash('success', 'Livre emprunte avec succes.');
        redirect('livres.php');
    }

    if ($action === 'return') {
        $loanId = (int) ($_POST['loan_id'] ?? 0);

        if ($loanId <= 0) {
            set_flash('error', 'Emprunt introuvable.');
            redirect('livres.php');
        }

        db()->begin_transaction();

        $stmt = db()->prepare(
            "SELECT e.id, e.livre_id, l.title
             FROM emprunts e
             INNER JOIN livres l ON l.id = e.livre_id
             WHERE e.id = ? AND e.user_id = ? AND e.status = 'borrowed'
             FOR UPDATE"
        );
        $stmt->bind_param('ii', $loanId, $userId);
        $stmt->execute();
        $loan = $stmt->get_result()->fetch_assoc();

        if (!$loan) {
            db()->rollback();
            set_flash('error', 'Emprunt actif introuvable.');
            redirect('livres.php');
        }

        $bookId = (int) $loan['livre_id'];

        $returnStmt = db()->prepare("UPDATE emprunts SET status = 'returned', date_retour = NOW() WHERE id = ?");
        $returnStmt->bind_param('i', $loanId);
        $returnStmt->execute();

        $bookStmt = db()->prepare('UPDATE livres SET availability = 1 WHERE id = ?');
        $bookStmt->bind_param('i', $bookId);
        $bookStmt->execute();

        db()->commit();
        set_flash('success', 'Livre retourne. Il est de nouveau disponible.');
        redirect('livres.php');
    }

    set_flash('error', 'Action inconnue.');
    redirect('livres.php');
} catch (mysqli_sql_exception $exception) {
    if (db() !== null) {
        db()->rollback();
    }

    set_flash('error', 'Une erreur est survenue pendant le traitement.');
    redirect('livres.php');
}
