-- =====================================================================
-- Tracksuit Couture: database schema + sample data
-- MySQL 8 / MariaDB 10.4+
--
-- Import (DBeaver): open an SQL editor on your connection, open this
-- file and run it as a script (Alt+X). It creates the database itself.
--
-- Test accounts (passwords are stored as bcrypt hashes):
--   admin@tracksuitcouture.ba  /  Admin123!     (role: admin)
--   amar@example.com           /  Customer123!  (role: customer)
--   lejla@example.com          /  Customer123!  (role: customer)
-- =====================================================================

DROP DATABASE IF EXISTS tracksuit_couture;
CREATE DATABASE tracksuit_couture CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tracksuit_couture;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    first_name         VARCHAR(50)  NOT NULL,
    last_name          VARCHAR(50)  NOT NULL,
    email              VARCHAR(100) NOT NULL UNIQUE,
    password_hash      VARCHAR(255) NOT NULL,
    role               ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    phone              VARCHAR(30)  NULL,
    address            VARCHAR(150) NULL,
    city               VARCHAR(60)  NULL,
    preferred_currency CHAR(3)      NOT NULL DEFAULT 'BAM',
    created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- categories
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)  NOT NULL UNIQUE,
    description VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- products (a tracksuit model)
-- A category cannot be deleted while it still has products (RESTRICT).
-- ---------------------------------------------------------------------
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT           NOT NULL,
    name        VARCHAR(100)  NOT NULL,
    description TEXT          NULL,
    fit         ENUM('men', 'women', 'unisex') NOT NULL DEFAULT 'unisex',
    price       DECIMAL(10,2) NOT NULL,           -- in BAM
    image_url   VARCHAR(255)  NULL,
    is_active   TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_products_price CHECK (price >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- product_variants (size + colour of a product, with its own stock)
-- Deleting a product deletes its variants (CASCADE).
-- ---------------------------------------------------------------------
CREATE TABLE product_variants (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    product_id     INT         NOT NULL,
    sku            VARCHAR(40) NOT NULL UNIQUE,
    size           ENUM('XS', 'S', 'M', 'L', 'XL', 'XXL') NOT NULL,
    colour         VARCHAR(30) NOT NULL,
    stock_quantity INT         NOT NULL DEFAULT 0,
    CONSTRAINT fk_variants_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT uq_variant UNIQUE (product_id, size, colour),
    CONSTRAINT chk_variants_stock CHECK (stock_quantity >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- orders
-- A user with orders cannot be deleted (RESTRICT), so order history is kept.
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT           NOT NULL,
    status           ENUM('processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'processing',
    total_amount     DECIMAL(10,2) NOT NULL DEFAULT 0, -- in BAM
    shipping_address VARCHAR(150)  NOT NULL,
    shipping_city    VARCHAR(60)   NOT NULL,
    shipping_zip     VARCHAR(10)   NULL,
    payment_method   ENUM('cash_on_delivery', 'card_on_delivery') NOT NULL DEFAULT 'cash_on_delivery',
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- order_items (one line of an order)
-- unit_price = price at the moment of purchase.
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT           NOT NULL,
    variant_id INT           NOT NULL,
    quantity   INT           NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id)
        REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_items_variant FOREIGN KEY (variant_id)
        REFERENCES product_variants(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_items_quantity CHECK (quantity > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- reviews (one review per user per product)
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT      NOT NULL,
    user_id    INT      NOT NULL,
    rating     TINYINT  NOT NULL,
    comment    TEXT     NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT uq_review UNIQUE (product_id, user_id),
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- =====================================================================
-- Sample data
-- =====================================================================

INSERT INTO users (id, first_name, last_name, email, password_hash, role, phone, address, city, preferred_currency, created_at) VALUES
(1, 'Admin', 'User',   'admin@tracksuitcouture.ba', '$2y$10$caSL2tl7PWa/OKbi7z3D4OABKOnFG0lTR5u8Epx0IS8dR8QFySqNq', 'admin',    '+387 33 000 000', 'Ferhadija 1',        'Sarajevo', 'BAM', '2026-10-01 09:00:00'),
(2, 'Amar',  'Hadžić', 'amar@example.com',          '$2y$10$LvYwRcE921M/bINMgoLI4eT.rm/7Ffz/.HhMt1GLnvlyCgMjal1bK', 'customer', '+387 61 111 111', 'Zmaja od Bosne 12',  'Sarajevo', 'EUR', '2026-10-05 14:20:00'),
(3, 'Lejla', 'Mehić',  'lejla@example.com',         '$2y$10$LvYwRcE921M/bINMgoLI4eT.rm/7Ffz/.HhMt1GLnvlyCgMjal1bK', 'customer', '+387 62 222 222', 'Maršala Tita 5',     'Mostar',   'BAM', '2026-10-08 18:45:00');

INSERT INTO categories (id, name, description) VALUES
(1, 'Velour',      'Soft velour sets'),
(2, 'Retro',       'Vintage-inspired track sets'),
(3, 'Lounge',      'Relaxed fleece and cotton'),
(4, 'Performance', 'Technical fabrics for training');

INSERT INTO products (id, category_id, name, description, fit, price, image_url, is_active) VALUES
(1, 1, 'Monaco Velour Tracksuit', 'Soft cotton-blend velour with a tailored, slim fit. Full-zip jacket with gold-tone hardware and matching tapered joggers.', 'unisex', 289.00, NULL, 1),
(2, 2, 'Riviera Track Set',       'Retro track jacket with contrast piping and straight-leg pants.',                                                         'men',    219.00, NULL, 1),
(3, 3, 'Atelier Fleece Set',      'Brushed fleece hoodie and wide-leg joggers in a relaxed fit.',                                                            'women',  249.00, NULL, 1),
(4, 4, 'Milano Tech Tracksuit',   'Lightweight stretch fabric that wicks moisture. Made for training.',                                                      'unisex', 199.00, NULL, 0),
(5, 2, 'Heritage Stripe Set',     'Classic three-stripe look with a modern cut.',                                                                            'unisex', 229.00, NULL, 1),
(6, 3, 'Olive Utility Tracksuit', 'Utility-inspired lounge set with cargo pockets.',                                                                         'men',    209.00, NULL, 1),
(7, 1, 'Noir Satin Set',          'Satin-finish velour for evening streetwear.',                                                                             'women',  329.00, NULL, 1),
(8, 4, 'Sprint Performance Set',  'Breathable mesh-lined set for running and the gym.',                                                                      'unisex', 179.00, NULL, 1);

INSERT INTO product_variants (id, product_id, sku, size, colour, stock_quantity) VALUES
(1,  1, 'MON-BLK-S',   'S',   'Black',    8),
(2,  1, 'MON-BLK-M',   'M',   'Black',    12),
(3,  1, 'MON-BLK-L',   'L',   'Black',    6),
(4,  1, 'MON-BLK-XXL', 'XXL', 'Black',    0),
(5,  1, 'MON-NAV-M',   'M',   'Navy',     5),
(6,  2, 'RIV-NAV-M',   'M',   'Navy',     9),
(7,  2, 'RIV-NAV-L',   'L',   'Navy',     7),
(8,  3, 'ATE-CRM-S',   'S',   'Cream',    10),
(9,  3, 'ATE-CRM-M',   'M',   'Cream',    4),
(10, 4, 'MIL-GRY-M',   'M',   'Grey',     15),
(11, 5, 'HER-BUR-M',   'M',   'Burgundy', 3),
(12, 5, 'HER-BUR-L',   'L',   'Burgundy', 2),
(13, 6, 'OLV-OLV-L',   'L',   'Olive',    11),
(14, 7, 'NOI-BLK-S',   'S',   'Black',    5),
(15, 7, 'NOI-BLK-M',   'M',   'Black',    6),
(16, 8, 'SPR-NAV-M',   'M',   'Navy',     20);

INSERT INTO orders (id, user_id, status, total_amount, shipping_address, shipping_city, shipping_zip, payment_method, created_at) VALUES
(1, 2, 'delivered',  229.00, 'Zmaja od Bosne 12', 'Sarajevo', '71000', 'cash_on_delivery', '2026-10-18 11:02:00'),
(2, 3, 'shipped',    199.00, 'Maršala Tita 5',    'Mostar',   '88000', 'card_on_delivery', '2026-11-02 16:30:00'),
(3, 2, 'processing', 508.00, 'Zmaja od Bosne 12', 'Sarajevo', '71000', 'cash_on_delivery', '2026-11-14 20:15:00'),
(4, 3, 'cancelled',  329.00, 'Maršala Tita 5',    'Mostar',   '88000', 'cash_on_delivery', '2026-11-15 10:00:00');

INSERT INTO order_items (id, order_id, variant_id, quantity, unit_price) VALUES
(1, 1, 11, 1, 229.00),
(2, 2, 10, 1, 199.00),
(3, 3, 2,  1, 289.00),
(4, 3, 7,  1, 219.00),
(5, 4, 14, 1, 329.00);

INSERT INTO reviews (id, product_id, user_id, rating, comment, created_at) VALUES
(1, 1, 2, 5, 'Best fitting tracksuit I own. The velour feels premium.', '2026-10-12 19:00:00'),
(2, 1, 3, 4, 'Great quality, runs slightly small. Size up.',           '2026-10-03 12:30:00'),
(3, 5, 2, 4, 'Love the stripes, good material.',                        '2026-10-25 09:15:00'),
(4, 4, 3, 3, 'Comfortable, but the colour is lighter than in photos.',  '2026-11-06 17:40:00');
