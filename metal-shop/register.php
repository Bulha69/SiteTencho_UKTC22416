<?php
/**
 * register.php
 * Регистрация на нов потребител + качване на профилна снимка
 */
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$old = ['username' => '', 'email' => '', 'full_name' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old['username']  = trim($_POST['username'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirmPassword  = $_POST['confirm_password'] ?? '';

    // ===== Валидация на текстовите полета =====
    if ($old['username'] === '' || mb_strlen($old['username']) < 3) {
        $errors[] = 'Потребителското име трябва да е минимум 3 символа.';
    }

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Моля, въведи валиден имейл адрес.';
    }

    if ($old['full_name'] === '') {
        $errors[] = 'Моля, въведи име и фамилия.';
    }

    if (mb_strlen($password) < 6) {
        $errors[] = 'Паролата трябва да е минимум 6 символа.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Паролите не съвпадат.';
    }

    // ===== Проверка дали потребителското име / имейл вече съществуват =====
    if (empty($errors)) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$old['username'], $old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'Потребител с това име или имейл вече съществува.';
        }
    }

    // ===== Обработка на снимката =====
    $avatarFilename = 'default.png';

    if (empty($errors) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Грешка при качване на снимката.';
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'Снимката е по-голяма от 3MB.';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
                $errors[] = 'Позволени формати за снимка: jpg, jpeg, png, gif, webp.';
            } else {
                // Допълнителна проверка, че файлът наистина е изображение
                $imageInfo = @getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    $errors[] = 'Файлът не е валидно изображение.';
                } else {
                    // Генерираме уникално име, за да няма конфликти
                    $avatarFilename = bin2hex(random_bytes(16)) . '.' . $ext;
                    $destination = AVATAR_UPLOAD_DIR . $avatarFilename;

                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        $errors[] = 'Не успяхме да запазим снимката. Опитай отново.';
                    }
                }
            }
        }
    }

    // ===== Записване в базата данни =====
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, full_name, avatar) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$old['username'], $old['email'], $passwordHash, $old['full_name'], $avatarFilename]);

        $_SESSION['register_success'] = true;
        redirect('login.php');
    }
}

$pageTitle = 'Регистрация';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <h1>Регистрация</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?>
                <div>• <?= e($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" enctype="multipart/form-data">

        <div class="form-group">
            <label for="username">Потребителско име</label>
            <input type="text" id="username" name="username" value="<?= e($old['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="full_name">Име и фамилия</label>
            <input type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Имейл</label>
            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Парола</label>
            <input type="password" id="password" name="password" required>
            <div class="form-hint">Минимум 6 символа.</div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Потвърди парола</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="form-group">
            <label for="avatar">Профилна снимка (по желание)</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
            <div class="form-hint">Позволени формати: jpg, png, gif, webp. Макс. 3MB.</div>
        </div>

        <button type="submit" class="btn btn-full">Регистрирай се</button>
    </form>

    <div class="form-footer">
        Вече имаш профил? <a href="login.php">Влез тук</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
