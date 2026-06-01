<?php
session_start();

$page = 'prediction';
$result = null;
$error = '';

require_once __DIR__ . '/config/db.php';

function nullableFloat(array $values, string $key): ?float
{
  if (!isset($values[$key]) || trim((string) $values[$key]) === '') {
    return null;
  }

  return (float) str_replace(',', '.', (string) $values[$key]);
}

function nullableInt(array $values, string $key): ?int
{
  if (!isset($values[$key]) || trim((string) $values[$key]) === '') {
    return null;
  }

  return (int) $values[$key];
}

function savePredictionHistory(string $mode, array $values, array $result): void
{
  $pdo = getOptionalDatabaseConnection();

  if (!$pdo) {
    return;
  }

  try {
    $stmt = $pdo->prepare(
      'INSERT INTO prediction_history
        (user_id, mode, model_name, probability, risk_label, prediction_label,
         age, gender, weight, height, ap_hi, ap_lo, cholesterol, gluc)
       VALUES
        (:user_id, :mode, :model_name, :probability, :risk_label, :prediction_label,
         :age, :gender, :weight, :height, :ap_hi, :ap_lo, :cholesterol, :gluc)'
    );

    $stmt->execute([
      'user_id' => $_SESSION['user_id'] ?? null,
      'mode' => $mode,
      'model_name' => $result['model_name'] ?? null,
      'probability' => $result['probability'] ?? null,
      'risk_label' => $result['risk_label'] ?? null,
      'prediction_label' => $result['prediction_label'] ?? null,
      'age' => nullableFloat($values, 'age'),
      'gender' => $values['gender'] ?? $values['sex'] ?? null,
      'weight' => nullableFloat($values, 'weight'),
      'height' => nullableFloat($values, 'height'),
      'ap_hi' => nullableInt($values, 'ap_hi') ?? nullableInt($values, 'trestbps'),
      'ap_lo' => nullableInt($values, 'ap_lo'),
      'cholesterol' => nullableInt($values, 'cholesterol'),
      'gluc' => nullableInt($values, 'gluc'),
    ]);
  } catch (Throwable $exception) {
    error_log('Historique prediction non enregistre : ' . $exception->getMessage());
  }
}

$mode = $_GET['mode'] ?? $_POST['mode'] ?? 'cardio';
if (!in_array($mode, ['cardio', 'heart'], true)) {
  $mode = 'cardio';
}

$formConfigs = [
  'cardio' => [
    'title'        => 'Test classique',
    'subtitle'     => 'Variables générales, faciles à renseigner sans données médicales détaillées.',
    'dataset_name' => 'Dataset cardio (70 000 observations)',
    'description'  => "Ce mode utilise le dataset cardio (Kaggle – Cardiovascular Disease). Il demande des données générales : âge, taille, poids, tension, cholestérol et habitudes de vie. Le modèle retenu est un Random Forest (ROC-AUC ≈ 0,80).",
    'fields' => [
      'age'         => ['label' => 'Âge (en années)',          'type' => 'number', 'placeholder' => 'Exemple : 50',  'required' => true, 'min' => 1,   'max' => 120],
      'gender'      => ['label' => 'Genre',                    'type' => 'select', 'options' => ['1' => 'Femme', '2' => 'Homme']],
      'height'      => ['label' => 'Taille (cm)',              'type' => 'number', 'placeholder' => 'Exemple : 170', 'required' => true, 'min' => 50,  'max' => 250],
      'weight'      => ['label' => 'Poids (kg)',               'type' => 'number', 'step' => '0.1', 'placeholder' => 'Exemple : 70', 'required' => true, 'min' => 20, 'max' => 300],
      'ap_hi'       => ['label' => 'Pression systolique (mmHg)',  'type' => 'number', 'placeholder' => 'Exemple : 120', 'required' => true, 'min' => 60,  'max' => 250],
      'ap_lo'       => ['label' => 'Pression diastolique (mmHg)', 'type' => 'number', 'placeholder' => 'Exemple : 80',  'required' => true, 'min' => 40,  'max' => 180],
      'cholesterol' => ['label' => 'Cholestérol',              'type' => 'select', 'options' => ['1' => 'Normal', '2' => 'Au-dessus de la normale', '3' => 'Bien au-dessus de la normale'],
                        'tooltip' => 'Évaluation approximative : Normal < 200 mg/dL, Au-dessus : 200-239, Bien au-dessus : ≥ 240'],
      'gluc'        => ['label' => 'Glycémie',                 'type' => 'select', 'options' => ['1' => 'Normale', '2' => 'Au-dessus de la normale', '3' => 'Bien au-dessus de la normale'],
                        'tooltip' => 'Glycémie à jeun approximative'],
      'smoke'       => ['label' => 'Fumeur·se',                'type' => 'select', 'options' => ['0' => 'Non', '1' => 'Oui']],
      'alco'        => ['label' => "Consommation d'alcool",    'type' => 'select', 'options' => ['0' => 'Non', '1' => 'Oui']],
      'active'      => ['label' => 'Activité physique régulière', 'type' => 'select', 'options' => ['0' => 'Non', '1' => 'Oui']],
    ],
    'defaults' => [
      'age'=>'','gender'=>'1','height'=>'','weight'=>'','ap_hi'=>'','ap_lo'=>'',
      'cholesterol'=>'1','gluc'=>'1','smoke'=>'0','alco'=>'0','active'=>'1',
    ],
  ],

  'heart' => [
    'title'        => 'Test avancé',
    'subtitle'     => 'Variables cliniques précises — nécessite un bilan médical.',
    'dataset_name' => 'Dataset heart (302 observations uniques)',
    'description'  => "Ce mode utilise le dataset heart (Kaggle – Heart Disease). Il demande des variables cliniques détaillées comme le type de douleur thoracique, l'ECG ou la fréquence cardiaque maximale. Le modèle retenu est une Régression Logistique (ROC-AUC ≈ 0,87).",
    'fields' => [
      'age'      => ['label' => 'Âge (en années)',                'type' => 'number', 'placeholder' => 'Exemple : 54', 'required' => true, 'min' => 1, 'max' => 120],
      'sex'      => ['label' => 'Sexe',                           'type' => 'select', 'options' => ['0' => 'Femme', '1' => 'Homme']],
      'cp'       => ['label' => 'Type de douleur thoracique',     'type' => 'select',
                     'options' => ['0' => 'Angine typique', '1' => 'Angine atypique', '2' => 'Douleur non angineuse', '3' => 'Asymptomatique'],
                     'tooltip' => 'Angine typique : douleur à l\'effort soulagée par le repos. Asymptomatique : aucune douleur.'],
      'trestbps' => ['label' => 'Pression artérielle au repos (mmHg)', 'type' => 'number', 'placeholder' => 'Exemple : 130', 'required' => true],
      'chol'     => ['label' => 'Cholestérol sérique (mg/dL)',    'type' => 'number', 'placeholder' => 'Exemple : 246', 'required' => true,
                     'tooltip' => 'Valeur issue d\'une prise de sang.'],
      'fbs'      => ['label' => 'Glycémie à jeun > 120 mg/dL',   'type' => 'select', 'options' => ['0' => 'Non', '1' => 'Oui'],
                     'tooltip' => 'Fasting Blood Sugar — indique un risque de diabète si > 120 mg/dL.'],
      'restecg'  => ['label' => 'Résultat ECG au repos',          'type' => 'select',
                     'options' => ['0' => 'Normal', '1' => 'Anomalie onde ST-T', '2' => 'Hypertrophie ventriculaire gauche'],
                     'tooltip' => 'Électrocardiogramme réalisé au repos.'],
      'thalach'  => ['label' => 'Fréquence cardiaque maximale atteinte', 'type' => 'number', 'placeholder' => 'Exemple : 150', 'required' => true],
      'exang'    => ['label' => "Angine induite par l'effort",    'type' => 'select', 'options' => ['0' => 'Non', '1' => 'Oui'],
                     'tooltip' => 'Exercise-induced angina : douleur thoracique apparaissant à l\'effort.'],
      'oldpeak'  => ['label' => 'Oldpeak (dépression ST)',        'type' => 'number', 'step' => '0.1', 'placeholder' => 'Exemple : 1.2', 'required' => true,
                     'tooltip' => 'Dépression du segment ST induite par l\'effort par rapport au repos. Valeur issue de l\'ECG d\'effort.'],
      'slope'    => ['label' => 'Pente du segment ST à l\'effort', 'type' => 'select',
                     'options' => ['0' => 'Ascendante (montante)', '1' => 'Plate', '2' => 'Descendante (abaissée)'],
                     'tooltip' => 'Forme de la courbe ST sur l\'ECG d\'effort.'],
      'ca'       => ['label' => 'Nombre de vaisseaux colorés (0–3)', 'type' => 'select', 'options' => ['0'=>'0','1'=>'1','2'=>'2','3'=>'3'],
                     'tooltip' => 'Nombre de vaisseaux principaux colorés lors d\'une fluoroscopie.'],
      'thal'     => ['label' => 'Thalassémie / flux sanguin',     'type' => 'select',
                     'options' => ['0' => 'Normal', '1' => 'Défaut fixe', '2' => 'Défaut réversible'],
                     'tooltip' => 'Résultat du test de stress au thallium (scintigraphie myocardique).'],
    ],
    'defaults' => [
      'age'=>'','sex'=>'1','cp'=>'0','trestbps'=>'','chol'=>'','fbs'=>'0',
      'restecg'=>'0','thalach'=>'','exang'=>'0','oldpeak'=>'','slope'=>'1','ca'=>'0','thal'=>'0',
    ],
  ],
];

$currentConfig = $formConfigs[$mode];
$values = $currentConfig['defaults'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($values as $key => $default) {
    if (isset($_POST[$key])) {
      $values[$key] = trim((string) $_POST[$key]);
    }
  }

  $tmpDir = __DIR__ . DIRECTORY_SEPARATOR . 'tmp';
  if (!is_dir($tmpDir)) { mkdir($tmpDir, 0777, true); }

  $inputFile = $tmpDir . DIRECTORY_SEPARATOR . 'prediction_input_' . $mode . '_' . uniqid('', true) . '.json';
  file_put_contents($inputFile, json_encode($values, JSON_UNESCAPED_UNICODE));

  $scriptPath   = __DIR__ . DIRECTORY_SEPARATOR . 'ml' . DIRECTORY_SEPARATOR . 'predict.py';
  $escapedScript = escapeshellarg($scriptPath);
  $escapedInput  = escapeshellarg($inputFile);
  $escapedMode   = escapeshellarg($mode);

  if (PHP_OS_FAMILY === 'Windows') {
    $pythonCmd = '"C:\\Windows\\py.exe" -3.11';
    $command = 'chcp 65001 > nul && set PYTHONIOENCODING=utf-8 && ' . $pythonCmd . ' ' . $escapedScript . ' ' . $escapedMode . ' ' . $escapedInput . ' 2>&1';
  } else {
    $pythonCmd = 'python3';
    $command   = $pythonCmd . ' ' . $escapedScript . ' ' . $escapedMode . ' ' . $escapedInput . ' 2>&1';
  }

  $rawOutput = shell_exec($command);

  if (is_file($inputFile)) {
    unlink($inputFile);
  }

  if ($rawOutput === null || trim($rawOutput) === '') {
    $error = 'Impossible de lancer Python depuis PHP. Vérifie que shell_exec est activé et que Python est accessible.';
  } else {
    $cleanOutput = trim($rawOutput);
    if (!mb_check_encoding($cleanOutput, 'UTF-8')) {
      $cleanOutput = mb_convert_encoding($cleanOutput, 'UTF-8', 'Windows-1252');
    }
    $decoded = json_decode($cleanOutput, true);
    if (is_array($decoded)) {
      if (($decoded['status'] ?? '') === 'ok') {
        $result = $decoded;
        savePredictionHistory($mode, $values, $result);
      } else {
        $error = $decoded['message'] ?? 'Erreur inconnue pendant la prédiction.';
      }
    } else {
      $error = 'Sortie invalide de Python : ' . htmlspecialchars($cleanOutput);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardioPredict - Prédiction</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .tooltip-wrap { position: relative; display: inline-block; margin-left: 6px; cursor: help; }
    .tooltip-icon { width: 17px; height: 17px; border-radius: 50%; background: var(--primary-soft);
                    color: var(--primary); font-size: 11px; font-weight: 700; display: inline-flex;
                    align-items: center; justify-content: center; border: 1px solid var(--line); }
    .tooltip-text { visibility: hidden; opacity: 0; width: 240px; background: #16304f; color: #fff;
                    border-radius: 10px; padding: 8px 12px; font-size: 12px; line-height: 1.5;
                    position: absolute; z-index: 99; bottom: 130%; left: 50%; transform: translateX(-50%);
                    transition: opacity .2s; pointer-events: none; }
    .tooltip-wrap:hover .tooltip-text { visibility: visible; opacity: 1; }
    .field-block label { display: flex; align-items: center; }
  </style>
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero prediction-hero">
        <h1>Choisir un mode de prédiction</h1>
        <p>
          Le site propose deux niveaux d'analyse. Le test classique utilise des variables simples à renseigner.
          Le test avancé demande des données cliniques plus précises mais offre une meilleure performance (ROC-AUC ≈ 0,87 vs 0,80).
        </p>
        <div class="mode-switch">
          <a href="prediction.php?mode=cardio" class="mode-pill <?= $mode === 'cardio' ? 'active' : '' ?>">Test classique</a>
          <a href="prediction.php?mode=heart"  class="mode-pill <?= $mode === 'heart'  ? 'active' : '' ?>">Test avancé</a>
        </div>
      </section>

      <div class="grid-2 prediction-layout">
        <div class="card">
          <div class="mode-card-head">
            <span class="badge"><?= htmlspecialchars($currentConfig['title']) ?></span>
            <h2><?= htmlspecialchars($currentConfig['dataset_name']) ?></h2>
            <p class="small-note"><?= htmlspecialchars($currentConfig['subtitle']) ?></p>
          </div>

          <p class="prediction-description"><?= htmlspecialchars($currentConfig['description']) ?></p>

          <form method="post">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
            <div class="form-grid">
              <?php foreach ($currentConfig['fields'] as $name => $field): ?>
                <div class="field-block <?= $field['type'] === 'select' ? 'field-select' : '' ?>">
                  <label for="<?= htmlspecialchars($name) ?>">
                    <?= htmlspecialchars($field['label']) ?>
                    <?php if (!empty($field['tooltip'])): ?>
                      <span class="tooltip-wrap">
                        <span class="tooltip-icon">?</span>
                        <span class="tooltip-text"><?= htmlspecialchars($field['tooltip']) ?></span>
                      </span>
                    <?php endif; ?>
                  </label>

                  <?php if ($field['type'] === 'select'): ?>
                    <select id="<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>">
                      <?php foreach ($field['options'] as $optVal => $optLabel): ?>
                        <option value="<?= htmlspecialchars($optVal) ?>" <?= (string)$values[$name] === (string)$optVal ? 'selected' : '' ?>>
                          <?= htmlspecialchars($optLabel) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  <?php else: ?>
                    <input
                      type="<?= htmlspecialchars($field['type']) ?>"
                      id="<?= htmlspecialchars($name) ?>"
                      name="<?= htmlspecialchars($name) ?>"
                      value="<?= htmlspecialchars($values[$name]) ?>"
                      placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                      <?= isset($field['step']) ? 'step="' . htmlspecialchars($field['step']) . '"' : '' ?>
                      <?= isset($field['min'])  ? 'min="'  . htmlspecialchars($field['min'])  . '"' : '' ?>
                      <?= isset($field['max'])  ? 'max="'  . htmlspecialchars($field['max'])  . '"' : '' ?>
                      <?= !empty($field['required']) ? 'required' : '' ?>
                    >
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-full">Lancer la prédiction</button>
          </form>
        </div>

        <div class="card result-panel">
          <h2>Résultat du modèle</h2>
          <p class="small-note">Le résultat affiché correspond au modèle sélectionné ci-dessus.</p>

          <?php if ($error !== ''): ?>
            <div class="error-box"><?= $error ?></div>

          <?php elseif ($result): ?>
            <div class="result-box <?= htmlspecialchars($result['risk_label_css'] ?? '') ?>">
              <p><strong>Type de test :</strong> <?= htmlspecialchars($currentConfig['title']) ?></p>
              <p><strong>Jeu de données :</strong> <?= htmlspecialchars($result['dataset_name'] ?? '') ?></p>
              <p><strong>Niveau de risque :</strong> <?= htmlspecialchars($result['risk_label'] ?? '') ?></p>
              <p><strong>Probabilité estimée :</strong> <?= htmlspecialchars((string)($result['probability_percent'] ?? '')) ?> %</p>
              <p><strong>Prédiction :</strong> <?= htmlspecialchars($result['prediction_label'] ?? '') ?></p>
              <p><?= htmlspecialchars($result['message'] ?? '') ?></p>
              <p class="disclaimer">
                ⚠️ Cette prédiction est une estimation basée sur un modèle statistique entraîné sur des données publiques.
                Elle ne remplace pas un avis médical professionnel.
              </p>
            </div>

            <table class="table-like metrics-table">
              <tr><th>Indicateur</th><th>Valeur</th></tr>
              <tr><td>Modèle utilisé</td><td><?= htmlspecialchars($result['model_name'] ?? '') ?></td></tr>
              <?php if (!empty($result['accuracy'])):  ?><tr><td>Accuracy (test)</td><td><?= htmlspecialchars((string)$result['accuracy']) ?></td></tr><?php endif; ?>
              <?php if (!empty($result['roc_auc'])):   ?><tr><td>ROC-AUC (test)</td><td><?= htmlspecialchars((string)$result['roc_auc']) ?></td></tr><?php endif; ?>
              <?php if (!empty($result['f1_score'])):  ?><tr><td>F1-Score (test)</td><td><?= htmlspecialchars((string)$result['f1_score']) ?></td></tr><?php endif; ?>
              <?php if (!empty($result['precision'])): ?><tr><td>Précision (test)</td><td><?= htmlspecialchars((string)$result['precision']) ?></td></tr><?php endif; ?>
              <?php if (!empty($result['recall'])):    ?><tr><td>Rappel (test)</td><td><?= htmlspecialchars((string)$result['recall']) ?></td></tr><?php endif; ?>
              <?php if (!empty($result['train_size'])): ?><tr><td>Taille train</td><td><?= htmlspecialchars((string)$result['train_size']) ?></td></tr><?php endif; ?>
              <?php if (!empty($result['test_size'])):  ?><tr><td>Taille test</td><td><?= htmlspecialchars((string)$result['test_size']) ?></td></tr><?php endif; ?>
            </table>

          <?php else: ?>
            <div class="empty-state">
              <p><strong>Aucun résultat pour le moment.</strong></p>
              <p>Choisis un mode, remplis le formulaire, puis lance la prédiction pour afficher l'estimation du modèle.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <section class="info-strip">
        <p>Cette application est un outil pedagogique universitaire. Les resultats fournis sont des estimations statistiques et ne remplacent pas un diagnostic medical. En cas de doute, consultez un professionnel de sante.</p>
      </section>
    </div>
  </main>

  <?php include 'partials_footer.php'; ?>
</body>
</html>
