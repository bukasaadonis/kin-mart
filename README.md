# KIN-MART — Plateforme intelligente de gestion de supermarché

**Sujet académique :** *Conception et réalisation d’une plateforme web intelligente de gestion d’un supermarché : ventes, stocks, commandes et fidélisation clientèle — Cas de KIN-MART Super Marché.*

KIN-MART est une application PHP/MySQL conçue pour fonctionner **en local sous XAMPP**, sans dépendance à Internet. Le projet regroupe dans une base de données unique : back-office, caisse POS, stocks, approvisionnements, boutique client, commandes/livraisons, fidélité, promotions, rapports, audit et aide à la décision.

## Stack

- PHP 8.x orienté objet, MVC personnalisé, PDO et requêtes préparées
- MySQL/MariaDB (InnoDB, utf8mb4, clés étrangères, index, transactions)
- Bootstrap 5.3.6 embarqué localement
- JavaScript / Fetch, moteur Canvas local pour les graphiques hors-ligne
- Exports CSV, Excel-compatible `.xls` et PDF simple intégré
- XAMPP / Apache, URLs propres via `.htaccess`

## Démarrage rapide

1. Copier le dossier `KinMart` dans `C:\xampp\htdocs\`.
2. Démarrer Apache et MySQL.
3. Importer `database/kinmart.sql` dans phpMyAdmin.
4. Facultatif : importer `database/seed.sql` pour charger ~50 produits et des données de démonstration.
5. Ouvrir `http://localhost/KinMart/`.
6. Connexion administrateur : `admin` / `KinMart@2026`.

Lire `INSTALLATION.txt` pour les détails, notamment si MySQL utilise le port 3307.

## Modules opérationnels

- **KIN-MART ADMIN** : dashboard, utilisateurs, paramètres, audit, sauvegarde SQL.
- **KIN-MART POS** : ouverture/clôture de caisse, scan code-barres, paiements USD/CDF, dépenses, tickets, retours et annulations.
- **KIN-MART STOCK** : stock disponible/réservé, mouvements, inventaire physique, seuils et expiration.
- **KIN-MART SUPPLY** : fournisseurs, bons de commande et réception avec mise à jour transactionnelle du stock.
- **KIN-MART SHOP** : catalogue, panier, compte client, coupon, retrait/livraison, suivi de commande.
- **KIN-MART LOYALTY** : points, utilisation au POS, niveaux Standard/Silver/Gold/Platinum et historique.
- **KIN-MART ANALYTICS** : couverture de stock, réapprovisionnement suggéré, produits vedettes/dormants et segmentation RFM simplifiée.
- **KIN-MART DELIVERY** : affectation des commandes aux livreurs et suivi du statut.
- **Rapports** : ventes, stock, stock faible, expiration, clients, commandes, approvisionnements, retours, inventaires et fidélité.

## Sécurité

CSRF, contrôle de rôle, sessions HttpOnly/SameSite, `password_hash/password_verify`, limitation des tentatives, PDO préparé, validation d’upload, encodage HTML, journal d’audit et transactions pour les opérations critiques.

> Le mot de passe de démonstration doit être changé avant utilisation réelle.


## Mise à jour visuelle RDC / FC
- Intégration d’images produits réalistes sur fond blanc.
- Prix d’articles localisés en Franc Congolais (FC).
- Fournisseurs de démonstration adaptés au contexte RDC.
- Dashboard administrateur redesigné dans un style plus éditorial et plus professionnel.
