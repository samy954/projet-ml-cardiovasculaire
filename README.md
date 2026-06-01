# CardioPredict

CardioPredict est un projet universitaire de machine learning applique a la prediction du risque cardiovasculaire.

L'application combine :
- un site web en PHP ;
- un script Python de prediction ;
- des modeles ML exportes en JSON ;
- une base MySQL pour les comptes utilisateurs et les historiques ;
- des outils sante complementaires : IMC, calories et tension arterielle.

Le projet reste utilisable sans compte. La connexion sert surtout a conserver un historique personnel.

## Fonctionnalites principales

- Page d'accueil du projet.
- Formulaire de prediction cardiovasculaire avec deux modes :
  - `cardio` : variables generales accessibles ;
  - `heart` : variables cliniques plus detaillees.
- Connexion et inscription utilisateur.
- Dashboard personnel.
- Historique des predictions.
- Calculateur IMC.
- Calculateur de besoins caloriques.
- Analyse simple de tension arterielle.
- Pages de visualisations et de methode ML.

## Structure du projet

```text
projet-ml-cardiovasculaire/
|
|-- database/
|   |-- schema.sql
|   |-- README_database.md
|
|-- notebooks/
|   |-- 01_exploration_visualisation.ipynb
|   |-- 02_modelisation_prediction.ipynb
|
|-- rapport/
|   |-- le_rapport.pdf
|
|-- site/
|   |-- index.php
|   |-- prediction.php
|   |-- register.php
|   |-- login.php
|   |-- logout.php
|   |-- profile.php
|   |-- dashboard.php
|   |-- historique.php
|   |-- imc.php
|   |-- calories.php
|   |-- tension.php
|   |-- visualisations.php
|   |-- methode.php
|   |-- partials_header.php
|   |-- partials_footer.php
|   |
|   |-- config/
|   |   |-- db.php
|   |
|   |-- assets/
|   |   |-- css/style.css
|   |   |-- js/app.js
|   |   |-- img/
|   |
|   |-- data/
|   |   |-- cardio.csv
|   |   |-- heart.csv
|   |
|   |-- ml/
|       |-- predict.py
|       |-- models/
|       |-- metadata/
```

## Installation avec MAMP

### 1. Placer le projet

Copier le dossier du projet dans le dossier web de MAMP, par exemple :

```text
C:/MAMP/htdocs/projet-ml-cardiovasculaire/
```

### 2. Lancer MAMP

Demarrer Apache et MySQL depuis MAMP.

### 3. Creer la base SQL

Ouvrir phpMyAdmin depuis MAMP, puis creer une base de donnees :

```text
cardiopredict
```

### 4. Importer le schema SQL

Dans phpMyAdmin :

1. Selectionner la base `cardiopredict`.
2. Ouvrir l'onglet `Importer`.
3. Choisir `database/schema.sql`.
4. Valider l'import.

Le schema cree les tables :
- `users`
- `prediction_history`
- `bmi_history`
- `calorie_history`
- `blood_pressure_history`

Plus de details sont disponibles dans `database/README_database.md`.

### 5. Configurer la connexion MySQL

Ouvrir :

```text
site/config/db.php
```

Adapter si besoin :

```php
const DB_HOST = 'localhost';
const DB_PORT = '3306';
const DB_NAME = 'cardiopredict';
const DB_USER = 'root';
const DB_PASSWORD = 'root';
```

Avec MAMP, le couple `root` / `root` est frequent, mais il peut varier selon l'installation.

### 6. Verifier Python

Le site appelle le script :

```text
site/ml/predict.py
```

Sur Windows, `prediction.php` utilise :

```text
C:\Windows\py.exe -3.11
```

Verifier que Python 3.11 est installe et accessible via ce lanceur.

### 7. Lancer le site

Ouvrir dans le navigateur :

```text
http://localhost/projet-ml-cardiovasculaire/site/
```

Le chemin exact depend du dossier place dans `htdocs`.

## Flux de prediction

Le fonctionnement de prediction existant est conserve :

```text
Formulaire PHP
    -> fichier JSON temporaire unique
    -> script Python ml/predict.py
    -> modele JSON
    -> resultat JSON
    -> affichage PHP
    -> sauvegarde optionnelle dans prediction_history
```

La prediction reste disponible sans compte.

Si la base SQL est indisponible, le calcul ML continue a fonctionner ; seule la sauvegarde dans l'historique est ignoree.

## Flux utilisateur

1. L'utilisateur peut creer un compte depuis `register.php`.
2. Le mot de passe est hashe avec `password_hash()`.
3. La connexion se fait depuis `login.php` avec `password_verify()`.
4. La session stocke :
   - `$_SESSION['user_id']`
   - `$_SESSION['username']`
5. Les predictions et calculs sante sont associes a l'utilisateur connecte.
6. Le dashboard affiche les derniers resultats.
7. L'historique affiche les anciennes predictions et mesures.

## Nouvelles pages

- `register.php` : inscription.
- `login.php` : connexion.
- `logout.php` : deconnexion.
- `profile.php` : informations du compte.
- `dashboard.php` : resume utilisateur.
- `historique.php` : historique des predictions, IMC, calories et tension.
- `imc.php` : calculateur IMC.
- `calories.php` : calculateur calorique avec Mifflin-St Jeor.
- `tension.php` : analyse simple de tension arterielle.

## Securite minimale

Le projet applique les protections de base suivantes :

- requetes SQL preparees avec PDO ;
- mots de passe hashes ;
- sessions PHP pour l'espace utilisateur ;
- validation serveur des formulaires ;
- fichiers JSON temporaires uniques pour les predictions ;
- suppression du fichier temporaire apres execution Python ;
- erreurs SQL techniques envoyees dans les logs et non affichees en detail ;
- site utilisable sans connexion.

## Limites medicales

CardioPredict est un outil pedagogique universitaire.

Les resultats fournis sont des estimations statistiques basees sur des jeux de donnees publics. Ils ne remplacent pas un diagnostic medical, un avis professionnel ou une consultation. En cas de doute, il faut consulter un professionnel de sante.

## Modeles ML

Deux datasets sont utilises :

- `cardio` : dataset Cardiovascular Disease, modele Random Forest.
- `heart` : dataset Heart Disease, modele Regression Logistique.

Les modeles sont stockes dans :

```text
site/ml/models/
```

Les metadonnees de performance sont stockees dans :

```text
site/ml/metadata/
```
