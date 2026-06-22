<?php
/**
 * config.php
 * Конфигурация за връзка с базата данни и стартиране на сесия
 */

// Стартиране на сесия (трябва да е първо, преди всякакъв изход)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== Настройки за връзка с MySQL базата данни =====
define('DB_HOST', 'localhost');
define('DB_NAME', 'metal_shop');
define('DB_USER', 'root');      // смени с твоя MySQL потребител
define('DB_PASS', '');          // смени с твоята MySQL парола

// ===== Настройки за качване на файлове =====
define('AVATAR_UPLOAD_DIR', __DIR__ . '/uploads/avatars/');
define('PRODUCT_UPLOAD_DIR', __DIR__ . '/uploads/products/');
define('MAX_FILE_SIZE', 3 * 1024 * 1024); // 3 MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

/**
 * Връща PDO връзка към базата данни.
 * Използва се singleton подход, за да не се отваря връзка повече от веднъж.
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Грешка при свързване с базата данни: ' . htmlspecialchars($e->getMessage()));
        }
    }

    return $pdo;
}

/**
 * Помощна функция за безопасен изход на текст (защита от XSS)
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Проверява дали потребителят е логнат
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Пренасочва към друга страница и спира изпълнението
 */
function redirect(string $location): void {
    header('Location: ' . $location);
    exit;
}

/**
 * Изисква логин - ако потребителят не е логнат, го връща към login.php
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}
