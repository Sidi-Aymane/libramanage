<?php
$pageTitle = 'Catalogue';
require_once __DIR__ . '/config.php';

$books = [];
$categories = [];
$activeLoans = [];
$q = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));

if (db() !== null) {
    try {
        $categoryResult = db()->query('SELECT DISTINCT category FROM livres ORDER BY category');
        while ($row = $categoryResult->fetch_assoc()) {
            $categories[] = $row['category'];
        }

        if (is_logged_in()) {
            $loanStmt = db()->prepare("SELECT id, livre_id FROM emprunts WHERE user_id = ? AND status = 'borrowed'");
            $userId = current_user_id();
            $loanStmt->bind_param('i', $userId);
            $loanStmt->execute();
            $loanResult = $loanStmt->get_result();
            while ($loan = $loanResult->fetch_assoc()) {
                $activeLoans[(int) $loan['livre_id']] = (int) $loan['id'];
            }
        }

        $where = [];
        $params = [];
        $types = '';

        if ($q !== '') {
            $where[] = '(title LIKE CONCAT("%", ?, "%") OR author LIKE CONCAT("%", ?, "%") OR category LIKE CONCAT("%", ?, "%"))';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $types .= 'sss';
        }

        if ($category !== '') {
            $where[] = 'category = ?';
            $params[] = $category;
            $types .= 's';
        }

        if ($status === 'available' || $status === 'unavailable') {
            $where[] = 'availability = ?';
            $params[] = $status === 'available' ? 1 : 0;
            $types .= 'i';
        }

        $sql = 'SELECT id, title, author, category, availability, cover_image, description FROM livres';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY title ASC';

        $stmt = db()->prepare($sql);
        if ($params !== []) {
            bind_dynamic_params($stmt, $types, $params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($book = $result->fetch_assoc()) {
            $books[] = $book;
        }
    } catch (mysqli_sql_exception $exception) {
        set_flash('error', 'Importez database.sql pour charger le catalogue.');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main>
    <section class="page-intro">
        <h1>Catalogue</h1>
        <p>Recherchez un livre, verifiez sa disponibilite et empruntez-le depuis votre compte.</p>
    </section>

    <section class="section">
        <form class="catalog-toolbar" action="livres.php" method="get">
            <input type="search" name="q" placeholder="Titre, auteur ou categorie..." value="<?= e($q) ?>">

            <select name="category" aria-label="Categorie">
                <option value="">Toutes categories</option>
                <?php foreach ($categories as $item): ?>
                    <option value="<?= e($item) ?>" <?= $category === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="status" aria-label="Disponibilite">
                <option value="">Tous les livres</option>
                <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Disponibles</option>
                <option value="unavailable" <?= $status === 'unavailable' ? 'selected' : '' ?>>Indisponibles</option>
            </select>

            <button class="btn btn-primary" type="submit">Rechercher</button>
        </form>

        <?php if ($books === []): ?>
            <div class="empty-state">
                <h2>Aucun livre trouve</h2>
                <p>Essayez une autre recherche ou retirez les filtres actifs.</p>
            </div>
        <?php else: ?>
            <div class="catalog-grid">
                <?php foreach ($books as $book): ?>
                    <?php
                    $bookId = (int) $book['id'];
                    $available = (int) $book['availability'] === 1;
                    $loanId = $activeLoans[$bookId] ?? null;
                    ?>
                    <article class="book-card">
                        <div class="book-top">
                            <div class="book-cover">
                                <img src="<?= e($book['cover_image']) ?>" alt="Couverture du livre <?= e($book['title']) ?>" loading="lazy">
                            </div>
                            <div>
                                <h2><?= e($book['title']) ?></h2>
                                <p class="book-meta"><?= e($book['author']) ?></p>
                                <p class="book-meta"><?= e($book['category']) ?></p>
                            </div>
                        </div>

                        <p class="book-desc"><?= e($book['description']) ?></p>

                        <?php if ($available): ?>
                            <span class="badge badge-success">Disponible</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Livre non disponible</span>
                        <?php endif; ?>

                        <div class="book-actions">
                            <?php if ($available): ?>
                                <?php if (is_logged_in()): ?>
                                    <form action="emprunt.php" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="borrow">
                                        <input type="hidden" name="book_id" value="<?= $bookId ?>">
                                        <button class="btn btn-primary" type="submit">Emprunter</button>
                                    </form>
                                <?php else: ?>
                                    <a class="btn btn-primary" href="login.php">Login pour emprunter</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($loanId !== null): ?>
                                    <form action="emprunt.php" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="return">
                                        <input type="hidden" name="loan_id" value="<?= $loanId ?>">
                                        <button class="btn btn-outline" type="submit" data-confirm="Confirmer le retour de ce livre ?">Retourner</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (is_logged_in() && $activeLoans !== []): ?>
            <aside class="loans-panel">
                <h2>Mes emprunts actifs</h2>
                <div class="loan-list">
                    <?php
                    $loanDetails = [];
                    try {
                        $loanStmt = db()->prepare(
                            "SELECT e.id, e.date_emprunt, l.title, l.author
                             FROM emprunts e
                             INNER JOIN livres l ON l.id = e.livre_id
                             WHERE e.user_id = ? AND e.status = 'borrowed'
                             ORDER BY e.date_emprunt DESC"
                        );
                        $userId = current_user_id();
                        $loanStmt->bind_param('i', $userId);
                        $loanStmt->execute();
                        $loanDetails = $loanStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    } catch (mysqli_sql_exception $exception) {
                        $loanDetails = [];
                    }
                    ?>

                    <?php foreach ($loanDetails as $loan): ?>
                        <div class="loan-item">
                            <div>
                                <strong><?= e($loan['title']) ?></strong>
                                <p class="book-meta"><?= e($loan['author']) ?> - Emprunte le <?= e(date('d/m/Y', strtotime($loan['date_emprunt']))) ?></p>
                            </div>
                            <form action="emprunt.php" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="return">
                                <input type="hidden" name="loan_id" value="<?= (int) $loan['id'] ?>">
                                <button class="btn btn-outline" type="submit" data-confirm="Confirmer le retour de ce livre ?">Retourner</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
