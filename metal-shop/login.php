<?php
/**
 * login.php
 * Вход в системата - сравнение на парола с хеша в базата данни
 * чрез password_verify() (PHP вграден bcrypt механизъм)
 */
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$successMessage = '';

if (isset($_SESSION['register_success'])) {
    $successMessage = 'Регистрацията е успешна! Сега можеш да влезеш в профила си.';
    unset($_SESSION['register_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $error = 'Моля, попълни всички полета.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();

        // password_verify сравнява въведената парола с хеша в базата данни.
        // Хешът никога не се декриптира - само се проверява съвпадение.
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            redirect('index.php');
        } else {
            $error = 'Грешно потребителско име/имейл или парола.';
        }
    }
}

$pageTitle = 'Вход';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <h1>Вход</h1>

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= e($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Потребителско име или имейл</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Парола</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-full">Влез</button>
    </form>

    <div class="form-footer">
        Нямаш профил? <a href="register.php">Регистрирай се</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
