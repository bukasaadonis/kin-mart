# 10. Base MERISE : règles de gestion, MCD, MLD et MPD

## Règles de gestion principales
1. Un utilisateur possède un et un seul rôle ; un rôle peut être porté par plusieurs utilisateurs.
2. Un produit appartient à une catégorie et une unité ; il peut avoir une marque et un fournisseur principal.
3. Un produit possède un état de stock unique dans le magasin ; tout changement significatif génère un mouvement.
4. Une vente comporte au moins une ligne et un paiement.
5. Une commande client comporte au moins une ligne et réserve les quantités correspondantes.
6. Une réception fournisseur augmente le stock uniquement après validation.
7. Un inventaire validé peut générer des ajustements de stock.
8. Un client peut disposer d’un compte fidélité unique.
9. Un coupon peut être utilisé plusieurs fois dans la limite configurée ; chaque utilisation est tracée.
10. Une commande livrée libère les réservations, diminue le stock et crée une vente correspondante.

## Cardinalités essentielles
- ROLE (0,N) — possède — USER (1,1)
- CATEGORY (0,N) — classe — PRODUCT (1,1)
- SUPPLIER (0,N) — reçoit — PURCHASE_ORDER (1,1)
- PURCHASE_ORDER (1,N) — contient — PURCHASE_ORDER_ITEM (1,1)
- PRODUCT (1,1) — possède — STOCK (1,1)
- PRODUCT (1,N) — concerne — STOCK_MOVEMENT (1,1)
- SALE (1,N) — contient — SALE_ITEM (1,1)
- CUSTOMER (0,N) — réalise — SALE (0,1 côté vente)
- CUSTOMER (0,N) — passe — ORDER (1,1)
- ORDER (1,N) — contient — ORDER_ITEM (1,1)
- CUSTOMER (1,1) — possède — LOYALTY_ACCOUNT (0,1)

Le fichier `database/kinmart.sql` constitue le MPD exécutable.
