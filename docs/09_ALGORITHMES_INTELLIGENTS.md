# 9. Algorithmes intelligents et aide à la décision

L’intelligence de KIN-MART est **explicable** : les résultats peuvent être justifiés devant un jury et vérifiés à partir des données historiques.

## 1. Vitesse de vente
Pour un produit `p` sur `N` jours :

`VenteMoyenneJournalière(p) = QuantitéVendue(p,N) / N`

## 2. Jours de couverture

`StockDisponible = StockPhysique - StockRéservé`

`Couverture = StockDisponible / VenteMoyenneJournalière`

Une faible couverture déclenche une priorité de réapprovisionnement.

## 3. Suggestion de réapprovisionnement

`QuantitéRecommandée = max(0, StockMax + VenteMoyenne × DélaiFournisseur - StockDisponible)`

Le résultat tient donc compte du rythme de sortie et du délai avant une nouvelle livraison.

## 4. Produits dormants
Un produit est signalé si sa dernière vente dépasse le nombre de jours défini dans les paramètres ou s’il n’a jamais été vendu alors qu’il reste en stock.

## 5. Produits vedettes
Classement par quantité vendue, chiffre d’affaires et marge brute.

## 6. Segmentation RFM simplifiée
- **NOUVEAU** : pas encore ou très peu d’achats ;
- **INACTIF** : dernier achat ancien ;
- **VIP** : montant ou fréquence très élevés ;
- **FIDELE** : fréquence ou montant élevés ;
- **REGULIER** : activité récurrente intermédiaire.

## 7. Recommandations
Le système identifie les catégories déjà achetées par un client puis propose des produits disponibles appartenant à ces catégories. S’il n’existe pas encore d’historique, il utilise les produits récents/populaires. Cette méthode est volontairement transparente et ne prétend pas être un modèle d’IA opaque.
