<?php
/**
 * manage_products.php
 * Управление на продукти - добавяне на нов продукт и изтриване на съществуващи.
 * Достъпна само за логнати потребители.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$pdo = getDB();
$errors = [];
$success = '';

// ===== Добавяне на нов продукт =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {

    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price'] ?? '';
    $category    = trim($_POST['category'] ?? '');

    if ($name === '') {
        $errors[] = 'Името на продукта е задължително.';
    }
    if (!is_numeric($price) || (float)$price <= 0) {
        $errors[] = 'Цената трябва да е положително число.';
    }
    if ($category === '') {
        $category = 'Друго';
    }

    $imageFilename = 'default-product.png';

    if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Грешка при качване на изображението.';
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'Изображението е по-голямо от 3MB.';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
                $errors[] = 'Позволени формати: jpg, jpeg, png, gif, webp.';
            } else {
                $imageInfo = @getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    $errors[] = 'Файлът не е валидно изображение.';
                } else {
                    $imageFilename = bin2hex(random_bytes(16)) . '.' . $ext;
                    $destination = PRODUCT_UPLOAD_DIR . $imageFilename;

                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        $errors[] = 'Не успяхме да запазим изображението.';
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO products (name, description, price, category, image, created_by) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $description, (float)$price, $category, $imageFilename, $_SESSION['user_id']]);
        $success = 'Продуктът беше добавен успешно.';
    }
}

// ===== Изтриване на продукт =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($productId > 0) {
        // Първо вземаме името на снимката, за да я изтрием от диска
        $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if ($product) {
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $stmt->execute([$productId]);

            if ($product['image'] !== 'default-product.png') {
                $imgPath = PRODUCT_UPLOAD_DIR . $product['image'];
                if (file_exists($imgPath)) {
                    @unlink($imgPath);
                }
            }
            $success = 'Продуктът беше изтрит успешно.';
        }
    }
}

$products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Управление на продукти';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Управление на продукти</h1>
    <a href="products.php" class="btn btn-outline btn-small">Виж магазина</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $err): ?>
            <div>• <?= e($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<!-- ===== Форма за добавяне на нов продукт ===== -->
<div class="add-product-box">
    <h2>+ Добави нов продукт</h2>
    <form method="POST" action="manage_products.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">

        <div class="form-row">
            <div class="form-group">
                <label for="name">Име на продукта</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="category">Категория</label>
                <select id="category" name="category">
                    <option value="Тениска">Тениска</option>
                    <option value="Суичър">Суичър</option>
                    <option value="Блуза">Блуза</option>
                    <option value="Жилетка">Жилетка</option>
                    <option value="Аксесоар">Аксесоар</option>
                    <option value="Друго">Друго</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Описание</label>
            <textarea id="description" name="description" placeholder="Кратко описание на продукта..."></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="price">Цена (лв.)</label>
                <input type="number" id="price" name="price" step="0.01" min="0.01" required>
            </div>

            <div class="form-group">
                <label for="image">Снимка на продукта</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
        </div>

        <button type="submit" class="btn">Добави продукт</button>
    </form>
</div>

<!-- ===== Таблица със съществуващи продукти + изтриване ===== -->
<div class="manage-table-wrapper">
    <table class="manage-table">
        <thead>
        <tr>
            <th>Снимка</th>
            <th>Име</th>
            <th>Категория</th>
            <th>Цена</th>
            <th>Добавен на</th>
            <th>Действие</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
            <tr><td colspan="6" style="text-align:center; color:var(--text-dim);">Няма добавени продукти.</td></tr>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <img class="thumb" src="uploads/products/<?= e($product['image']) ?>"
                             alt="<?= e($product['name']) ?>"
                             onerror="this.src='assets/default-product.png'">
                    </td>
                    <td><?= e($product['name']) ?></td>
                    <td><?= e($product['category']) ?></td>
                    <td><?= number_format((float)$product['price'], 2) ?> лв.</td>
                    <td><?= e(date('d.m.Y', strtotime($product['created_at']))) ?></td>
                    <td>
                        <form method="POST" action="manage_products.php"
                              onsubmit="return confirm('Сигурен ли си, че искаш да изтриеш този продукт?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-small">Изтрий</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
