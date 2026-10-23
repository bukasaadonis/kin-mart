# 5. Sécurité

- `password_hash()` / `password_verify()` pour les mots de passe.
- Session avec cookie HttpOnly et SameSite=Lax.
- Régénération de l’identifiant de session après authentification.
- Jeton CSRF sur chaque requête POST.
- Requêtes préparées PDO, émulation désactivée.
- Encodage HTML systématique via `e()` contre XSS réfléchi.
- RBAC : routeur protégé par authentification et rôles.
- Limitation des tentatives de connexion dans une fenêtre temporelle.
- Upload limité en taille et MIME, noms aléatoires.
- Journal d’audit des actions sensibles.
- Suppression logique des produits plutôt que destruction de l’historique.
- Vente annulée par une opération métier qui restaure le stock au lieu d’un DELETE.
- Répertoires `app`, `database` et `storage` à protéger par Apache.

Pour un déploiement Internet futur, ajouter HTTPS, politique CSP, sauvegardes externalisées, configuration `debug=false`, SMTP sécurisé et éventuellement MFA.
