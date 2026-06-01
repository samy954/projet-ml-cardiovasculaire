<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$page = 'accueil';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardioPredict - Accueil</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <span class="kicker">Projet universitaire de machine learning</span>
        <h1>Evaluer le risque cardiovasculaire avec des donnees de sante simples.</h1>
        <p>
          CardioPredict combine modeles de machine learning, visualisations et outils de suivi sante
          pour proposer une interface pedagogique autour de la prevention cardiovasculaire.
        </p>
        <div class="hero-actions">
          <a class="btn" href="prediction.php">Lancer une prediction</a>
          <a class="btn btn-secondary" href="dashboard.php">Ouvrir le dashboard</a>
        </div>

        <div class="stats-row">
          <div class="stat-box">
            <strong>70 302</strong>
            <span>observations exploitees apres deduplication</span>
          </div>
          <div class="stat-box">
            <strong>2 tests</strong>
            <span>mode classique et mode clinique avance</span>
          </div>
          <div class="stat-box">
            <strong>0,87</strong>
            <span>meilleure ROC-AUC observee sur le dataset Heart</span>
          </div>
        </div>
      </section>

      <section class="feature-grid">
        <div class="feature-card">
          <strong>Prediction ML</strong>
          <p>Estimation du risque avec deux modeles adaptes aux donnees disponibles.</p>
        </div>
        <div class="feature-card">
          <strong>Outils sante</strong>
          <p>Calculs IMC, calories et tension pour completer l'analyse utilisateur.</p>
        </div>
        <div class="feature-card">
          <strong>Historique</strong>
          <p>Sauvegarde des resultats pour suivre les estimations dans le temps.</p>
        </div>
        <div class="feature-card">
          <strong>Visualisations</strong>
          <p>Graphiques et interpretation des variables issues des datasets publics.</p>
        </div>
      </section>

      <section class="section-box">
        <h2>Comment fonctionne la plateforme ?</h2>
        <div class="workflow">
          <div class="workflow-step">
            <span class="step-index">1</span>
            <h3>Saisie des donnees</h3>
            <p>L'utilisateur renseigne un profil simple ou clinique selon le mode choisi.</p>
          </div>
          <div class="workflow-step">
            <span class="step-index">2</span>
            <h3>Prediction Python</h3>
            <p>PHP transmet les valeurs au script Python, qui charge le modele ML exporte.</p>
          </div>
          <div class="workflow-step">
            <span class="step-index">3</span>
            <h3>Resultat et suivi</h3>
            <p>Le risque estime est affiche puis enregistre dans l'historique si la base est disponible.</p>
          </div>
        </div>
      </section>

      <section class="grid-2">
        <div class="card dataset-card">
          <span class="badge">Dataset Cardio</span>
          <h2>Test classique</h2>
          <p>
            Mode adapte aux donnees faciles a renseigner : age, taille, poids, tension,
            cholesterol, glycemie et habitudes de vie.
          </p>
          <div class="metric-line"><span>Observations</span><strong>70 000</strong></div>
          <div class="metric-line"><span>Modele retenu</span><strong>Random Forest</strong></div>
          <div class="metric-line"><span>ROC-AUC</span><strong>0,798</strong></div>
          <a href="prediction.php?mode=cardio" class="btn btn-secondary" style="margin-top:16px;">Tester ce modele</a>
        </div>

        <div class="card dataset-card secondary">
          <span class="badge">Dataset Heart</span>
          <h2>Test avance</h2>
          <p>
            Mode plus clinique avec ECG, douleur thoracique, cholesterol serique,
            frequence cardiaque maximale et autres variables medicales.
          </p>
          <div class="metric-line"><span>Observations uniques</span><strong>302</strong></div>
          <div class="metric-line"><span>Modele retenu</span><strong>Regression Logistique</strong></div>
          <div class="metric-line"><span>ROC-AUC</span><strong>0,871</strong></div>
          <a href="prediction.php?mode=heart" class="btn btn-secondary" style="margin-top:16px;">Tester ce modele</a>
        </div>
      </section>

      <section class="info-strip" style="margin-top:22px;">
        <p>
          CardioPredict est un outil pedagogique universitaire. Les resultats sont des estimations statistiques
          et ne remplacent pas un diagnostic medical. En cas de doute, consultez un professionnel de sante.
        </p>
      </section>
    </div>
  </main>

  <?php include 'partials_footer.php'; ?>
</body>
</html>
