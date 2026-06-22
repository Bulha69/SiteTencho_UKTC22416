<?php
/**
 * profile.php
 * Профилна страница - показва данните на логнатия потребител
 */
require_once __DIR__ . '/config.php';
requireLogin();

$pdo = getDB();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // Ако потребителят е изтрит от базата, но сесията е останала активна
    redirect('logout.php');
}

// Брой добавени продукти от този потребител
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM products WHERE created_by = ?');
$stmt->execute([$_SESSION['user_id']]);
$productCount = $stmt->fetch()['total'];

$pageTitle = 'Моят профил';
require_once __DIR__ . '/includes/header.php';
?>

<div class="profile-box">
    <img
        class="profile-avatar"
        src="uploads/avatars/<?= e($user['avatar']) ?>"
        alt="Профилна снимка"
        onerror="this.src='assets/default-avatar.png'"
    >

    <h1><?= e($user['full_name']) ?></h1>
    <div class="profile-username">@<?= e($user['username']) ?></div>

    <div class="profile-details">
        <div class="profile-row">
            <span class="label">Потребителско име</span>
            <span class="value"><?= e($user['username']) ?></span>
        </div>
        <div class="profile-row">
            <span class="label">Имейл</span>
            <span class="value"><?= e($user['email']) ?></span>
        </div>
        <div class="profile-row">
            <span class="label">Име и фамилия</span>
            <span class="value"><?= e($user['full_name']) ?></span>
        </div>
        <div class="profile-row">
            <span class="label">Регистриран на</span>
            <span class="value"><?= e(date('d.m.Y H:i', strtotime($user['created_at']))) ?></span>
        </div>
        <div class="profile-row">
            <span class="label">Добавени продукти</span>
            <span class="value"><?= (int)$productCount ?></span>
        </div>
    </div>

    <div style="margin-top:24px;">
        <a href="manage_products.php" class="btn btn-outline btn-small">Управление на продукти</a>
        <a href="logout.php" class="btn btn-danger btn-small">Изход</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
