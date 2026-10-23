-- ==============================================================
-- KIN-MART Super Marché
-- Base de données complète pour MySQL / MariaDB (XAMPP)
-- Contient : structure + données initiales + données de démonstration
-- Import direct via phpMyAdmin
-- Base : kinmart
-- ==============================================================

-- KIN-MART - Base de données principale
-- Compatible MySQL 8 / MariaDB (XAMPP)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
CREATE DATABASE IF NOT EXISTS kinmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kinmart;

DROP TABLE IF EXISTS audit_logs, notifications, favorite_products, coupon_usage, coupons, promotion_products, promotions,
deliveries, order_items, orders, delivery_zones, return_items, `returns`, sale_payments, cash_expenses, sale_items, sales, cash_sessions, registers,
inventory_items, inventories, stock_movements, stocks, supplier_payments, purchase_order_items, purchase_orders,
product_batches, product_images, products, suppliers, units, brands, subcategories, categories, customer_addresses,
loyalty_transactions, loyalty_accounts, users, customers, role_permissions, permissions, roles, settings, login_attempts;

CREATE TABLE roles (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(50) NOT NULL UNIQUE,
 label VARCHAR(100) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(100) NOT NULL UNIQUE,
 label VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
 role_id INT UNSIGNED NOT NULL,
 permission_id INT UNSIGNED NOT NULL,
 PRIMARY KEY(role_id, permission_id),
 FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE,
 FOREIGN KEY(permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE customers (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(30) NOT NULL UNIQUE,
 first_name VARCHAR(100) NOT NULL,
 last_name VARCHAR(100) NOT NULL,
 gender ENUM('M','F','AUTRE') NULL,
 phone VARCHAR(30) NULL,
 email VARCHAR(150) NULL UNIQUE,
 address VARCHAR(255) NULL,
 segment ENUM('NOUVEAU','REGULIER','FIDELE','VIP','INACTIF') NOT NULL DEFAULT 'NOUVEAU',
 total_spent DECIMAL(14,2) NOT NULL DEFAULT 0,
 order_count INT UNSIGNED NOT NULL DEFAULT 0,
 status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_customer_phone(phone), INDEX idx_customer_segment(segment)
) ENGINE=InnoDB;

CREATE TABLE users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 role_id INT UNSIGNED NOT NULL,
 customer_id INT UNSIGNED NULL,
 username VARCHAR(80) NOT NULL UNIQUE,
 email VARCHAR(150) NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 full_name VARCHAR(160) NOT NULL,
 phone VARCHAR(30) NULL,
 avatar VARCHAR(255) NULL,
 status ENUM('ACTIVE','INACTIVE','LOCKED') NOT NULL DEFAULT 'ACTIVE',
 force_password_change TINYINT(1) NOT NULL DEFAULT 0,
 last_login_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(role_id) REFERENCES roles(id),
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
 INDEX idx_users_role(role_id), INDEX idx_users_status(status)
) ENGINE=InnoDB;

CREATE TABLE login_attempts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 login VARCHAR(150) NOT NULL,
 ip_address VARCHAR(64) NULL,
 success TINYINT(1) NOT NULL DEFAULT 0,
 attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_login_attempt(login, attempted_at)
) ENGINE=InnoDB;

CREATE TABLE customer_addresses (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 customer_id INT UNSIGNED NOT NULL,
 label VARCHAR(80) DEFAULT 'Adresse', commune VARCHAR(100), quartier VARCHAR(100), avenue VARCHAR(150),
 number VARCHAR(40), reference_text VARCHAR(255), phone VARCHAR(30), is_default TINYINT(1) DEFAULT 0,
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL UNIQUE, slug VARCHAR(140) NOT NULL UNIQUE, description TEXT NULL,
 status ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE subcategories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_id INT UNSIGNED NOT NULL, name VARCHAR(120) NOT NULL,
 slug VARCHAR(140) NOT NULL UNIQUE, status ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
 FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE brands (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL UNIQUE, status ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'
) ENGINE=InnoDB;
CREATE TABLE units (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80) NOT NULL, symbol VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;
CREATE TABLE suppliers (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 company_name VARCHAR(180) NOT NULL, contact_name VARCHAR(150), phone VARCHAR(30), email VARCHAR(150), address VARCHAR(255),
 lead_time_days INT UNSIGNED NOT NULL DEFAULT 3, debt DECIMAL(14,2) NOT NULL DEFAULT 0,
 status ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_supplier_name(company_name)
) ENGINE=InnoDB;

CREATE TABLE products (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 internal_code VARCHAR(50) NOT NULL UNIQUE,
 barcode VARCHAR(80) NULL UNIQUE,
 name VARCHAR(180) NOT NULL,
 description TEXT NULL,
 category_id INT UNSIGNED NOT NULL,
 subcategory_id INT UNSIGNED NULL,
 brand_id INT UNSIGNED NULL,
 supplier_id INT UNSIGNED NULL,
 unit_id INT UNSIGNED NOT NULL,
 purchase_price DECIMAL(14,2) NOT NULL DEFAULT 0,
 sale_price DECIMAL(14,2) NOT NULL DEFAULT 0,
 tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
 min_stock DECIMAL(12,3) NOT NULL DEFAULT 5,
 max_stock DECIMAL(12,3) NOT NULL DEFAULT 100,
 location VARCHAR(100) NULL,
 manufacturing_date DATE NULL,
 expiration_date DATE NULL,
 image VARCHAR(255) NULL,
 status ENUM('ACTIVE','INACTIVE','ARCHIVED') DEFAULT 'ACTIVE',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(category_id) REFERENCES categories(id),
 FOREIGN KEY(subcategory_id) REFERENCES subcategories(id) ON DELETE SET NULL,
 FOREIGN KEY(brand_id) REFERENCES brands(id) ON DELETE SET NULL,
 FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
 FOREIGN KEY(unit_id) REFERENCES units(id),
 INDEX idx_products_name(name), INDEX idx_products_category(category_id), INDEX idx_products_exp(expiration_date)
) ENGINE=InnoDB;

CREATE TABLE product_images (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id INT UNSIGNED NOT NULL, path VARCHAR(255) NOT NULL, is_primary TINYINT(1) DEFAULT 0,
 FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE product_batches (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id INT UNSIGNED NOT NULL, batch_no VARCHAR(80), quantity DECIMAL(12,3) NOT NULL,
 expiration_date DATE NULL, purchase_price DECIMAL(14,2) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE, INDEX idx_batch_exp(expiration_date)
) ENGINE=InnoDB;
CREATE TABLE stocks (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id INT UNSIGNED NOT NULL UNIQUE, quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
 reserved_quantity DECIMAL(12,3) NOT NULL DEFAULT 0, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE stock_movements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id INT UNSIGNED NOT NULL,
 type ENUM('ENTREE','VENTE','RETOUR_CLIENT','RETOUR_FOURNISSEUR','CASSE','PERTE','EXPIRATION','AJUSTEMENT','TRANSFERT','RESERVATION','LIBERATION_RESERVATION') NOT NULL,
 quantity DECIMAL(12,3) NOT NULL, stock_before DECIMAL(12,3) NOT NULL, stock_after DECIMAL(12,3) NOT NULL,
 user_id INT UNSIGNED NULL, reason VARCHAR(255) NULL, reference_no VARCHAR(100) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(product_id) REFERENCES products(id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_stock_prod_date(product_id, created_at), INDEX idx_stock_type(type)
) ENGINE=InnoDB;

CREATE TABLE purchase_orders (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_no VARCHAR(50) NOT NULL UNIQUE, supplier_id INT UNSIGNED NOT NULL,
 status ENUM('BROUILLON','COMMANDE','PARTIELLEMENT_RECU','RECU','ANNULE') DEFAULT 'BROUILLON',
 subtotal DECIMAL(14,2) NOT NULL DEFAULT 0, discount DECIMAL(14,2) NOT NULL DEFAULT 0, total DECIMAL(14,2) NOT NULL DEFAULT 0,
 ordered_at DATETIME NULL, received_at DATETIME NULL, user_id INT UNSIGNED NOT NULL, notes TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(supplier_id) REFERENCES suppliers(id), FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE purchase_order_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, purchase_order_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL,
 quantity DECIMAL(12,3) NOT NULL, received_quantity DECIMAL(12,3) NOT NULL DEFAULT 0, unit_price DECIMAL(14,2) NOT NULL, subtotal DECIMAL(14,2) NOT NULL,
 FOREIGN KEY(purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id)
) ENGINE=InnoDB;
CREATE TABLE supplier_payments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, supplier_id INT UNSIGNED NOT NULL, purchase_order_id INT UNSIGNED NULL,
 amount DECIMAL(14,2) NOT NULL, currency ENUM('USD','CDF') DEFAULT 'USD', reference_no VARCHAR(100), paid_at DATETIME NOT NULL,
 user_id INT UNSIGNED NOT NULL, FOREIGN KEY(supplier_id) REFERENCES suppliers(id), FOREIGN KEY(purchase_order_id) REFERENCES purchase_orders(id) ON DELETE SET NULL,
 FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE inventories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, inventory_no VARCHAR(50) NOT NULL UNIQUE, status ENUM('OUVERT','VALIDE','ANNULE') DEFAULT 'OUVERT',
 notes TEXT NULL, user_id INT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, validated_at DATETIME NULL,
 FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE inventory_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, inventory_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL,
 system_qty DECIMAL(12,3) NOT NULL, physical_qty DECIMAL(12,3) NOT NULL, difference_qty DECIMAL(12,3) NOT NULL,
 FOREIGN KEY(inventory_id) REFERENCES inventories(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE registers (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(30) NOT NULL UNIQUE, name VARCHAR(100) NOT NULL, status ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'
) ENGINE=InnoDB;
CREATE TABLE cash_sessions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, register_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL,
 opening_amount DECIMAL(14,2) NOT NULL DEFAULT 0, theoretical_amount DECIMAL(14,2) NULL, closing_amount DECIMAL(14,2) NULL,
 difference_amount DECIMAL(14,2) NULL, status ENUM('OPEN','CLOSED') DEFAULT 'OPEN', opened_at DATETIME NOT NULL, closed_at DATETIME NULL,
 FOREIGN KEY(register_id) REFERENCES registers(id), FOREIGN KEY(user_id) REFERENCES users(id), INDEX idx_cash_open(user_id,status)
) ENGINE=InnoDB;

CREATE TABLE cash_expenses (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cash_session_id BIGINT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, description VARCHAR(255) NOT NULL, amount DECIMAL(14,2) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(cash_session_id) REFERENCES cash_sessions(id) ON DELETE CASCADE, FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE sales (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sale_no VARCHAR(50) NOT NULL UNIQUE, customer_id INT UNSIGNED NULL,
 cash_session_id BIGINT UNSIGNED NULL, user_id INT UNSIGNED NOT NULL, subtotal DECIMAL(14,2) NOT NULL, discount DECIMAL(14,2) NOT NULL DEFAULT 0,
 tax DECIMAL(14,2) NOT NULL DEFAULT 0, total DECIMAL(14,2) NOT NULL, cost_total DECIMAL(14,2) NOT NULL DEFAULT 0,
 status ENUM('COMPLETED','CANCELLED','REFUNDED') DEFAULT 'COMPLETED', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL, FOREIGN KEY(cash_session_id) REFERENCES cash_sessions(id) ON DELETE SET NULL,
 FOREIGN KEY(user_id) REFERENCES users(id), INDEX idx_sales_date(created_at), INDEX idx_sales_customer(customer_id)
) ENGINE=InnoDB;
CREATE TABLE sale_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sale_id BIGINT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL,
 quantity DECIMAL(12,3) NOT NULL, unit_price DECIMAL(14,2) NOT NULL, purchase_price DECIMAL(14,2) NOT NULL DEFAULT 0,
 discount DECIMAL(14,2) NOT NULL DEFAULT 0, subtotal DECIMAL(14,2) NOT NULL,
 FOREIGN KEY(sale_id) REFERENCES sales(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id), INDEX idx_saleitems_product(product_id)
) ENGINE=InnoDB;
CREATE TABLE sale_payments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sale_id BIGINT UNSIGNED NOT NULL,
 method ENUM('ESPECES','CARTE','M_PESA','AIRTEL_MONEY','ORANGE_MONEY','AFRIMONEY','MIXTE') NOT NULL,
 currency ENUM('USD','CDF') NOT NULL DEFAULT 'USD', amount DECIMAL(14,2) NOT NULL, exchange_rate DECIMAL(14,4) NOT NULL DEFAULT 1,
 reference_no VARCHAR(100) NULL, received_amount DECIMAL(14,2) NULL, change_amount DECIMAL(14,2) NULL,
 FOREIGN KEY(sale_id) REFERENCES sales(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE `returns` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, return_no VARCHAR(50) NOT NULL UNIQUE, sale_id BIGINT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL, amount DECIMAL(14,2) NOT NULL DEFAULT 0, reason VARCHAR(255) NOT NULL,
 status ENUM('APPROVED','CANCELLED') DEFAULT 'APPROVED', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(sale_id) REFERENCES sales(id), FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE return_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, return_id BIGINT UNSIGNED NOT NULL, sale_item_id BIGINT UNSIGNED NOT NULL,
 product_id INT UNSIGNED NOT NULL, quantity DECIMAL(12,3) NOT NULL, amount DECIMAL(14,2) NOT NULL,
 FOREIGN KEY(return_id) REFERENCES `returns`(id) ON DELETE CASCADE, FOREIGN KEY(sale_item_id) REFERENCES sale_items(id), FOREIGN KEY(product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE delivery_zones (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL UNIQUE, fee_usd DECIMAL(10,2) NOT NULL DEFAULT 0, status ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'
) ENGINE=InnoDB;
INSERT INTO delivery_zones(name,fee_usd) VALUES ('Gombe',3.00),('Kasa-Vubu',3.50),('Limete',4.00),('Lemba',4.50),('Ngaliema',4.00),('Autre zone Kinshasa',5.00);

CREATE TABLE orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_no VARCHAR(50) NOT NULL UNIQUE, customer_id INT UNSIGNED NOT NULL, delivery_zone_id INT UNSIGNED NULL,
 status ENUM('NOUVELLE','CONFIRMEE','EN_PREPARATION','PRETE','EN_LIVRAISON','LIVREE','ANNULEE') DEFAULT 'NOUVELLE',
 fulfillment ENUM('RETRAIT','LIVRAISON') NOT NULL DEFAULT 'RETRAIT', subtotal DECIMAL(14,2) NOT NULL, discount DECIMAL(14,2) NOT NULL DEFAULT 0,
 delivery_fee DECIMAL(14,2) NOT NULL DEFAULT 0, total DECIMAL(14,2) NOT NULL,
 commune VARCHAR(100), quartier VARCHAR(100), avenue VARCHAR(150), number VARCHAR(50), reference_text VARCHAR(255), phone VARCHAR(30), instructions TEXT,
 assigned_delivery_user_id INT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(customer_id) REFERENCES customers(id), FOREIGN KEY(delivery_zone_id) REFERENCES delivery_zones(id) ON DELETE SET NULL, FOREIGN KEY(assigned_delivery_user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_orders_status(status), INDEX idx_orders_customer(customer_id)
) ENGINE=InnoDB;
CREATE TABLE order_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL,
 quantity DECIMAL(12,3) NOT NULL, unit_price DECIMAL(14,2) NOT NULL, subtotal DECIMAL(14,2) NOT NULL,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id)
) ENGINE=InnoDB;
CREATE TABLE deliveries (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL UNIQUE, delivery_user_id INT UNSIGNED NULL,
 status ENUM('A_AFFECTER','A_RECUPERER','EN_COURS','LIVREE','ECHEC') DEFAULT 'A_AFFECTER', notes TEXT NULL,
 assigned_at DATETIME NULL, delivered_at DATETIME NULL, FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
 FOREIGN KEY(delivery_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE promotions (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, type ENUM('PERCENT','FIXED','BUY_X_GET_Y') NOT NULL,
 value DECIMAL(14,2) NOT NULL DEFAULT 0, category_id INT UNSIGNED NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL,
 min_cart DECIMAL(14,2) NOT NULL DEFAULT 0, status ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL, INDEX idx_promo_dates(start_at,end_at,status)
) ENGINE=InnoDB;
CREATE TABLE promotion_products (
 promotion_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, PRIMARY KEY(promotion_id,product_id),
 FOREIGN KEY(promotion_id) REFERENCES promotions(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE coupons (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(50) NOT NULL UNIQUE, type ENUM('PERCENT','FIXED') NOT NULL, value DECIMAL(14,2) NOT NULL,
 min_cart DECIMAL(14,2) DEFAULT 0, usage_limit INT UNSIGNED NULL, used_count INT UNSIGNED DEFAULT 0, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL,
 status ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'
) ENGINE=InnoDB;
CREATE TABLE coupon_usage (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, coupon_id INT UNSIGNED NOT NULL, customer_id INT UNSIGNED NOT NULL, order_id BIGINT UNSIGNED NULL,
 used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(coupon_id) REFERENCES coupons(id), FOREIGN KEY(customer_id) REFERENCES customers(id),
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE favorite_products (
 customer_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(customer_id,product_id), FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE loyalty_accounts (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, customer_id INT UNSIGNED NOT NULL UNIQUE, points_balance INT NOT NULL DEFAULT 0,
 tier ENUM('STANDARD','SILVER','GOLD','PLATINUM') DEFAULT 'STANDARD', updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE loyalty_transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, loyalty_account_id INT UNSIGNED NOT NULL,
 type ENUM('GAIN','UTILISATION','BONUS','ANNULATION','EXPIRATION') NOT NULL, points INT NOT NULL, reference_no VARCHAR(100), notes VARCHAR(255),
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(loyalty_account_id) REFERENCES loyalty_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL, role_name VARCHAR(50) NULL, type VARCHAR(60) NOT NULL,
 title VARCHAR(180) NOT NULL, message VARCHAR(500) NOT NULL, link VARCHAR(255) NULL, is_read TINYINT(1) DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_notif_read(is_read,created_at)
) ENGINE=InnoDB;
CREATE TABLE audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL, action VARCHAR(80) NOT NULL, module VARCHAR(80) NOT NULL,
 old_data LONGTEXT NULL, new_data LONGTEXT NULL, ip_address VARCHAR(64) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL, INDEX idx_audit_date(created_at), INDEX idx_audit_module(module)
) ENGINE=InnoDB;
CREATE TABLE settings (
 `key` VARCHAR(100) PRIMARY KEY, `value` TEXT NULL, `group` VARCHAR(80) DEFAULT 'general', updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Rôles
INSERT INTO roles(name,label) VALUES
('SUPER_ADMIN','Super administrateur'),('ADMIN','Administrateur / Gérant'),('CASHIER','Caissier'),('STOCK_MANAGER','Magasinier'),
('SALES_MANAGER','Responsable commercial'),('DELIVERY','Livreur'),('CLIENT','Client');

INSERT INTO permissions(code,label) VALUES
('dashboard.view','Voir le tableau de bord'),('products.manage','Gérer les produits'),('stocks.manage','Gérer les stocks'),('sales.create','Créer des ventes'),
('sales.view','Voir les ventes'),('customers.manage','Gérer les clients'),('orders.manage','Gérer les commandes'),('reports.view','Voir les rapports'),
('settings.manage','Gérer les paramètres'),('users.manage','Gérer les utilisateurs'),('backup.create','Créer des sauvegardes');
INSERT INTO role_permissions(role_id,permission_id) SELECT 1,id FROM permissions;

-- Catégories, unités, marques
INSERT INTO categories(name,slug) VALUES
('Alimentation','alimentation'),('Boissons','boissons'),('Fruits et légumes','fruits-legumes'),('Boulangerie','boulangerie'),
('Produits laitiers','produits-laitiers'),('Viandes','viandes'),('Surgelés','surgeles'),('Hygiène','hygiene'),('Beauté','beaute'),
('Entretien','entretien'),('Bébé','bebe'),('Maison','maison'),('Papeterie','papeterie'),('Électroménager','electromenager');
INSERT INTO units(name,symbol) VALUES ('Pièce','pc'),('Kilogramme','kg'),('Litre','L'),('Paquet','paq'),('Carton','ctn'),('Bouteille','bt');
INSERT INTO brands(name) VALUES ('KIN-MART Selection'),('Coca-Cola'),('Pepsi'),('Nido'),('Omo'),('Colgate'),('Ariel'),('Generic');
INSERT INTO registers(code,name) VALUES ('CAISSE-01','Caisse principale'),('CAISSE-02','Caisse secondaire');

-- Client de démonstration avant l'utilisateur client
INSERT INTO customers(code,first_name,last_name,phone,email,address,segment,total_spent,order_count) VALUES
('CLI-0001','Grace','Client','+243000000001','client@kinmart.local','Kinshasa','REGULIER',0,0);
INSERT INTO loyalty_accounts(customer_id,points_balance,tier) VALUES (1,25,'STANDARD');

-- Utilisateurs initiaux
INSERT INTO users(role_id,customer_id,username,email,password_hash,full_name,status) VALUES
(1,NULL,'admin','admin@kinmart.local','$2y$12$Xk.OXmeUE4N0x/YfsGBHteex3YxrQbuf9X2uSnPBAesBTdQL0dUw2','Administrateur KIN-MART','ACTIVE'),
(2,NULL,'manager','manager@kinmart.local','$2y$12$3ajyLAw3h4fzzNsKijTdjOz1z5E5iuIzV0xNhiwNA.DVAX8UcbUni','Gérant KIN-MART','ACTIVE'),
(3,NULL,'cashier','cashier@kinmart.local','$2y$12$ui0RD0eioVHAM4QpZaaNt.g.F5qoZsXCUc55cd9TvlXQRZetTemAa','Caissier Démo','ACTIVE'),
(4,NULL,'stock','stock@kinmart.local','$2y$12$RoVF7pnDDxZB9m68Jh65muC4aJQAKSqEiHcnvXTdncSSFxUrED3vm','Magasinier Démo','ACTIVE'),
(6,NULL,'delivery','delivery@kinmart.local','$2y$12$F.bG/V0z1AARumY5NOW56OX0uFp1qzvhMD.dSFTQTPeuHVVUzFhmK','Livreur Démo','ACTIVE'),
(7,1,'client','client@kinmart.local','$2y$12$lFGBhUA..YvXamoUgL5XEOsPYqjl7dXMTYuyVY9FHwc8vihNQYc4e','Grace Client','ACTIVE');

-- Paramètres KIN-MART
INSERT INTO settings(`key`,`value`,`group`) VALUES
('company_name','KIN-MART','company'),('company_subtitle','Super Marché','company'),('company_logo','public/assets/images/logo-kinmart.jpg','company'),
('company_address','','company'),('company_phone','','company'),('company_whatsapp','','company'),('company_email','','company'),
('company_rccm','','company'),('company_idnat','','company'),('company_tax_no','','company'),('company_hours','','company'),
('primary_currency','CDF','finance'),('secondary_currency','USD','finance'),('usd_cdf_rate','2850','finance'),('tax_rate','0','finance'),
('loyalty_points_per_usd','1','loyalty'),('loyalty_points_per_reward','100','loyalty'),('loyalty_reward_usd','1','loyalty'),
('low_stock_days_threshold','5','analytics'),('dormant_days','30','analytics');

SET FOREIGN_KEY_CHECKS=1;


-- =============================================================
-- DONNEES DE DEMONSTRATION / SEED
-- =============================================================

USE kinmart;
INSERT INTO suppliers(company_name,contact_name,phone,email,address,lead_time_days) VALUES ('Fournisseur Central KIN-MART','Service commercial','+243000000100','supply@example.local','Kinshasa',3),('Distributeur Boissons','Commercial','+243000000101','boissons@example.local','Kinshasa',2);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0001','6201234000017','Riz parfumé 5 kg',1,8,1,1,18.00,24.00,10,80,'Rayon 1',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),40,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0002','6201234000024','Sucre blanc 1 kg',1,8,1,1,1.20,1.70,10,160,'Rayon 1',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),80,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0003','6201234000031','Farine de maïs 2 kg',1,8,1,1,3.00,4.20,10,130,'Rayon 1',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),65,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0004','6201234000048','Huile végétale 1 L',1,8,1,1,2.30,3.20,10,180,'Rayon 1',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),90,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0005','6201234000055','Spaghetti 500 g',1,8,1,1,0.80,1.25,10,240,'Rayon 1',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),120,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0006','6201234000062','Haricots 1 kg',1,8,1,1,2.10,3.00,10,110,'Rayon 1',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),55,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0007','6201234000079','Sel iodé 500 g',1,8,1,1,0.35,0.65,10,200,'Rayon 1',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),100,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0008','6201234000086','Tomates en boîte',1,8,1,1,0.80,1.20,10,140,'Rayon 1',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),70,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0009','6201234000093','Coca-Cola 50 cl',2,2,2,1,0.55,0.90,10,300,'Rayon 2',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),150,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0010','6201234000109','Fanta 50 cl',2,8,2,1,0.55,0.90,10,280,'Rayon 2',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),140,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0011','6201234000116','Eau minérale 1,5 L',2,8,2,1,0.45,0.80,10,360,'Rayon 2',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),180,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0012','6201234000123','Jus mangue 1 L',2,8,2,1,1.20,1.90,10,120,'Rayon 2',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),60,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0013','6201234000130','Pepsi 50 cl',2,3,2,1,0.50,0.85,10,200,'Rayon 2',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),100,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0014','6201234000147','Boisson énergétique',2,8,2,1,0.80,1.40,10,140,'Rayon 2',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),70,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0015','6201234000154','Pommes 1 kg',3,8,1,1,2.00,3.20,10,80,'Rayon 3',DATE_ADD(CURDATE(), INTERVAL 22 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),40,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0016','6201234000161','Bananes 1 kg',3,8,1,1,1.10,1.80,10,110,'Rayon 3',DATE_ADD(CURDATE(), INTERVAL 23 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),55,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0017','6201234000178','Oranges 1 kg',3,8,1,1,1.50,2.50,10,90,'Rayon 3',DATE_ADD(CURDATE(), INTERVAL 24 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),45,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0018','6201234000185','Avocats lot de 4',3,8,1,1,1.30,2.10,10,70,'Rayon 3',DATE_ADD(CURDATE(), INTERVAL 25 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),35,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0019','6201234000192','Pain baguette',4,8,1,1,0.50,0.80,10,160,'Rayon 4',DATE_ADD(CURDATE(), INTERVAL 26 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),80,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0020','6201234000208','Pain complet',4,8,1,1,0.80,1.20,10,70,'Rayon 4',DATE_ADD(CURDATE(), INTERVAL 7 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),35,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0021','6201234000215','Croissant',4,8,1,1,0.45,0.75,10,100,'Rayon 4',DATE_ADD(CURDATE(), INTERVAL 8 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),50,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0022','6201234000222','Lait Nido 400 g',5,4,1,1,5.50,7.00,10,90,'Rayon 5',DATE_ADD(CURDATE(), INTERVAL 9 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),45,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0023','6201234000239','Yaourt nature',5,8,1,1,0.65,1.00,10,140,'Rayon 5',DATE_ADD(CURDATE(), INTERVAL 10 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),70,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0024','6201234000246','Fromage tranche',5,8,1,1,2.50,3.80,10,60,'Rayon 5',DATE_ADD(CURDATE(), INTERVAL 11 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),30,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0025','6201234000253','Poulet entier',6,8,1,1,5.50,7.50,10,70,'Rayon 6',DATE_ADD(CURDATE(), INTERVAL 12 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),35,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0026','6201234000260','Viande de boeuf 1 kg',6,8,1,1,6.50,9.00,10,56,'Rayon 6',DATE_ADD(CURDATE(), INTERVAL 13 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),28,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0027','6201234000277','Poisson surgelé 1 kg',7,8,1,1,4.00,5.80,10,80,'Rayon 7',DATE_ADD(CURDATE(), INTERVAL 14 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),40,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0028','6201234000284','Frites surgelées 1 kg',7,8,1,1,2.20,3.50,10,90,'Rayon 7',DATE_ADD(CURDATE(), INTERVAL 15 DAY));
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),45,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0029','6201234000291','Savon de toilette',8,8,1,1,0.50,0.90,10,200,'Rayon 8',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),100,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0030','6201234000307','Dentifrice Colgate',8,6,1,1,1.10,1.70,10,170,'Rayon 8',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),85,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0031','6201234000314','Papier hygiénique x4',8,8,1,1,1.40,2.20,10,150,'Rayon 8',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),75,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0032','6201234000321','Shampoing 400 ml',9,8,1,1,2.00,3.20,10,90,'Rayon 9',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),45,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0033','6201234000338','Lotion corporelle',9,8,1,1,2.40,3.80,10,76,'Rayon 9',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),38,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0034','6201234000345','Déodorant',9,8,1,1,1.50,2.50,10,100,'Rayon 9',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),50,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0035','6201234000352','Omo poudre 1 kg',10,5,1,1,2.00,3.00,10,130,'Rayon 10',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),65,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0036','6201234000369','Eau de javel 1 L',10,8,1,1,0.70,1.20,10,160,'Rayon 10',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),80,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0037','6201234000376','Liquide vaisselle',10,8,1,1,0.90,1.50,10,140,'Rayon 10',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),70,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0038','6201234000383','Couches bébé S',11,8,1,1,6.50,8.50,10,80,'Rayon 11',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),40,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0039','6201234000390','Lingettes bébé',11,8,1,1,1.20,2.00,10,100,'Rayon 11',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),50,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0040','6201234000406','Biberon 250 ml',11,8,1,1,1.50,2.50,10,60,'Rayon 11',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),30,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0041','6201234000413','Seau plastique',12,8,1,1,2.50,4.00,10,50,'Rayon 12',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),25,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0042','6201234000420','Balai',12,8,1,1,1.60,2.80,10,70,'Rayon 12',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),35,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0043','6201234000437','Assiettes x6',12,8,1,1,3.20,5.00,10,50,'Rayon 12',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),20,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0044','6201234000444','Cahier 100 pages',13,8,1,1,0.55,0.90,10,200,'Rayon 13',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),100,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0045','6201234000451','Stylo bleu',13,8,1,1,0.12,0.25,10,400,'Rayon 13',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),200,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0046','6201234000468','Ramette A4',13,8,1,1,4.00,5.80,10,70,'Rayon 13',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),35,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0047','6201234000475','Bouilloire électrique',14,8,1,1,12.00,18.00,10,50,'Rayon 14',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),12,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0048','6201234000482','Fer à repasser',14,8,1,1,15.00,22.00,10,50,'Rayon 14',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),10,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0049','6201234000499','Ventilateur',14,8,1,1,20.00,29.00,10,50,'Rayon 14',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),8,0);
INSERT INTO products(internal_code,barcode,name,category_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,min_stock,max_stock,location,expiration_date) VALUES('KM-0050','6201234000505','Multiprise 4 ports',14,8,1,1,4.00,6.50,10,50,'Rayon 14',NULL);
INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(LAST_INSERT_ID(),25,0);
INSERT INTO promotions(name,type,value,category_id,start_at,end_at,min_cart,status) VALUES ('Promo Boissons','PERCENT',10,2,DATE_SUB(NOW(),INTERVAL 2 DAY),DATE_ADD(NOW(),INTERVAL 20 DAY),0,'ACTIVE'),('Promo Hygiène','PERCENT',5,8,NOW(),DATE_ADD(NOW(),INTERVAL 30 DAY),0,'ACTIVE');
INSERT INTO coupons(code,type,value,min_cart,usage_limit,start_at,end_at,status) VALUES ('KINMART10','PERCENT',10,20,100,NOW(),DATE_ADD(NOW(),INTERVAL 60 DAY),'ACTIVE');
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-040',1,3,48.00,48.00,36.00,DATE_SUB(NOW(),INTERVAL 40 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,1,2,24.00,18.00,48.00); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',48.00,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-2) WHERE product_id=1;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-038',1,3,2.40,2.40,1.50,DATE_SUB(NOW(),INTERVAL 38 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,19,3,0.80,0.50,2.40); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',2.40,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-3) WHERE product_id=19;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-036',1,3,2.50,2.50,1.50,DATE_SUB(NOW(),INTERVAL 36 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,17,1,2.50,1.50,2.50); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',2.50,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-1) WHERE product_id=17;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-034',1,3,6.40,6.40,4.00,DATE_SUB(NOW(),INTERVAL 34 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,15,2,3.20,2.00,6.40); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',6.40,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-2) WHERE product_id=15;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-032',1,3,2.55,2.55,1.50,DATE_SUB(NOW(),INTERVAL 32 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,13,3,0.85,0.50,2.55); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',2.55,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-3) WHERE product_id=13;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-030',1,3,0.80,0.80,0.45,DATE_SUB(NOW(),INTERVAL 30 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,11,1,0.80,0.45,0.80); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',0.80,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-1) WHERE product_id=11;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-028',1,3,1.80,1.80,1.10,DATE_SUB(NOW(),INTERVAL 28 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,9,2,0.90,0.55,1.80); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',1.80,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-2) WHERE product_id=9;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-026',1,3,1.95,1.95,1.05,DATE_SUB(NOW(),INTERVAL 26 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,7,3,0.65,0.35,1.95); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',1.95,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-3) WHERE product_id=7;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-024',1,3,1.25,1.25,0.80,DATE_SUB(NOW(),INTERVAL 24 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,5,1,1.25,0.80,1.25); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',1.25,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-1) WHERE product_id=5;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-022',1,3,8.40,8.40,6.00,DATE_SUB(NOW(),INTERVAL 22 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,3,2,4.20,3.00,8.40); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',8.40,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-2) WHERE product_id=3;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-020',1,3,72.00,72.00,54.00,DATE_SUB(NOW(),INTERVAL 20 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,1,3,24.00,18.00,72.00); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',72.00,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-3) WHERE product_id=1;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-018',1,3,0.80,0.80,0.50,DATE_SUB(NOW(),INTERVAL 18 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,19,1,0.80,0.50,0.80); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',0.80,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-1) WHERE product_id=19;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-016',1,3,5.00,5.00,3.00,DATE_SUB(NOW(),INTERVAL 16 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,17,2,2.50,1.50,5.00); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',5.00,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-2) WHERE product_id=17;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-014',1,3,9.60,9.60,6.00,DATE_SUB(NOW(),INTERVAL 14 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,15,3,3.20,2.00,9.60); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',9.60,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-3) WHERE product_id=15;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-012',1,3,0.85,0.85,0.50,DATE_SUB(NOW(),INTERVAL 12 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,13,1,0.85,0.50,0.85); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',0.85,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-1) WHERE product_id=13;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-010',1,3,1.60,1.60,0.90,DATE_SUB(NOW(),INTERVAL 10 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,11,2,0.80,0.45,1.60); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',1.60,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-2) WHERE product_id=11;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-008',1,3,2.70,2.70,1.65,DATE_SUB(NOW(),INTERVAL 8 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,9,3,0.90,0.55,2.70); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',2.70,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-3) WHERE product_id=9;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-006',1,3,0.65,0.65,0.35,DATE_SUB(NOW(),INTERVAL 6 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,7,1,0.65,0.35,0.65); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',0.65,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-1) WHERE product_id=7;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-004',1,3,2.50,2.50,1.60,DATE_SUB(NOW(),INTERVAL 4 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,5,2,1.25,0.80,2.50); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',2.50,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-2) WHERE product_id=5;
INSERT INTO sales(sale_no,customer_id,user_id,subtotal,total,cost_total,created_at) VALUES('DEMO-002',1,3,12.60,12.60,9.00,DATE_SUB(NOW(),INTERVAL 2 DAY));
SET @sid=LAST_INSERT_ID(); INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(@sid,3,3,4.20,3.00,12.60); INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate) VALUES(@sid,'ESPECES','USD',12.60,1);
UPDATE stocks SET quantity=GREATEST(0,quantity-3) WHERE product_id=3;
UPDATE customers SET total_spent=184.35,order_count=20,segment='FIDELE' WHERE id=1;
UPDATE loyalty_accounts SET points_balance=points_balance+184,tier='SILVER' WHERE customer_id=1;
INSERT INTO orders(order_no,customer_id,status,fulfillment,subtotal,total,phone,created_at) VALUES('CMD-DEMO-001',1,'NOUVELLE','RETRAIT',5.10,5.10,'+243000000001',NOW()); SET @oid=LAST_INSERT_ID(); INSERT INTO order_items(order_id,product_id,quantity,unit_price,subtotal) VALUES(@oid,2,3,1.70,5.10); UPDATE stocks SET reserved_quantity=reserved_quantity+3 WHERE product_id=2; INSERT INTO deliveries(order_id,status) VALUES(@oid,'A_AFFECTER');


-- =============================================================
-- LOCALISATION RDC / FC / IMAGES PRODUITS / FOURNISSEURS
-- =============================================================
UPDATE settings SET value='CDF' WHERE `key`='primary_currency';
UPDATE settings SET value='USD' WHERE `key`='secondary_currency';
UPDATE settings SET value='2850' WHERE `key`='usd_cdf_rate';

UPDATE suppliers SET company_name='Nestlé Distribution RDC', contact_name='Service commercial', phone='+243 000 100 001', email='distribution.rdc@demo.local', address='Kinshasa', lead_time_days=4 WHERE id=1;
UPDATE suppliers SET company_name='BRALIMA SA', contact_name='Service commercial', phone='+243 000 100 002', email='contact@demo.local', address='Kinshasa', lead_time_days=2 WHERE id=2;
INSERT INTO suppliers(company_name,contact_name,phone,email,address,lead_time_days) VALUES
('BRACONGO SA','Service commercial','+243 000 100 003','contact@demo.local','Kinshasa',2),
('MARSAVCO SA','Service commercial','+243 000 100 004','contact@demo.local','Kinshasa',3),
('Kin Hygiene Distribution','Service commercial','+243 000 100 005','contact@demo.local','Kinshasa',3),
('Coopérative Maraîchère de Kinshasa','Approvisionnement frais','+243 000 100 006','contact@demo.local','Kinshasa',1),
('Boulangerie Pâtisserie Kin','Approvisionnement','+243 000 100 007','contact@demo.local','Kinshasa',1),
('Congo Fresh Foods','Approvisionnement viandes','+243 000 100 008','contact@demo.local','Kinshasa',2),
('BabyCare Distribution RDC','Service commercial','+243 000 100 009','contact@demo.local','Kinshasa',3),
('Maison & Bureau Distribution','Service commercial','+243 000 100 010','contact@demo.local','Kinshasa',4);

UPDATE products SET purchase_price=ROUND(purchase_price*2850,2), sale_price=ROUND(sale_price*2850,2);
UPDATE sales SET subtotal=ROUND(subtotal*2850,2), total=ROUND(total*2850,2), cost_total=ROUND(cost_total*2850,2);
UPDATE sale_items SET unit_price=ROUND(unit_price*2850,2), purchase_price=ROUND(purchase_price*2850,2), subtotal=ROUND(subtotal*2850,2);
UPDATE sale_payments SET currency='CDF', amount=ROUND(amount*2850,2), exchange_rate=1;
UPDATE orders SET subtotal=ROUND(subtotal*2850,2), total=ROUND(total*2850,2);
UPDATE order_items SET unit_price=ROUND(unit_price*2850,2), subtotal=ROUND(subtotal*2850,2);
UPDATE customers SET total_spent=ROUND(total_spent*2850,2);
UPDATE coupons SET min_cart=ROUND(min_cart*2850,2) WHERE min_cart IS NOT NULL;

UPDATE products SET supplier_id=1 WHERE id BETWEEN 1 AND 8;
UPDATE products SET supplier_id=2 WHERE id BETWEEN 9 AND 11;
UPDATE products SET supplier_id=3 WHERE id BETWEEN 12 AND 14;
UPDATE products SET supplier_id=6 WHERE id BETWEEN 15 AND 18;
UPDATE products SET supplier_id=7 WHERE id BETWEEN 19 AND 21;
UPDATE products SET supplier_id=1 WHERE id BETWEEN 22 AND 24;
UPDATE products SET supplier_id=8 WHERE id BETWEEN 25 AND 28;
UPDATE products SET supplier_id=5 WHERE id BETWEEN 29 AND 31;
UPDATE products SET supplier_id=4 WHERE id BETWEEN 32 AND 37;
UPDATE products SET supplier_id=9 WHERE id BETWEEN 38 AND 40;
UPDATE products SET supplier_id=10 WHERE id BETWEEN 41 AND 50;

UPDATE products SET image='products/epicerie-rdc.png' WHERE id BETWEEN 1 AND 8;
UPDATE products SET image='products/boissons-rdc.png' WHERE id BETWEEN 9 AND 14;
UPDATE products SET image='products/fruits-legumes-rdc.png' WHERE id BETWEEN 15 AND 18;
UPDATE products SET image='products/boulangerie-laitiers-rdc.png' WHERE id BETWEEN 19 AND 24;
UPDATE products SET image='products/viandes-surgeles-rdc.png' WHERE id BETWEEN 25 AND 28;
UPDATE products SET image='products/hygiene-rdc.png' WHERE id BETWEEN 29 AND 31;
UPDATE products SET image='products/beaute-rdc.png' WHERE id BETWEEN 32 AND 34;
UPDATE products SET image='products/entretien-rdc.png' WHERE id BETWEEN 35 AND 37;
UPDATE products SET image='products/bebe-rdc.png' WHERE id BETWEEN 38 AND 40;
UPDATE products SET image='products/maison-bureau-electro-rdc.png' WHERE id BETWEEN 41 AND 50;
