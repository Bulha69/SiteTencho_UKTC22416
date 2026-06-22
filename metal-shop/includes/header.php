<?php
/**
 * includes/header.php
 * Общ хедър за всички страници - навигация и начало на HTML документа
 */
require_once __DIR__ . '/../config.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Metal Shop</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="logo">☠ METAL<span>SHOP</span></a>
        <nav class="main-nav">
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Начало</a>
            <a href="products.php" class="<?= $currentPage === 'products.php' ? 'active' : '' ?>">Магазин</a>
            <?php if (isLoggedIn()): ?>
                <a href="manage_products.php" class="<?= $currentPage === 'manage_products.php' ? 'active' : '' ?>">Управление</a>
                <a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">Профил</a>
                <a href="logout.php" class="btn-logout">Изход</a>
            <?php else: ?>
                <a href="login.php" class="<?= $currentPage === 'login.php' ? 'active' : '' ?>">Вход</a>
                <a href="register.php" class="btn-register <?= $currentPage === 'register.php' ? 'active' : '' ?>">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="site-main">
