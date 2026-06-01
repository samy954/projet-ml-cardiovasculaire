# Modifications a integrer au rapport

Ce fichier sert de journal de bord pour garder une trace des ameliorations apportees au projet CardioPredict.
Il pourra etre repris plus tard pour modifier le rapport final.

## 1. Ajout d'une base de donnees SQL

### Fichiers crees

- `database/schema.sql`
- `database/README_database.md`

### Objectif

Ajouter une vraie base de donnees relationnelle pour stocker les utilisateurs et les historiques.

### Modifications realisees

- Creation de la table `users`.
- Creation de la table `prediction_history`.
- Creation de la table `bmi_history`.
- Creation de la table `calorie_history`.
- Creation de la table `blood_pressure_history`.
- Utilisation du moteur `InnoDB`.
- Utilisation de l'encodage `utf8mb4`.
- Ajout de cles etrangeres vers `users(id)`.
- Utilisation de `ON DELETE SET NULL` pour conserver les historiques meme si un utilisateur est supprime.

### Interet pour le projet

Cette modification transforme le site en application web plus complete, capable de conserver des donnees utilisateurs et des resultats dans le temps.

## 2. Connexion PHP a MySQL avec PDO

### Fichier cree

- `site/config/db.php`

### Objectif

Centraliser la connexion a la base de donnees et eviter de repeter les identifiants MySQL dans plusieurs fichiers.

### Modifications realisees

- Connexion PDO avec charset `utf8mb4`.
- Gestion des erreurs avec exceptions PDO.
- Ajout d'une fonction de connexion obligatoire.
- Ajout d'une fonction de connexion optionnelle pour ne pas bloquer la prediction si MySQL est indisponible.

### Interet pour le projet

Le code est mieux organise et plus facile a configurer pour MAMP.

## 3. Ajout d'un espace utilisateur

### Fichiers crees

- `site/register.php`
- `site/login.php`
- `site/logout.php`
- `site/profile.php`

### Objectif

Permettre a un utilisateur de creer un compte, de se connecter, de se deconnecter et de consulter son profil.

### Modifications realisees

- Inscription avec `username`, `email` et `password`.
- Hash du mot de passe avec `password_hash()`.
- Connexion avec `password_verify()`.
- Stockage de `$_SESSION['user_id']` et `$_SESSION['username']`.
- Requetes SQL preparees avec PDO.
- Messages d'erreur clairs.

### Interet pour le projet

L'application devient personnalisee : chaque utilisateur connecte peut retrouver ses resultats.

## 4. Historique des predictions

### Fichiers modifies ou crees

- `site/prediction.php`
- `site/historique.php`

### Objectif

Conserver les predictions effectuees par les utilisateurs sans casser le fonctionnement existant.

### Modifications realisees

- Conservation du flux existant : formulaire PHP -> JSON temporaire -> script Python -> resultat.
- Creation d'un fichier JSON temporaire unique avec `uniqid()`.
- Suppression du fichier temporaire apres execution.
- Sauvegarde optionnelle dans `prediction_history`.
- Association a `user_id` si l'utilisateur est connecte.
- Enregistrement avec `user_id = NULL` si l'utilisateur n'est pas connecte.
- Creation d'une page `historique.php`.

### Interet pour le projet

L'utilisateur peut suivre ses predictions dans le temps, ce qui rend le site plus proche d'une vraie plateforme de suivi.

## 5. Ajout des outils sante

### Fichiers crees

- `site/imc.php`
- `site/calories.php`
- `site/tension.php`

### Objectif

Completer la prediction cardiovasculaire avec des outils pedagogiques simples autour de la sante.

### Modifications realisees

- Calculateur IMC :
  - poids ;
  - taille ;
  - calcul de l'IMC ;
  - categorie d'interpretation ;
  - sauvegarde dans `bmi_history`.

- Calculateur calories :
  - age ;
  - sexe ;
  - poids ;
  - taille ;
  - niveau d'activite ;
  - formule de Mifflin-St Jeor ;
  - sauvegarde dans `calorie_history`.

- Page tension arterielle :
  - pression systolique ;
  - pression diastolique ;
  - pression pulsee ;
  - pression arterielle moyenne ;
  - categorie d'interpretation ;
  - sauvegarde dans `blood_pressure_history`.

### Interet pour le projet

Ces pages enrichissent le site et creent un lien direct entre donnees de sante simples et prevention cardiovasculaire.

## 6. Ajout d'un dashboard utilisateur

### Fichier cree

- `site/dashboard.php`

### Objectif

Creer une page centrale pour l'utilisateur connecte.

### Modifications realisees

- Affichage du nom de l'utilisateur.
- Affichage de la derniere prediction.
- Affichage du dernier IMC.
- Affichage de la derniere tension.
- Affichage du dernier calcul calorique.
- Affichage du nombre total de predictions.
- Liens rapides vers prediction, IMC, calories, tension et historique.
- Message d'invitation si l'utilisateur n'est pas connecte.

### Interet pour le projet

Le dashboard ameliore l'experience utilisateur et donne une vue synthetique des derniers resultats.

## 7. Navigation du site

### Fichiers modifies

- `site/partials_header.php`
- `site/index.php`
- `site/visualisations.php`
- `site/methode.php`

### Objectif

Rendre la navigation coherente avec les nouvelles fonctionnalites.

### Modifications realisees

- Ajout des liens :
  - Accueil ;
  - Prediction ;
  - IMC ;
  - Calories ;
  - Tension ;
  - Dashboard ;
  - Historique ;
  - Visualisations ;
  - Methode ;
  - Connexion / Deconnexion.
- Ajout de la gestion de session sur les pages principales pour adapter le menu.

### Interet pour le projet

Les nouvelles pages sont accessibles directement et le site devient plus facile a utiliser.

## 8. Avertissement medical

### Fichiers modifies

- `site/prediction.php`
- `site/imc.php`
- `site/calories.php`
- `site/tension.php`

### Objectif

Rappeler clairement que l'application est un outil pedagogique et non un outil de diagnostic.

### Modifications realisees

- Ajout d'un message indiquant que les resultats sont des estimations statistiques.
- Mention explicite qu'ils ne remplacent pas un diagnostic medical.
- Invitation a consulter un professionnel de sante en cas de doute.

### Interet pour le projet

Cette modification est importante pour le contexte sante et pour les limites ethiques du projet.

## 9. Documentation generale du projet

### Fichier modifie

- `README.md`

### Objectif

Documenter clairement l'installation, la base SQL et les nouvelles pages.

### Modifications realisees

- Presentation du projet.
- Installation avec MAMP.
- Creation de la base SQL.
- Import de `database/schema.sql`.
- Configuration de `site/config/db.php`.
- Description du flux de prediction.
- Description du flux utilisateur.
- Description des nouvelles pages.
- Limites medicales du projet.

### Interet pour le projet

Le projet devient plus facile a installer, tester et presenter.

## 10. Amelioration du notebook d'exploration

### Fichier modifie

- `notebooks/01_exploration_visualisation.ipynb`

### Objectif

Renforcer l'analyse de qualite des donnees.

### Modifications realisees

- Ajout de variables derivees :
  - `pulse_pressure` ;
  - `mean_arterial_pressure`.
- Amelioration de la detection des valeurs aberrantes.
- Controle des cas `ap_hi < ap_lo`.
- Controle des tensions extremes.
- Controle des tailles, poids et IMC incoherents.
- Ajout d'un texte explicatif apres le test des valeurs aberrantes.
- Conclusion enrichie sur les limites des donnees.

### Interet pour le projet

Cette modification justifie mieux les choix de nettoyage et les limites du dataset avant la modelisation.

## 11. Amelioration du notebook de modelisation

### Fichier modifie

- `notebooks/02_modelisation_prediction.ipynb`

### Objectif

Rendre l'evaluation machine learning plus solide et plus adaptee au contexte medical.

### Modifications realisees

- Ajout de metriques adaptees a la sante :
  - rappel ;
  - specificite ;
  - faux negatifs ;
  - faux positifs ;
  - PR-AUC ;
  - balanced accuracy.
- Correction de l'evaluation du dataset Heart :
  - `target = 0` est traite comme la classe malade ;
  - les metriques medicales evaluent donc bien la detection des patients malades.
- Ajout d'une recherche d'hyperparametres ciblee avec `RandomizedSearchCV`.
- Ajout de l'optimisation des seuils de decision.
- Ajout de l'interpretabilite par permutation importance.
- Ajout de textes explicatifs apres les principaux tests et tableaux.
- Conclusion ML renforcee.

### Interet pour le projet

L'evaluation ne se limite plus a l'accuracy. Elle prend mieux en compte les enjeux d'un projet de prediction en sante, notamment la reduction des faux negatifs.

## 12. Verifications realisees

### Verifications cote site

- Verification syntaxique PHP avec `php -l`.
- Test de chargement HTTP des pages principales.
- Verification que la prediction reste utilisable sans base SQL.
- Verification du fonctionnement Python avec le lanceur `C:\\Windows\\py.exe -3.11`.

### Verifications cote notebooks

- Verification que les notebooks restent des fichiers JSON valides.
- Verification syntaxique des cellules Python avec `ast.parse`.
- Test cible sur les nouvelles variables du notebook d'exploration.
- Test cible sur la logique des metriques sante.

## 13. Amelioration esthetique et visuelle du site

### Fichiers modifies

- `site/assets/css/style.css`
- `site/index.php`
- `site/dashboard.php`
- `site/historique.php`

### Objectif

Donner au site un rendu plus professionnel, plus clair et plus credible pour une presentation universitaire.

### Modifications realisees

- Modernisation de la palette visuelle.
- Reduction des arrondis et des ombres pour un rendu plus sobre.
- Header rendu plus compact et plus lisible.
- Refonte de la page d'accueil :
  - hero plus clair ;
  - mise en avant du projet universitaire ;
  - section fonctionnalites ;
  - section fonctionnement en 3 etapes ;
  - cartes datasets plus structurees ;
  - avertissement medical visible.
- Amelioration du dashboard :
  - cartes de synthese ;
  - meilleure hierarchie visuelle ;
  - badges de statut ;
  - mise en avant des derniers resultats.
- Amelioration de l'historique :
  - compteurs en haut de page ;
  - badges de risque ;
  - tableaux plus lisibles ;
  - meilleur rendu mobile avec tables scrollables.

### Interet pour le projet

Cette passe rend l'application plus professionnelle et plus facile a comprendre. Elle renforce la premiere impression du site et facilite la lecture des resultats par un utilisateur ou un jury.

### Verifications

- `php -l site/index.php`
- `php -l site/dashboard.php`
- `php -l site/historique.php`
- `php -l site/partials_header.php`
- Test HTTP 200 sur :
  - `index.php`
  - `dashboard.php`
  - `historique.php`
  - `prediction.php`

## Modifications futures

Ajouter ici chaque nouvelle modification importante sous ce format :

```text
## X. Titre de la modification

### Fichiers modifies ou crees

- ...

### Objectif

...

### Modifications realisees

- ...

### Interet pour le projet

...

### Verifications

- ...
```
