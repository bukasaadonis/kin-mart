# KIN-MART — Design system et benchmark e-commerce

## Direction visuelle
La version premium de KIN-MART adopte une identité rouge/noir/blanc dérivée du logo officiel, avec des surfaces blanches, un fond très clair, de grands rayons, des cartes arrondies et une hiérarchie visuelle forte.

## Principes repris des grandes expériences e-commerce
- Recherche principale visible immédiatement dans l’en-tête.
- Navigation par rayons et catégories sous forme de liens/chips.
- Mise en avant des promotions et des produits populaires sur la page d’accueil.
- Choix clair entre retrait en magasin et livraison.
- Cartes produit centrées sur l’image, le prix, la disponibilité et l’action « Ajouter ».
- Panier et checkout avec résumé collant et réduction du nombre d’étapes.
- Design mobile-first avec navigation inférieure sur smartphone.
- Dashboard interne distinct de la boutique, avec cartes KPI, alertes, graphiques et actions rapides.
- POS optimisé pour la vitesse avec recherche/scanner en premier plan et encaissement dans un panneau dédié.

## Composants
- `shop-header` : logo + recherche + compte + panier.
- `shop-hero` : message commercial, CTA et rayons.
- `service-ribbon` : livraison, retrait, fidélité, stock.
- `category-card` : rayon visuel.
- `shop-product` : carte produit uniforme.
- `cart-summary` / `checkout-summary` : résumé de commande collant.
- `km-sidebar` : navigation back-office sombre.
- `km-stat` : KPI dashboard.
- `pos-grid` : surface de caisse.

## Responsive
- Desktop : navigation complète, grille 4 produits, sidebar administration.
- Tablette : grilles condensées, sidebar rétractable.
- Mobile : recherche sur une ligne dédiée, grille 2 produits, navigation boutique fixe en bas, back-office sans sidebar permanente.
