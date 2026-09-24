CREATE DATABASE IF NOT EXISTS `glamourgems` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `glamourgems`;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(40) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    metal VARCHAR(80) NOT NULL,
    jewellery_type VARCHAR(100) NOT NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    sku VARCHAR(80) NOT NULL UNIQUE,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    sale_price DECIMAL(12,2) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    description TEXT,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    heading VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS curated_looks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS new_arrivals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    old_price DECIMAL(12,2) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS instagram_reels (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    reel_url VARCHAR(500) DEFAULT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    video_file VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS journey_numbers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    icon VARCHAR(100) DEFAULT NULL,
    number_value VARCHAR(80) NOT NULL,
    title VARCHAR(180) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    subject VARCHAR(180) DEFAULT NULL,
    message TEXT NOT NULL,
    status ENUM('unread','read') NOT NULL DEFAULT 'unread',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS newsletter (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(120) NOT NULL,
    location VARCHAR(120) DEFAULT NULL,
    review TEXT NOT NULL,
    rating DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cart (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    new_arrival_id INT UNSIGNED DEFAULT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cart_user (user_id),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_new_arrival FOREIGN KEY (new_arrival_id) REFERENCES new_arrivals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS wishlist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (user_id, product_id),
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS new_arrival_wishlist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    new_arrival_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_new_arrival_wishlist (user_id, new_arrival_id),
    CONSTRAINT fk_new_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_new_wishlist_item FOREIGN KEY (new_arrival_id) REFERENCES new_arrivals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(60) NOT NULL UNIQUE,
    user_id INT UNSIGNED DEFAULT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_email VARCHAR(190) NOT NULL,
    customer_phone VARCHAR(30) DEFAULT NULL,
    shipping_address TEXT NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_method VARCHAR(80) NOT NULL,
    payment_status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    order_status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orders_user (user_id),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    product_name VARCHAR(180) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS collection_gold_products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(180) NOT NULL, price DECIMAL(12,2) NOT NULL DEFAULT 0, image VARCHAR(255) DEFAULT NULL, display_order INT NOT NULL DEFAULT 0, status ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS collection_silver_products LIKE collection_gold_products;
CREATE TABLE IF NOT EXISTS collection_rose_gold_products LIKE collection_gold_products;
CREATE TABLE IF NOT EXISTS collection_diamond_products LIKE collection_gold_products;
CREATE TABLE IF NOT EXISTS collection_bridal_products LIKE collection_gold_products;
CREATE TABLE IF NOT EXISTS collection_groom_products LIKE collection_gold_products;
CREATE TABLE IF NOT EXISTS collection_kids_products LIKE collection_gold_products;

SET FOREIGN_KEY_CHECKS = 1;

INSERT IGNORE INTO admins (id, name, email, password, role) VALUES (1, 'Glamour Gems Admin', 'admin@glamourgems.com', '$2y$10$JRu/BC4oKVGSywgbIYwq5uHLAQD6sIafQeoA1wg.uGG162T5TLO.y', 'admin');
INSERT IGNORE INTO users (id, name, email, phone, password, status) VALUES (1, 'Demo Customer', 'customer@glamourgems.com', '9876543210', '$2y$10$cnOW37wgrD9HkSRCoi65seN9ydORlngKDbrXu.ASU8rU6PpHoTAfS', 'active');

INSERT IGNORE INTO categories (id, name, slug, description, image, status) VALUES
(1, 'Rings', 'rings', 'Elegant rings for every occasion.', 'category-rings.jpg', 'active'),
(2, 'Necklaces', 'necklaces', 'Graceful necklaces with a modern finish.', 'category-necklaces.jpg', 'active'),
(3, 'Earrings', 'earrings', 'Statement and everyday earrings.', 'category-earrings.jpg', 'active'),
(4, 'Bracelets', 'bracelets', 'Refined bracelets crafted to be treasured.', 'category-bracelets.jpg', 'active'),
(5, 'Bridal', 'bridal', 'Celebrate your forever with bridal jewellery.', 'bridal.jpg', 'active');
INSERT IGNORE INTO materials (id, name, slug, image, status) VALUES
(1, 'Gold', 'gold', 'material-gold.jpg', 'active'),
(2, 'Silver', 'silver', 'material-silver.jpg', 'active'),
(3, 'Diamond', 'diamond', 'material-diamond.jpg', 'active');

INSERT IGNORE INTO products (id, category_id, metal, jewellery_type, name, slug, sku, price, sale_price, stock, image, description, status) VALUES
(1, 1, '18K Gold', 'Ring', 'Aurora Diamond Ring', 'aurora-diamond-ring', 'GG-RNG-001', 28500, 24900, 12, 'product_default.jpg', 'A brilliant diamond ring with a warm 18K gold setting.', 'active'),
(2, 2, '18K Gold', 'Necklace', 'Celeste Gold Necklace', 'celeste-gold-necklace', 'GG-NCK-001', 42000, 38900, 8, 'product_default.jpg', 'A graceful necklace designed for unforgettable evenings.', 'active'),
(3, 3, 'Sterling Silver', 'Earrings', 'Luna Pearl Drops', 'luna-pearl-drops', 'GG-EAR-001', 9800, 7990, 20, 'product_default.jpg', 'Pearl drop earrings with a polished silver finish.', 'active'),
(4, 4, 'Rose Gold', 'Bracelet', 'Serene Charm Bracelet', 'serene-charm-bracelet', 'GG-BRC-001', 16500, NULL, 15, 'product_default.jpg', 'A delicate bracelet with an understated charm detail.', 'active'),
(5, 1, 'White Gold', 'Ring', 'Celestial Halo Ring', 'celestial-halo-ring', 'GG-RNG-002', 36500, 32900, 10, 'product5.jpg', 'A luminous halo ring for modern celebrations.', 'active'),
(6, 2, 'Yellow Gold', 'Necklace', 'Solstice Pendant', 'solstice-pendant', 'GG-NCK-002', 28900, 25900, 14, 'product6.jpg', 'A refined pendant with a warm golden glow.', 'active'),
(7, 3, 'Diamond', 'Earrings', 'Starlight Studs', 'starlight-studs', 'GG-EAR-002', 21900, 18900, 18, 'product7.jpg', 'Elegant diamond studs designed for everyday sparkle.', 'active'),
(8, 4, 'Rose Gold', 'Bracelet', 'Rosette Tennis Bracelet', 'rosette-tennis-bracelet', 'GG-BRC-002', 45500, 41900, 7, 'product8.jpg', 'A graceful tennis bracelet with a rose-gold finish.', 'active'),
(9, 2, 'Sterling Silver', 'Necklace', 'Moonlit Chain', 'moonlit-chain', 'GG-NCK-003', 14900, 12900, 22, 'product9.jpg', 'A versatile chain for effortless layering.', 'active'),
(10, 3, '18K Gold', 'Earrings', 'Golden Petal Hoops', 'golden-petal-hoops', 'GG-EAR-003', 17900, 15900, 16, 'product10.jpg', 'Sculptural hoops with a polished petal silhouette.', 'active');

INSERT IGNORE INTO banners (id, title, heading, description, image, display_order, status) VALUES
(1, 'Timeless brilliance', 'Jewellery that tells your story', 'Discover handcrafted pieces made for your most memorable moments.', 'banner-1.jpg', 1, 1),
(2, 'The new edit', 'Meet your next signature piece', 'A considered collection of modern classics.', 'banner-2.jpg', 2, 1);
INSERT IGNORE INTO curated_looks (id, title, price, image, display_order, status) VALUES
(1, 'The Celebration Edit', 54900, 'curated-1.jpg', 1, 'active'),
(2, 'Everyday Radiance', 22500, 'curated-2.jpg', 2, 'active'),
(3, 'Modern Heirlooms', 31500, 'product3.jpg', 3, 'active'),
(4, 'Diamond Evening Edit', 47500, 'product4.jpg', 4, 'active'),
(5, 'Rose Gold Romance', 28900, 'product5.jpg', 5, 'active'),
(6, 'The Golden Hour', 33900, 'product6.jpg', 6, 'active'),
(7, 'Pearl Light', 25900, 'product7.jpg', 7, 'active'),
(8, 'Midnight Sparkle', 41900, 'product8.jpg', 8, 'active');
INSERT IGNORE INTO new_arrivals (id, title, price, old_price, image, display_order, status) VALUES
(1, 'Iris Solitaire Pendant', 19900, 22900, 'new-arrival-1.jpg', 1, 'active'),
(2, 'Noor Gold Hoops', 12900, 14900, 'new-arrival-2.jpg', 2, 'active'),
(3, 'Aster Pearl Earrings', 10900, 12900, 'arrival3.jpg', 3, 'active'),
(4, 'Mira Gold Bracelet', 17900, 20900, 'arrival4.jpg', 4, 'active'),
(5, 'Elara Crystal Ring', 14900, 16900, 'arrival5.jpg', 5, 'active'),
(6, 'Sia Layered Necklace', 23900, 26900, 'arrival6.jpg', 6, 'active');
INSERT IGNORE INTO instagram_reels (id, title, reel_url, thumbnail, status, display_order) VALUES
(1, 'The art of gifting', 'https://www.instagram.com/', 'reel-1.jpg', 'active', 1),
(2, 'Made to be remembered', 'https://www.instagram.com/', 'reel-2.jpg', 'active', 2),
(3, 'Details that sparkle', 'https://www.instagram.com/', NULL, 'active', 3),
(4, 'Crafted for your moment', 'https://www.instagram.com/', NULL, 'active', 4);
INSERT IGNORE INTO journey_numbers (id, icon, number_value, title, display_order, status) VALUES
(1, 'fa-gem', '10K+', 'Happy customers', 1, 'active'),
(2, 'fa-award', '15+', 'Years of craft', 2, 'active'),
(3, 'fa-heart', '100%', 'Made with care', 3, 'active');
INSERT IGNORE INTO customer_reviews (id, customer_name, location, review, rating, image, status) VALUES
(1, 'Aarohi Shah', 'Mumbai', 'The ring looked even more beautiful in person. The finishing is exquisite.', 5.0, 'review-default.jpg', 'active'),
(2, 'Riya Mehta', 'Ahmedabad', 'Beautiful packaging, quick delivery and a truly premium piece.', 5.0, 'review-default.jpg', 'active');
INSERT IGNORE INTO newsletter_subscribers (id, email, status) VALUES (1, 'hello@example.com', 'active');
INSERT IGNORE INTO newsletter (id, email) VALUES (1, 'hello@example.com');

INSERT IGNORE INTO collection_gold_products (id, name, price, image, display_order, status) VALUES (1, 'Imperial Gold Chain', 32000, 'collection-gold-1.jpg', 1, 'active');
INSERT IGNORE INTO collection_silver_products (id, name, price, image, display_order, status) VALUES (1, 'Silver Halo Pendant', 8900, 'collection-silver-1.jpg', 1, 'active');
INSERT IGNORE INTO collection_rose_gold_products (id, name, price, image, display_order, status) VALUES (1, 'Blush Rose Bracelet', 14500, 'collection-rose-1.jpg', 1, 'active');
INSERT IGNORE INTO collection_diamond_products (id, name, price, image, display_order, status) VALUES (1, 'Eternal Diamond Studs', 36500, 'collection-diamond-1.jpg', 1, 'active');
INSERT IGNORE INTO collection_bridal_products (id, name, price, image, display_order, status) VALUES (1, 'Bridal Heritage Set', 98500, 'collection-bridal-1.jpg', 1, 'active');
INSERT IGNORE INTO collection_groom_products (id, name, price, image, display_order, status) VALUES (1, 'Groom Classic Cufflinks', 11900, 'collection-groom-1.jpg', 1, 'active');
INSERT IGNORE INTO collection_kids_products (id, name, price, image, display_order, status) VALUES (1, 'Little Star Bracelet', 4900, 'collection-kids-1.jpg', 1, 'active');

INSERT IGNORE INTO orders (id, order_number, user_id, customer_name, customer_email, customer_phone, shipping_address, total_amount, payment_method, payment_status, order_status) VALUES
(1, 'GG-DEMO-1001', 1, 'Demo Customer', 'customer@glamourgems.com', '9876543210', '21 Rose Avenue, Ahmedabad, Gujarat 380001', 24900, 'UPI', 'Paid', 'Delivered');
INSERT IGNORE INTO order_items (id, order_id, product_id, product_name, price, quantity, total) VALUES
(1, 1, 1, 'Aurora Diamond Ring', 24900, 1, 24900);

UPDATE categories SET image = CASE id
    WHEN 1 THEN 'women.jpg'
    WHEN 2 THEN 'men.jpg'
    WHEN 3 THEN 'kids.jpg'
    WHEN 4 THEN 'groom.jpg'
    WHEN 5 THEN 'bridal.jpg'
    ELSE image END;
UPDATE banners SET image = CASE id WHEN 1 THEN 'slide1.jpg' WHEN 2 THEN 'slide2.jpg' ELSE image END;
UPDATE materials SET image = CASE id WHEN 1 THEN 'gold.jpg' WHEN 2 THEN 'silver.jpg' WHEN 3 THEN 'diamond.jpg' ELSE image END;
UPDATE curated_looks SET image = CASE id WHEN 1 THEN 'product1.jpg' WHEN 2 THEN 'product2.jpg' WHEN 3 THEN 'product3.jpg' WHEN 4 THEN 'product4.jpg' WHEN 5 THEN 'product5.jpg' WHEN 6 THEN 'product6.jpg' WHEN 7 THEN 'product7.jpg' WHEN 8 THEN 'product8.jpg' ELSE image END;
UPDATE new_arrivals SET image = CASE id WHEN 1 THEN 'arrival1.jpg' WHEN 2 THEN 'arrival2.jpg' WHEN 3 THEN 'arrival3.jpg' WHEN 4 THEN 'arrival4.jpg' WHEN 5 THEN 'arrival5.jpg' WHEN 6 THEN 'arrival6.jpg' ELSE image END;
UPDATE customer_reviews SET image = CASE id WHEN 1 THEN 'review1.jpg' WHEN 2 THEN 'review2.jpg' ELSE image END;
UPDATE products SET image = CASE id
    WHEN 1 THEN 'product1.jpg'
    WHEN 2 THEN 'product2.jpg'
    WHEN 3 THEN 'product3.jpg'
    WHEN 4 THEN 'product4.jpg'
    WHEN 5 THEN 'product5.jpg'
    WHEN 6 THEN 'product6.jpg'
    WHEN 7 THEN 'product7.jpg'
    WHEN 8 THEN 'product8.jpg'
    WHEN 9 THEN 'product9.jpg'
    WHEN 10 THEN 'product10.jpg'
    ELSE image END;
UPDATE instagram_reels SET video_file = CASE id
    WHEN 1 THEN 'reel1.mp4'
    WHEN 2 THEN 'reel2.mp4'
    WHEN 3 THEN 'reel3.mp4'
    WHEN 4 THEN 'reel4.mp4'
    ELSE video_file END,
    thumbnail = NULL;
UPDATE collection_gold_products SET image = 'women-necklace.jpg' WHERE id = 1;
UPDATE collection_silver_products SET image = 'women-necklace.jpg' WHERE id = 1;
UPDATE collection_rose_gold_products SET image = 'women-necklace.jpg' WHERE id = 1;
UPDATE collection_diamond_products SET image = 'women-necklace.jpg' WHERE id = 1;
UPDATE collection_bridal_products SET image = 'diamond-bridal-set.jpg' WHERE id = 1;
UPDATE collection_groom_products SET image = 'silver-chain.jpg' WHERE id = 1;
UPDATE collection_kids_products SET image = 'silver-chain.jpg' WHERE id = 1;

SET FOREIGN_KEY_CHECKS = 1;
