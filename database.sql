-- database.sql (قم بتشغيله في phpMyAdmin)
CREATE DATABASE IF NOT EXISTS aurum_db;
USE aurum_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    is_admin TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE watches (
    id VARCHAR(50) PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    name VARCHAR(200) NOT NULL,
    model VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    description TEXT,
    theme VARCHAR(50) DEFAULT 'classic'
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    watch_id VARCHAR(50) NOT NULL,
    quantity INT DEFAULT 1,
    color VARCHAR(50) DEFAULT 'silver',
    strap VARCHAR(50) DEFAULT 'leather',
    engraving TEXT,
    price DECIMAL(10,2),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (watch_id) REFERENCES watches(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id VARCHAR(50) PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('processing','shipped','delivered') DEFAULT 'processing',
    total DECIMAL(10,2) NOT NULL,
    shipping_name VARCHAR(255),
    shipping_street VARCHAR(255),
    shipping_city VARCHAR(100),
    shipping_postal VARCHAR(20),
    shipping_country VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL,
    watch_id VARCHAR(50) NOT NULL,
    brand VARCHAR(100),
    name VARCHAR(200),
    price DECIMAL(10,2),
    quantity INT,
    color VARCHAR(50),
    strap VARCHAR(50),
    engraving TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    watch_id VARCHAR(50) NOT NULL,
    UNIQUE KEY (user_id, watch_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (watch_id) REFERENCES watches(id) ON DELETE CASCADE
);

CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255),
    street VARCHAR(255),
    city VARCHAR(100),
    postal VARCHAR(20),
    country VARCHAR(100),
    phone VARCHAR(50),
    is_default TINYINT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (email, password, full_name, is_admin) VALUES 
('admin@aurum.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 1);

INSERT INTO watches (id, brand, name, model, price, image, description, theme) VALUES
('r1', 'Rolex', 'Submariner Date', 'Submariner Date', 14500, 'images/r1.jpg', 'The quintessential diver''s watch.', 'classic'),
('r2', 'Rolex', 'Daytona Cosmograph', 'Daytona', 32000, 'images/r2.jpg', 'Ultimate chronograph for racing.', 'champagne'),
('pp1', 'Patek Philippe', 'Nautilus', '5711', 145000, 'images/pp1.jpg', 'Most sought-after sports watch.', 'blue'),
('ap1', 'Audemars Piguet', 'Royal Oak', '15500', 32000, 'images/ap1.jpg', 'Iconic luxury sports watch.', 'blue'),
('rm1', 'Richard Mille', 'RM 011', 'RM 011', 195000, 'images/rm1.jpg', 'Formula One engineering.', 'classic'),
('o1', 'Omega', 'Seamaster Planet Ocean', 'Seamaster', 7800, 'images/o1.jpg', 'Ocean explorer 600m.', 'blue');