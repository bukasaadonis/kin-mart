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
