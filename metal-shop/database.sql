-- ============================================
-- БАЗА ДАННИ ЗА METAL SHOP
-- ============================================

CREATE DATABASE IF NOT EXISTS metal_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE metal_shop;

-- Таблица с потребители
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица с продукти (metal дрехи)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) DEFAULT 'Тениска',
    image VARCHAR(255) DEFAULT 'default-product.png',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Примерни продукти
INSERT INTO products (name, description, price, category, image) VALUES
('Iron Maiden - Eddie Tour Tee', 'Черна тениска с принт на Eddie, 100% памук', 39.90, 'Тениска', 'default-product.png'),
('Slayer - Reign in Blood Hoodie', 'Суичър с качулка, плътен памук, изображение от албума', 79.90, 'Суичър', 'default-product.png'),
('Death - Symbolic Long Sleeve', 'Блуза с дълъг ръкав, death metal естетика', 49.90, 'Блуза', 'default-product.png'),
('Cannibal Corpse - Battle Vest', 'Дънкова жилетка с пачове, ръчна изработка', 119.90, 'Жилетка', 'default-product.png'),
('Metallica - Master Puppets Cap', 'Шапка с козирка, бродирано лого', 29.90, 'Аксесоар', 'default-product.png');
