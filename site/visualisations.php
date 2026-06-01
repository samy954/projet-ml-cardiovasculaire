<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$page = 'visualisations';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardioPredict - Visualisations</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .viz-tabs { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
    .viz-tab  { padding: 9px 20px; border-radius: 999px; font-weight: 600; font-size: 14px;
                cursor: pointer; border: 2px solid var(--line); background: var(--surface);
                color: var(--primary-dark); transition: .2s; }
    .viz-tab.active, .viz-tab:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
    .viz-panel { display: none; }
    .viz-panel.active { display: block; }
    .viz-observation { background: var(--primary-soft); border-left: 4px solid var(--primary);
                       border-radius: 0 10px 10px 0; padding: 10px 16px; margin-top: 10px;
                       font-size: 14px; color: var(--primary-dark); }
    .dataset-badge { display: inline-block; padding: 3px 12px; border-radius: 999px; font-size: 12px;
                     font-weight: 700; margin-bottom: 8px; }
    .badge-cardio { background: #dbeafe; color: #1e40af; }
    .badge-heart  { background: #fce7f3; color: #9d174d; }
  </style>
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <h1>Visualisations du projet</h1>
        <p>
          Cette page regroupe les graphiques clés issus de l'analyse exploratoire et de l'évaluation des deux modèles.
          Les visualisations sont organisées par dataset. Chaque graphique est accompagné d'une interprétation concrète.
        </p>
      </section>

      <div class="viz-tabs">
        <button class="viz-tab active" onclick="showPanel('cardio', this)">Dataset Cardio</button>
        <button class="viz-tab"        onclick="showPanel('heart',  this)">Dataset Heart</button>
        <button class="viz-tab"        onclick="showPanel('compare',this)">Comparaison des modèles</button>
      </div>

      <!-- ── CARDIO ─────────────────────────────────────────────────────── -->
      <div id="panel-cardio" class="viz-panel active">
        <section class="viz-grid">

          <div class="card">
            <span class="dataset-badge badge-cardio">Dataset Cardio</span>
            <h2>Répartition de la variable cible</h2>
            <img src="assets/img/cardio_target_distribution.png" alt="Répartition cible cardio" class="viz-img">
            <p>Distribution des classes 0 (sain) et 1 (malade cardiovasculaire) dans le dataset.</p>
            <div class="viz-observation">
              Le dataset est presque équilibré (≈ 50/50), ce qui est favorable : le modèle n'est pas biaisé vers
              une classe majoritaire. Aucun rééquilibrage (SMOTE, sous-échantillonnage) n'a été nécessaire.
            </div>
          </div>

          <div class="card">
            <span class="dataset-badge badge-cardio">Dataset Cardio</span>
            <h2>Importance des variables</h2>
            <img src="assets/img/cardio_feature_importance.png" alt="Importance variables cardio" class="viz-img">
            <p>Poids de chaque variable dans la décision du Random Forest (critère Gini).</p>
            <div class="viz-observation">
              La pression systolique (<em>ap_hi</em>), l'âge et le poids sont les trois variables
              les plus déterminantes. En revanche, le tabagisme et la consommation d'alcool ont un impact
              nettement plus faible, ce qui peut sembler contre-intuitif mais s'explique par la faible
              proportion de fumeurs déclarés dans le dataset.
            </div>
          </div>

          <div class="card">
            <span class="dataset-badge badge-cardio">Dataset Cardio</span>
            <h2>Matrice de confusion</h2>
            <img src="assets/img/cardio_confusion_matrix.png" alt="Matrice de confusion cardio" class="viz-img">
            <p>Résumé des prédictions correctes et des erreurs sur l'ensemble de test (14 000 observations).</p>
            <div class="viz-observation">
              Le modèle présente un taux de faux négatifs non négligeable (personnes malades prédites saines).
              Dans un contexte médical, minimiser les faux négatifs est prioritaire — un seuil de décision
              abaissé (ex. 0,40 au lieu de 0,50) pourrait améliorer le rappel au prix d'une légère baisse de précision.
            </div>
          </div>

          <div class="card">
            <span class="dataset-badge badge-cardio">Dataset Cardio</span>
            <h2>Courbe ROC</h2>
            <img src="assets/img/cardio_roc_curve.png" alt="Courbe ROC cardio" class="viz-img">
            <p>Capacité de discrimination de chaque modèle selon le seuil de décision (ensemble de test).</p>
            <div class="viz-observation">
              Le Random Forest obtient la meilleure AUC ≈ 0,798, suivi de près par l'arbre de décision.
              La régression logistique, plus simple, atteint ≈ 0,778. Toutes les courbes sont nettement
              au-dessus de la diagonale (modèle aléatoire), ce qui confirme que les modèles ont bien appris.
            </div>
          </div>

        </section>
      </div>

      <!-- ── HEART ──────────────────────────────────────────────────────── -->
      <div id="panel-heart" class="viz-panel">
        <section class="viz-grid">

          <div class="card">
            <span class="dataset-badge badge-heart">Dataset Heart</span>
            <h2>Répartition de la variable cible</h2>
            <img src="assets/img/heart_target_distribution.png" alt="Répartition cible heart" class="viz-img">
            <p>Distribution après suppression des 723 doublons (302 observations uniques conservées).</p>
            <div class="viz-observation">
              Le dataset Kaggle "heart-disease-dataset" est la concaténation de plusieurs sources (Cleveland,
              Hungarian, Suisse, VA). Cela créait 723 lignes identiques sur 1025, provoquant un <em>data leakage</em>
              (score artificiel de 1,0). Après déduplication, le dataset est équilibré : 138 sains / 164 malades.
            </div>
          </div>

          <div class="card">
            <span class="dataset-badge badge-heart">Dataset Heart</span>
            <h2>Importance des variables</h2>
            <img src="assets/img/heart_feature_importance.png" alt="Importance variables heart" class="viz-img">
            <p>Poids des variables dans la Régression Logistique (coefficients normalisés en valeur absolue).</p>
            <div class="viz-observation">
              La douleur thoracique (<em>cp</em>), le nombre de vaisseaux colorés (<em>ca</em>) et la
              fréquence cardiaque maximale (<em>thalach</em>) ressortent comme les variables les plus
              discriminantes. Ces résultats sont cohérents avec la littérature cardiologique.
            </div>
          </div>

          <div class="card">
            <span class="dataset-badge badge-heart">Dataset Heart</span>
            <h2>Matrice de confusion</h2>
            <img src="assets/img/heart_confusion_matrix.png" alt="Matrice de confusion heart" class="viz-img">
            <p>Prédictions du meilleur modèle sur l'ensemble de test (61 observations après déduplication).</p>
            <div class="viz-observation">
              La Régression Logistique atteint 80 % d'accuracy sur les 61 observations de test, avec un bon
              rappel (≈ 85 %) — elle manque peu de vrais malades. Le petit effectif de test invite à
              interpréter ces chiffres avec prudence.
            </div>
          </div>

          <div class="card">
            <span class="dataset-badge badge-heart">Dataset Heart</span>
            <h2>Courbe ROC</h2>
            <img src="assets/img/heart_roc_curve.png" alt="Courbe ROC heart" class="viz-img">
            <p>Comparaison des trois modèles après déduplication du dataset (données saines).</p>
            <div class="viz-observation">
              La Régression Logistique obtient la meilleure AUC ≈ 0,871, devant le Random Forest (0,862).
              Sur un dataset aussi petit (302 lignes), les modèles simples régularisés surpassent souvent les
              méthodes d'ensemble, moins stables avec peu de données.
            </div>
          </div>

        </section>
      </div>

      <!-- ── COMPARAISON ────────────────────────────────────────────────── -->
      <div id="panel-compare" class="viz-panel">
        <section class="viz-grid" style="grid-template-columns: 1fr;">
          <div class="card">
            <h2>Comparaison des modèles — les deux datasets</h2>
            <img src="assets/img/models_comparison.png" alt="Comparaison des modèles" class="viz-img" style="max-width:900px;margin:auto;display:block;">
            <p>Accuracy, ROC-AUC et F1-Score sur l'ensemble de test pour chaque modèle et chaque dataset.</p>
            <div class="viz-observation">
              <strong>Dataset Cardio (70 000 obs.) :</strong> le Random Forest est retenu (ROC-AUC = 0,798,
              Accuracy = 0,732). La grande taille du dataset profite aux méthodes d'ensemble.<br><br>
              <strong>Dataset Heart (302 obs. uniques) :</strong> la Régression Logistique est retenue
              (ROC-AUC = 0,871, Accuracy = 0,803). Avec peu de données, un modèle plus simple et régularisé
              est plus stable. Le critère de sélection est la ROC-AUC, car elle est insensible au seuil de décision.
            </div>
          </div>
        </section>
      </div>

      <section class="info-strip">
        <h2>Note méthodologique</h2>
        <p>
          Tous les modèles sont évalués sur un ensemble de test séparé (80/20, stratifié). Les métriques affichées
          ne proviennent pas du jeu d'entraînement — elles reflètent la capacité de généralisation réelle.
          Le critère principal de sélection du meilleur modèle est la ROC-AUC, complété par le F1-Score en cas d'égalité.
        </p>
      </section>
    </div>
  </main>

  <?php include 'partials_footer.php'; ?>

  <script>
    function showPanel(name, btn) {
      document.querySelectorAll('.viz-panel').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.viz-tab').forEach(b => b.classList.remove('active'));
      document.getElementById('panel-' + name).classList.add('active');
      btn.classList.add('active');
    }
  </script>
</body>
</html>
