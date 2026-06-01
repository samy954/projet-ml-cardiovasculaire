# Base de donnees CardioPredict

Ce dossier contient le schema SQL MySQL/MariaDB utilise par l'application CardioPredict.

## Fichiers

- `schema.sql` : creation des tables principales de l'application.
- `README_database.md` : explication du role des tables et de l'import SQL.

## Tables

### `users`

Stocke les comptes utilisateurs.

Champs principaux :
- `id` : identifiant unique.
- `username` : nom d'utilisateur unique.
- `email` : adresse email unique.
- `password_hash` : mot de passe hashe avec `password_hash()` en PHP.
- `created_at` : date de creation du compte.

### `prediction_history`

Stocke l'historique des predictions cardiovasculaires.

Une prediction peut etre associee a un utilisateur connecte via `user_id`, ou rester anonyme avec `user_id = NULL`.

Champs principaux :
- `mode` : mode de prediction utilise (`cardio` ou `heart`).
- `model_name` : nom du modele utilise.
- `probability` : probabilite retournee par le modele.
- `risk_label` : niveau de risque affiche.
- `prediction_label` : libelle de prediction.
- `age`, `gender`, `weight`, `height`, `ap_hi`, `ap_lo`, `cholesterol`, `gluc` : principales donnees saisies.

### `bmi_history`

Stocke l'historique des calculs d'IMC.

Champs principaux :
- `weight` : poids en kilogrammes.
- `height` : taille en centimetres.
- `bmi` : IMC calcule.
- `category` : interpretation de l'IMC.

### `calorie_history`

Stocke l'historique des estimations caloriques.

Champs principaux :
- `age`, `gender`, `weight`, `height` : donnees du formulaire.
- `activity_level` : niveau d'activite choisi.
- `bmr` : metabolisme de base calcule.
- `daily_calories` : besoin calorique journalier estime.

### `blood_pressure_history`

Stocke l'historique des analyses de tension arterielle.

Champs principaux :
- `ap_hi` : pression systolique.
- `ap_lo` : pression diastolique.
- `pulse_pressure` : pression pulsee.
- `mean_arterial_pressure` : pression arterielle moyenne.
- `category` : interpretation simple.

## Relations

Les tables d'historique possedent toutes une cle etrangere nullable vers `users(id)`.

Si un utilisateur est supprime, ses historiques ne sont pas supprimes : leur `user_id` passe a `NULL` grace a `ON DELETE SET NULL`.

## Importer le schema avec MAMP/phpMyAdmin

1. Lancer MAMP.
2. Ouvrir phpMyAdmin.
3. Creer une base, par exemple `cardiopredict`.
4. Selectionner cette base.
5. Ouvrir l'onglet `Importer`.
6. Choisir le fichier `database/schema.sql`.
7. Valider l'import.

## Importer le schema en ligne de commande

Depuis la racine du projet :

```bash
mysql -u root -p cardiopredict < database/schema.sql
```

Avec MAMP, l'utilisateur local est souvent `root` et le mot de passe peut etre `root`, selon la configuration.

## Verifier les historiques

Apres import et configuration de `site/config/db.php` :

1. Creer un compte depuis `register.php`.
2. Lancer une prediction depuis `prediction.php`.
3. Faire un calcul IMC, calories ou tension.
4. Ouvrir `historique.php`.
5. Verifier dans phpMyAdmin que les tables d'historique contiennent de nouvelles lignes.

Le site doit rester utilisable sans compte : dans ce cas, les calculs peuvent etre enregistres avec `user_id = NULL` si la base est disponible.
