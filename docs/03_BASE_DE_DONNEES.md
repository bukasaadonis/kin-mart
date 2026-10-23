# 3. Base de données MySQL/MariaDB

La base `kinmart` utilise InnoDB et utf8mb4. Les tables sont regroupées par domaines.

## Sécurité / utilisateurs
`roles`, `permissions`, `role_permissions`, `users`, `login_attempts`, `audit_logs`.

## Clientèle
`customers`, `customer_addresses`, `loyalty_accounts`, `loyalty_transactions`, `favorite_products`.

## Catalogue
`categories`, `subcategories`, `brands`, `units`, `products`, `product_images`, `product_batches`.

## Supply chain
`suppliers`, `purchase_orders`, `purchase_order_items`, `supplier_payments`.

## Stock
`stocks`, `stock_movements`, `inventories`, `inventory_items`.

## Caisse / ventes
`registers`, `cash_sessions`, `cash_expenses`, `sales`, `sale_items`, `sale_payments`, `returns`, `return_items`.

## Omnicanal
`orders`, `order_items`, `delivery_zones`, `deliveries`.

## Marketing
`promotions`, `promotion_products`, `coupons`, `coupon_usage`.

## Système
`notifications`, `settings`.

### Transaction critique : vente
Une vente POS est traitée dans une transaction SQL. Avant validation, les lignes de stock sont verrouillées (`FOR UPDATE`). Le système crée la vente et ses lignes, diminue les stocks, crée les mouvements, enregistre le paiement et met à jour la fidélité. Toute exception entraîne un `ROLLBACK`.

### Intégrité
Les codes internes sont uniques, les codes-barres sont uniques lorsqu’ils existent, les relations utilisent des clés étrangères et les colonnes fréquemment filtrées sont indexées.
