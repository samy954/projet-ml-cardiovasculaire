<?php
declare(strict_types=1);

session_start();

$page = 'historique';
$isLoggedIn = !empty($_SESSION['user_id']);
$errors = [];
$predictions = [];
$bmiHistory = [];
$calorieHistory = [];
$bloodPressureHistory = [];

require_once __DIR__ . '/config/db.php';

function historyRiskBadgeClass(?string $riskLabel): string
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
      'SELECT created_at, mode, model_name, probability, risk_label, prediction_label,
              age, gender, weight, height, ap_hi, ap_lo, cholesterol, gluc
       FROM prediction_history
       WHERE user_id = :user_id
       ORDER BY created_at DESC
       LIMIT 50'
    );
    $stmt->execute(['user_id' => $userId]);
    $predictions = $stmt->fetchAll();

    $stmt = $pdo->prepare(
      'SELECT created_at, weight, height, bmi, category
       FROM bmi_history
       WHERE user_id = :user_id
       ORDER BY created_at DESC
       LIMIT 50'
    );
    $stmt->execute(['user_id' => $userId]);
    $bmiHistory = $stmt->fetchAll();

    $stmt = $pdo->prepare(
      'SELECT created_at, age, gender, weight, height, activity_level, bmr, daily_calories
       FROM calorie_history
       WHERE user_id = :user_id
       ORDER BY created_at DESC
       LIMIT 50'
    );
    $stmt->execute(['user_id' => $userId]);
    $calorieHistory = $stmt->fetchAll();

    $stmt = $pdo->prepare(
      'SELECT created_at, ap_hi, ap_lo, pulse_pressure, mean_arterial_pressure, category
       FROM blood_pressure_history
       WHERE user_id = :user_id
       ORDER BY created_at DESC
       LIMIT 50'
    );
    $stmt->execute(['user_id' => $userId]);
    $bloodPressureHistory = $stmt->fetchAll();
  } catch (Throwable $exception) {
    error_log('Erreur historique : ' . $exception->getMessage());
    $errors[] = "Impossible de charger l'historique pour le moment. Verifie que la base SQL est configuree.";
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardioPredict - Historique</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <span class="kicker">Suivi personnel</span>
        <h1>Historique</h1>
        <p>Retrouve tes predictions et tes calculs sante enregistres dans CardioPredict.</p>
      </section>

      <?php if (!$isLoggedIn): ?>
        <section class="card">
          <h2>Connexion requise</h2>
          <p>Connecte-toi ou cree un compte pour consulter ton historique personnel.</p>
          <div class="hero-actions">
            <a class="btn" href="login.php">Se connecter</a>
            <a class="btn btn-secondary" href="register.php">Creer un compte</a>
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

        <section class="history-summary">
          <div class="stat-box">
            <strong><?= count($predictions) ?></strong>
            <span>predictions recentes</span>
          </div>
          <div class="stat-box">
            <strong><?= count($bmiHistory) ?></strong>
            <span>calculs IMC</span>
          </div>
          <div class="stat-box">
            <strong><?= count($calorieHistory) ?></strong>
            <span>calculs calories</span>
          </div>
          <div class="stat-box">
            <strong><?= count($bloodPressureHistory) ?></strong>
            <span>analyses tension</span>
          </div>
        </section>

        <section class="section-box">
          <h2>Predictions</h2>
          <?php if ($predictions === []): ?>
            <p class="small-note">Aucune prediction enregistree pour le moment.</p>
          <?php else: ?>
            <table class="table-like">
              <tr>
                <th>Date</th><th>Mode</th><th>Modele</th><th>Probabilite</th><th>Risque</th><th>Prediction</th>
              </tr>
              <?php foreach ($predictions as $row): ?>
                <tr>
                  <td><?= htmlspecialchars((string) $row['created_at']) ?></td>
                  <td><?= htmlspecialchars((string) $row['mode']) ?></td>
                  <td><?= htmlspecialchars((string) $row['model_name']) ?></td>
                  <td><?= htmlspecialchars((string) round(((float) $row['probability']) * 100, 2)) ?> %</td>
                  <td><span class="<?= historyRiskBadgeClass($row['risk_label'] ?? null) ?>"><?= htmlspecialchars((string) $row['risk_label']) ?></span></td>
                  <td><?= htmlspecialchars((string) $row['prediction_label']) ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>
        </section>

        <section class="section-box">
          <h2>IMC</h2>
          <?php if ($bmiHistory === []): ?>
            <p class="small-note">Aucun calcul IMC enregistre pour le moment.</p>
          <?php else: ?>
            <table class="table-like">
              <tr><th>Date</th><th>Poids</th><th>Taille</th><th>IMC</th><th>Categorie</th></tr>
              <?php foreach ($bmiHistory as $row): ?>
                <tr>
                  <td><?= htmlspecialchars((string) $row['created_at']) ?></td>
                  <td><?= htmlspecialchars((string) $row['weight']) ?> kg</td>
                  <td><?= htmlspecialchars((string) $row['height']) ?> cm</td>
                  <td><?= htmlspecialchars((string) $row['bmi']) ?></td>
                  <td><?= htmlspecialchars((string) $row['category']) ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>
        </section>

        <section class="section-box">
          <h2>Calories</h2>
          <?php if ($calorieHistory === []): ?>
            <p class="small-note">Aucun calcul calorique enregistre pour le moment.</p>
          <?php else: ?>
            <table class="table-like">
              <tr><th>Date</th><th>Age</th><th>Sexe</th><th>Activite</th><th>BMR</th><th>Calories/jour</th></tr>
              <?php foreach ($calorieHistory as $row): ?>
                <tr>
                  <td><?= htmlspecialchars((string) $row['created_at']) ?></td>
                  <td><?= htmlspecialchars((string) $row['age']) ?></td>
                  <td><?= htmlspecialchars((string) $row['gender']) ?></td>
                  <td><?= htmlspecialchars((string) $row['activity_level']) ?></td>
                  <td><?= htmlspecialchars((string) $row['bmr']) ?></td>
                  <td><?= htmlspecialchars((string) $row['daily_calories']) ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>
        </section>

        <section class="section-box">
          <h2>Tension arterielle</h2>
          <?php if ($bloodPressureHistory === []): ?>
            <p class="small-note">Aucune analyse de tension enregistree pour le moment.</p>
          <?php else: ?>
            <table class="table-like">
              <tr><th>Date</th><th>Systolique</th><th>Diastolique</th><th>Pression pulsee</th><th>PAM</th><th>Categorie</th></tr>
              <?php foreach ($bloodPressureHistory as $row): ?>
                <tr>
                  <td><?= htmlspecialchars((string) $row['created_at']) ?></td>
                  <td><?= htmlspecialchars((string) $row['ap_hi']) ?></td>
                  <td><?= htmlspecialchars((string) $row['ap_lo']) ?></td>
                  <td><?= htmlspecialchars((string) $row['pulse_pressure']) ?></td>
                  <td><?= htmlspecialchars((string) $row['mean_arterial_pressure']) ?></td>
                  <td><?= htmlspecialchars((string) $row['category']) ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>
        </section>
      <?php endif; ?>
    </div>
  </main>

  <?php include 'partials_footer.php'; ?>
</body>
</html>
