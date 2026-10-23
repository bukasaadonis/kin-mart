# 2. Architecture logicielle

KIN-MART utilise une architecture **MVC orientée objet**.

## Flux d’une requête
Navigateur → `.htaccess` → `index.php` → `Router.php` → middleware → contrôleur → modèle/service → MySQL → vue.

## Responsabilités
- `app/controllers` : orchestration des cas d’utilisation.
- `app/models` : accès aux données et logique persistante.
- `app/views` : interfaces HTML.
- `app/core` : routeur, base de données, sessions, CSRF, validation.
- `app/middlewares` : authentification et contrôle des rôles.
- `app/services` : audit, PDF, code-barres, recommandations.
- `public/assets` : Bootstrap, CSS et JavaScript locaux.
- `database` : schéma et données de démonstration.
- `storage` : sauvegardes, exports et logs éventuels.

## Pourquoi MVC ?
La séparation réduit le couplage : une modification du design ne doit pas réécrire les requêtes SQL ; un changement de base ne doit pas imposer de réécrire toutes les vues. Cette structure facilite également les tests, la défense académique et la maintenance commerciale.

## Portabilité
Le projet ne contient aucun chemin lié à un compte Windows. `base_url()` est calculé depuis l’emplacement du script. Le port MySQL est centralisé dans `app/config/config.php` et peut être 3306, 3307 ou autre.
