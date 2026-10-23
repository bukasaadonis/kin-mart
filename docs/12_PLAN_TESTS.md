# 12. Plan de tests d’acceptation

1. Authentification admin correcte / incorrecte / blocage après tentatives.
2. RBAC : le caissier ne peut pas accéder aux paramètres.
3. Création produit + unicité code/barcode.
4. Réception fournisseur augmente le stock et crée des mouvements.
5. Inventaire génère uniquement les écarts saisis.
6. Ouverture POS, vente, paiement, ticket, baisse du stock.
7. Vente impossible si stock disponible insuffisant.
8. Vente à un client : points gagnés.
9. Utilisation points : réduction et transaction UTILISATION.
10. Annulation : stock restauré, statut CANCELLED.
11. Retour partiel : quantité retournée <= quantité vendue.
12. Commande boutique : réservation du stock.
13. Annulation commande : réservation libérée.
14. Livraison : stock physique diminué et vente WEB créée.
15. Coupon valide / expiré / minimum non atteint.
16. Stock faible : visible Dashboard/Stock/Analytics.
17. Produit périssable : visible dans rapport expiration.
18. Sauvegarde : fichier SQL téléchargeable.
19. Export CSV/XLS/PDF.
20. Portabilité : copie sur un second XAMPP avec port MySQL différent.
