<?php
declare(strict_types=1);

session_start();

$page = 'dashboard';
$isLoggedIn = !empty($_SESSION['user_id']);
$errors = [];
$lastPrediction = null;
$lastBmi = null;
$lastCalories = null;
$lastBloodPressure = null;
$predictionCount = 0;

require_once __DIR__ . '/config/db.php';

function riskBadgeClass(?string $riskLabel): string
{
  $riskLabel = strtolower((string) $riskLabel);
  if (str_contains($riskLabel, 'faible')) {
    return 'status-badge status-low';
  }
  if (str_contains($riskLabel, 'modere')) {
    return 'status-badge status-moderate';
  }
  if (str_contains($riskLabel, 'eleve')) {
    return 'status-badge status-high';
  }
  return 'status-badge';
}

if ($isLoggedIn) {
  try {
    $pdo = getDatabaseConnection();
    $userId = (int) $_SESSION['user_id'];

    $stmt = $pdo->prepare(
      'SELECT created_at, mode, model_name, probability, risk_label, prediction_label
       FROM prediction_history
       WHERE user_id = :user_id
       ORDER BY created_at DESC
       LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);
    $lastPrediction = $stmt->fetch() ?: null;

    $stmt = $pdo->prepare(
      'SELECT created_at, bmi, category
       FROM bmi_history
       WHERE user_id = :user_id
       ORDER BY created_at DESC
       LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);
    $lastBmi = $stmt->fetch() ?: null;

    $stmt = $pdo->prepare(
      'SELECT created_at, bmr, daily_calories, activity_level
       FROM calorie_history
       WHERE user_id = :user_id
       ORDER BY created_at DESC
       LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);
    $lastCalories = $stmt->fetch() ?: null;

    $stmt = $pdo->prepare(
      'SELECT created_at, ap_hi, ap_lo, pulse_pressure, mean_arterial_pressure, category
       FROM blood_pressure_history
       WHERE user_id = :user_id
       ORDER BY created_at DESC
       LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);
    $lastBloodPressure = $stmt->fetch() ?: null;

    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM prediction_history WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
    $predictionCount = (int) ($stmt->fetch()['total'] ?? 0);
  } catch (Throwable $exception) {
    error_log('Erreur dashboard : ' . $exception->getMessage());
    $errors[] = "Impossible de charger le dashboard pour le moment. Verifie que la base SQL est configuree.";
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardioPredict - Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <span class="kicker">Espace personnel</span>
        <h1>Dashboard</h1>
        <?php if ($isLoggedIn): ?>
          <p>Bonjour <?= htmlspecialchars((string) ($_SESSION['username'] ?? '')) ?>, voici un resume de ton activite CardioPredict.</p>
        <?php else: ?>
          <p>Connecte-toi pour retrouver tes dernieres predictions et tes calculs sante au meme endroit.</p>
        <?php endif; ?>
      </section>

      <?php if (!$isLoggedIn): ?>
        <section class="grid-2">
          <div class="card">
            <h2>Creer un espace personnel</h2>
            <p>La prediction reste disponible sans compte, mais un compte permet de conserver ton historique.</p>
            <div class="hero-actions">
              <a class="btn" href="register.php">Creer un compte</a>
              <a class="btn btn-secondary" href="login.php">Se connecter</a>
            </div>
          </div>
          <div class="card">
            <h2>Acces rapide</h2>
            <div class="hero-actions">
              <a class="btn btn-secondary" href="prediction.php">Prediction</a>
              <a class="btn btn-secondary" href="imc.php">IMC</a>
              <a class="btn btn-secondary" href="calories.php">Calories</a>
              <a class="btn btn-secondary" href="tension.php">Tension</a>
            </div>
          </div>
        </section>
      <?php else: ?>
        <?php if ($errors !== []): ?>
          <div class="error-box">
            <?php foreach ($errors as $error): ?>
              <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <section class="dashboard-grid">
          <div class="dashboard-card">
            <span>Predictions</span>
            <strong><?= $predictionCount ?></strong>
          </div>
          <div class="dashboard-card">
            <span>Dernier IMC</span>
            <strong><?= $lastBmi ? htmlspecialchars((string) $lastBmi['bmi']) : '-' ?></strong>
          </div>
          <div class="dashboard-card">
            <span>Derniere tension</span>
            <strong><?= $lastBloodPressure ? htmlspecialchars((string) $lastBloodPressure['category']) : '-' ?></strong>
          </div>
          <div class="dashboard-card">
            <span>Calories estimees</span>
            <strong><?= $lastCalories ? htmlspecialchars((string) round((float) $lastCalories['daily_calories'])) : '-' ?></strong>
          </div>
        </section>

        <section class="grid-2">
          <div class="card dataset-card">
            <h2>Derniere prediction</h2>
            <?php if ($lastPrediction): ?>
              <p><strong><?= htmlspecialchars((string) $lastPrediction['prediction_label']) ?></strong></p>
              <p><span class="<?= riskBadgeClass($lastPrediction['risk_label'] ?? null) ?>"><?= htmlspecialchars((string) $lastPrediction['risk_label']) ?></span></p>
              <p>Probabilite : <?= htmlspecialchars((string) round(((float) $lastPrediction['probability']) * 100, 2)) ?> %</p>
              <p class="small-note"><?= htmlspecialchars((string) $lastPrediction['created_at']) ?></p>
            <?php else: ?>
              <p class="small-note">Aucune prediction enregistree.</p>
            <?php endif; ?>
          </div>

          <div class="card dataset-card secondary">
            <h2>Dernier calcul calorique</h2>
            <?php if ($lastCalories): ?>
              <p><strong><?= htmlspecialchars((string) $lastCalories['daily_calories']) ?> kcal/jour</strong></p>
              <p>BMR : <?= htmlspecialchars((string) $lastCalories['bmr']) ?> kcal/jour</p>
              <p>Activite : <?= htmlspecialchars((string) $lastCalories['activity_level']) ?></p>
              <p class="small-note"><?= htmlspecialchars((string) $lastCalories['created_at']) ?></p>
            <?php else: ?>
              <p class="small-note">Aucun calcul calorique enregistre.</p>
            <?php endif; ?>
          </div>

          <div class="card">
            <h2>Dernier IMC</h2>
            <?php if ($lastBmi): ?>
              <p><strong><?= htmlspecialchars((string) $lastBmi['bmi']) ?></strong></p>
              <p><span class="status-badge"><?= htmlspecialchars((string) $lastBmi['category']) ?></span></p>
              <p class="small-note"><?= htmlspecialchars((string) $lastBmi['created_at']) ?></p>
            <?php else: ?>
              <p class="small-note">Aucun IMC enregistre.</p>
            <?php endif; ?>
          </div>

          <div class="card">
            <h2>Derniere tension</h2>
            <?php if ($lastBloodPressure): ?>
              <p><strong><?= htmlspecialchars((string) $lastBloodPressure['ap_hi']) ?>/<?= htmlspecialchars((string) $lastBloodPressure['ap_lo']) ?> mmHg</strong></p>
              <p><span class="status-badge"><?= htmlspecialchars((string) $lastBloodPressure['category']) ?></span></p>
              <p>PAM : <?= htmlspecialchars((string) $lastBloodPressure['mean_arterial_pressure']) ?> mmHg</p>
              <p class="small-note"><?= htmlspecialchars((string) $lastBloodPressure['created_at']) ?></p>
            <?php else: ?>
              <p class="small-note">Aucune tension enregistree.</p>
            <?php endif; ?>
          </div>
        </section>

        <section class="info-strip">
          <h2>Liens rapides</h2>
          <div class="hero-actions">
            <a class="btn" href="prediction.php">Prediction</a>
            <a class="btn btn-secondary" href="imc.php">IMC</a>
            <a class="btn btn-secondary" href="calories.php">Calories</a>
            <a class="btn btn-secondary" href="tension.php">Tension</a>
            <a class="btn btn-secondary" href="historique.php">Historique</a>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </main>

  <?php include 'partials_footer.php'; ?>
</body>
</html>
