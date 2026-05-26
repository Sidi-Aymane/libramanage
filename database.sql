CREATE DATABASE IF NOT EXISTS libramanage
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE libramanage;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS livres (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    author VARCHAR(160) NOT NULL,
    category VARCHAR(100) NOT NULL,
    availability TINYINT(1) NOT NULL DEFAULT 1,
    cover_image VARCHAR(255) NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_livres_availability (availability),
    INDEX idx_livres_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS emprunts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    livre_id INT UNSIGNED NOT NULL,
    date_emprunt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_retour DATETIME NULL,
    status ENUM('borrowed', 'returned') NOT NULL DEFAULT 'borrowed',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_emprunts_status (status),
    INDEX idx_emprunts_user_status (user_id, status),
    CONSTRAINT fk_emprunts_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_emprunts_livre
        FOREIGN KEY (livre_id) REFERENCES livres(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO livres (title, author, category, availability, cover_image, description) VALUES
('Le Petit Prince', 'Antoine de Saint-Exupery', 'Classique', 1, 'assets/images/books/le-petit-prince.jpg', 'Un conte poetique sur le voyage, l amitie et la curiosite.'),
('L Etranger', 'Albert Camus', 'Roman', 1, 'assets/images/books/letranger.jpg', 'Un roman majeur de la litterature moderne autour de l absurde.'),
('1984', 'George Orwell', 'Science-fiction', 1, 'assets/images/books/1984.jpg', 'Une dystopie incontournable sur la surveillance et la liberte.'),
('Clean Code', 'Robert C. Martin', 'Informatique', 1, 'assets/images/books/clean-code.jpg', 'Un guide pratique pour ecrire un code plus lisible et maintenable.'),
('Sapiens', 'Yuval Noah Harari', 'Histoire', 1, 'assets/images/books/sapiens.jpg', 'Une exploration accessible de l histoire de l humanite.'),
('Atomic Habits', 'James Clear', 'Developpement personnel', 1, 'assets/images/books/atomic-habits.jpg', 'Des methodes simples pour construire de meilleures habitudes.'),
('Les Miserables', 'Victor Hugo', 'Classique', 0, 'assets/images/books/les-miserables.jpg', 'Une fresque puissante sur la justice, la pauvrete et la redemption.'),
('The Pragmatic Programmer', 'Andrew Hunt, David Thomas', 'Informatique', 1, 'assets/images/books/pragmatic-programmer.jpg', 'Conseils durables pour progresser comme developpeur.'),
('Fahrenheit 451', 'Ray Bradbury', 'Science-fiction', 1, 'assets/images/books/fahrenheit-451.jpg', 'Un roman sur les livres, la censure et la resistance intellectuelle.');
