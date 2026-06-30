-- =========================================================
-- Mealtime Pet Food Shop — Database Schema
-- Target: MySQL 8.0+ / MariaDB 10.5+ (uses utf8mb4, JSON, CHECK constraints)
-- =========================================================

CREATE DATABASE IF NOT EXISTS mealtime_shop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mealtime_shop;

-- ---------------------------------------------------------
-- Categories (Dog / Cat / Small Pet, etc.)
-- ---------------------------------------------------------
CREATE TABLE categories (
  category_id     INT AUTO_INCREMENT PRIMARY KEY,
  name             VARCHAR(50)  NOT NULL UNIQUE,
  slug             VARCHAR(50)  NOT NULL UNIQUE,
  description      VARCHAR(255),
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Products
-- ---------------------------------------------------------
CREATE TABLE products (
  product_id       INT AUTO_INCREMENT PRIMARY KEY,
  category_id      INT NOT NULL,
  sku              VARCHAR(40)   NOT NULL UNIQUE,
  name             VARCHAR(150)  NOT NULL,
  description      TEXT,
  life_stage       ENUM('puppy','kitten','adult','senior','all') DEFAULT 'all',
  weight_kg        DECIMAL(6,2)  NOT NULL,
  price            DECIMAL(10,2) NOT NULL CHECK (price >= 0),
  stock_quantity   INT NOT NULL DEFAULT 0 CHECK (stock_quantity >= 0),
  is_grain_free    BOOLEAN DEFAULT FALSE,
  image_url        VARCHAR(255),
  is_active        BOOLEAN DEFAULT TRUE,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(category_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_products_category (category_id),
  INDEX idx_products_active (is_active)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Customers
-- ---------------------------------------------------------
CREATE TABLE customers (
  customer_id      INT AUTO_INCREMENT PRIMARY KEY,
  full_name        VARCHAR(120) NOT NULL,
  email            VARCHAR(150) NOT NULL UNIQUE,
  phone            VARCHAR(30),
  password_hash    VARCHAR(255) NOT NULL,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Customer addresses (a customer can have multiple)
-- ---------------------------------------------------------
CREATE TABLE addresses (
  address_id       INT AUTO_INCREMENT PRIMARY KEY,
  customer_id      INT NOT NULL,
  label            VARCHAR(40) DEFAULT 'Home',
  address_line1    VARCHAR(150) NOT NULL,
  address_line2    VARCHAR(150),
  city             VARCHAR(80)  NOT NULL,
  postal_code      VARCHAR(20)  NOT NULL,
  country          VARCHAR(80)  NOT NULL DEFAULT 'Sri Lanka',
  is_default       BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_addresses_customer (customer_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Orders
-- ---------------------------------------------------------
CREATE TABLE orders (
  order_id         INT AUTO_INCREMENT PRIMARY KEY,
  customer_id      INT NOT NULL,
  address_id       INT NOT NULL,
  status           ENUM('pending','paid','processing','shipped','delivered','cancelled')
                     NOT NULL DEFAULT 'pending',
  subtotal         DECIMAL(10,2) NOT NULL DEFAULT 0,
  delivery_fee     DECIMAL(10,2) NOT NULL DEFAULT 0,
  total            DECIMAL(10,2) NOT NULL DEFAULT 0,
  placed_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (address_id) REFERENCES addresses(address_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_orders_customer (customer_id),
  INDEX idx_orders_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Order line items
-- ---------------------------------------------------------
CREATE TABLE order_items (
  order_item_id    INT AUTO_INCREMENT PRIMARY KEY,
  order_id         INT NOT NULL,
  product_id       INT NOT NULL,
  quantity         INT NOT NULL CHECK (quantity > 0),
  unit_price       DECIMAL(10,2) NOT NULL,  -- price at time of purchase
  line_total       DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_order_items_order (order_id),
  INDEX idx_order_items_product (product_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Complaints (from the Complaints page)
-- ---------------------------------------------------------
CREATE TABLE complaints (
  complaint_id     INT AUTO_INCREMENT PRIMARY KEY,
  customer_id      INT NULL,                 -- nullable: guests can also file complaints
  order_id         INT NULL,
  full_name        VARCHAR(120) NOT NULL,
  email            VARCHAR(150) NOT NULL,
  complaint_type   ENUM('quality','delivery','wrong','billing','other') NOT NULL DEFAULT 'other',
  details          TEXT NOT NULL,
  status           ENUM('open','in_progress','resolved') NOT NULL DEFAULT 'open',
  submitted_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  resolved_at      TIMESTAMP NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_complaints_status (status),
  INDEX idx_complaints_customer (customer_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Contact / inquiry messages (from the Contact page)
-- ---------------------------------------------------------
CREATE TABLE contact_messages (
  message_id       INT AUTO_INCREMENT PRIMARY KEY,
  name             VARCHAR(120) NOT NULL,
  email            VARCHAR(150) NOT NULL,
  message          TEXT NOT NULL,
  submitted_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_resolved      BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB;

-- =========================================================
-- Seed data
-- =========================================================

INSERT INTO categories (name, slug, description) VALUES
  ('Dog', 'dog', 'Food for dogs of every life stage'),
  ('Cat', 'cat', 'Food for cats of every life stage'),
  ('Small Pet', 'small-pet', 'Food and hay for rabbits, hamsters, and other small pets');

INSERT INTO products (category_id, sku, name, description, life_stage, weight_kg, price, stock_quantity, is_grain_free, image_url) VALUES
  (1, 'DOG-CHK-5KG',  'Roast Chicken & Sweet Potato', 'Slow-roasted chicken with sweet potato and garden vegetables.', 'adult',  5.0, 24.99, 120, FALSE, '/images/dog-chicken.jpg'),
  (1, 'DOG-SAL-2KG',  'Puppy Salmon Bites',           'Soft salmon bites sized for growing puppies.',                'puppy',  2.0, 19.50, 80,  FALSE, '/images/dog-salmon.jpg'),
  (1, 'DOG-TUR-5KG',  'Senior Turkey & Pumpkin',       'Gentle, easy-to-digest recipe for senior dogs.',               'senior', 5.0, 26.00, 60,  FALSE, '/images/dog-turkey.jpg'),
  (1, 'DOG-BEEF-4KG', 'Grain-Free Beef Stew',          'Hearty beef stew recipe with zero grain fillers.',             'adult',  4.0, 28.75, 45,  TRUE,  '/images/dog-beef.jpg'),
  (2, 'CAT-TUNA-12C', 'Tuna & Sardine Pate',           'Pate-style wet food made with real tuna and sardine.',         'adult',  3.0, 22.00, 200, FALSE, '/images/cat-tuna.jpg'),
  (2, 'CAT-CHK-1.5KG','Kitten Chicken Morsels',        'Small, soft morsels designed for kittens.',                    'kitten', 1.5, 17.25, 70,  FALSE, '/images/cat-chicken.jpg'),
  (2, 'CAT-HAIR-3KG', 'Indoor Hairball Formula',       'Helps reduce hairballs for indoor adult cats.',                'adult',  3.0, 21.40, 90,  FALSE, '/images/cat-hairball.jpg'),
  (3, 'SML-HAY-2KG',  'Timothy Hay Bundle',            'Sun-dried timothy hay for rabbits and guinea pigs.',           'all',    2.0, 14.00, 150, FALSE, '/images/small-hay.jpg'),
  (3, 'SML-SEED-1KG', 'Seed & Grain Mix',              'Balanced seed and grain mix for hamsters and gerbils.',        'all',    1.0, 9.99,  140, FALSE, '/images/small-seed.jpg');

-- Sample customer (password_hash is a placeholder — always hash real passwords, e.g. with bcrypt)
INSERT INTO customers (full_name, email, phone, password_hash) VALUES
  ('Amal Perera', 'amal.perera@example.com', '+94771234567', '$2b$12$examplehashplaceholder000000000000000000000000');

INSERT INTO addresses (customer_id, label, address_line1, city, postal_code, country, is_default) VALUES
  (1, 'Home', '22 Galle Road', 'Colombo', '00300', 'Sri Lanka', TRUE);

-- Sample order: 2x Roast Chicken & Sweet Potato, 1x Timothy Hay Bundle
INSERT INTO orders (customer_id, address_id, status, subtotal, delivery_fee, total) VALUES
  (1, 1, 'paid', 63.98, 0.00, 63.98);

INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total) VALUES
  (1, 1, 2, 24.99, 49.98),
  (1, 8, 1, 14.00, 14.00);

INSERT INTO complaints (customer_id, order_id, full_name, email, complaint_type, details, status) VALUES
  (1, 1, 'Amal Perera', 'amal.perera@example.com', 'delivery', 'My order arrived three days later than the estimated delivery date.', 'resolved'),
  (NULL, NULL, 'Nadia Fernando', 'nadia.f@example.com', 'quality', 'One of the bags of beef stew arrived with a torn seal.', 'in_progress'),
  (NULL, NULL, 'Ruwan Silva', 'ruwan.silva@example.com', 'wrong', 'I ordered the senior turkey recipe but received puppy salmon bites instead.', 'open');
