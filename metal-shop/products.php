<?php
/**
 * products.php
 * Публична страница - списък с продукти/услуги (магазин за metal дрехи)
 */
require_once __DIR__ . '/config.php';

$pdo = getDB();
$products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Магазин';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Нашите продукти</h1>
    <?php if (isLoggedIn()): ?>
        <a href="manage_products.php" class="btn btn-small">+ Управление на продукти</a>
    <?php endif; ?>
</div>

<?php if (empty($products)): ?>
    <div class="empty-state">
        <p>Все още няма добавени продукти.</p>
    </div>
<?php else: ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img
                    class="product-img"
                    src="uploads/products/<?= e($product['image']) ?>"
                    alt="<?= e($product['name']) ?>"
                    onerror="this.src='assets/default-product.png'"
                >
                <div class="product-info">
                    <span class="product-category"><?= e($product['category']) ?></span>
                    <h3><?= e($product['name']) ?></h3>
                    <p><?= e($product['description']) ?></p>
                    <div class="product-price"><?= number_format((float)$product['price'], 2) ?> лв.</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
