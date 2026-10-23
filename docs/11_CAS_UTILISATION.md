# 11. Cas d’utilisation

## S’authentifier
Acteurs : tous les utilisateurs. Précondition : compte actif. Résultat : session et rôle chargés.

## Réaliser une vente POS
Acteur : Caissier. Préconditions : caisse ouverte, stock disponible. Scénario : scan → panier → client facultatif → remise/points → paiement → transaction SQL → ticket.

## Approvisionner
Acteur : Magasinier/Gérant. Créer bon → réception → quantités reçues → mouvements ENTREE → stock augmenté.

## Faire un inventaire
Acteur : Magasinier. Comparer quantité système et quantité physique → calcul différence → validation → mouvement AJUSTEMENT.

## Passer une commande
Acteur : Client. Panier → coupon → retrait/livraison → réservation de stock → commande NOUVELLE.

## Livrer
Acteurs : Gérant/Livreur. Affecter → EN_LIVRAISON → LIVREE → sortie réelle stock → vente → points fidélité.

## Annuler / retourner
Acteur autorisé. La vente n’est jamais supprimée : l’opération est tracée et le stock est restauré selon les quantités concernées.
