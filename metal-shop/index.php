<?php
$pageTitle = 'Начало';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Metal Shop</h1>
    <p>Облекло и аксесоари за истински метъл фенове. Тениски, суичъри, жилетки
       с принтове на легендарни групи — за тези, които живеят на сцената.</p>

    <a href="products.php" class="btn">Разгледай магазина</a>
    <?php if (!isLoggedIn()): ?>
        <a href="register.php" class="btn btn-outline">Регистрация</a>
    <?php endif; ?>
</section>

<section class="features">
    <div class="feature-card">
        <div class="icon">🤘</div>
        <h3>Автентичен стил</h3>
        <p>Дизайни, вдъхновени от thrash и death metal културата.</p>
    </div>
    <div class="feature-card">
        <div class="icon">🛒</div>
        <h3>Лесно пазаруване</h3>
        <p>Разгледай пълния каталог с продукти на едно място.</p>
    </div>
    <div class="feature-card">
        <div class="icon">🔒</div>
        <h3>Сигурен профил</h3>
        <p>Паролите се пазят хеширани, данните ти са защитени.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
