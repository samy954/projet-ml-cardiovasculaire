<?php
declare(strict_types=1);

session_start();

$page = 'tension';
$errors = [];
$result = null;

$apHi = trim((string) ($_POST['ap_hi'] ?? ''));
$apLo = trim((string) ($_POST['ap_lo'] ?? ''));

require_once __DIR__ . '/config/db.php';

function bloodPressureCategory(int $apHi, int $apLo): string
{
  if ($apHi < $apLo) {
    return 'Valeur incoherente';
  }

  if ($apHi < 120 && $apLo < 80) {
    return 'Normale';
  }

  if ($apHi < 130 && $apLo < 80) {
    return 'Elevee';
  }

  return 'Hypertension possible';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $apHiValue = (int) $apHi;
  $apLoValue = (int) $apLo;

  if ($apHiValue < 60 || $apHiValue > 250) {
    $errors[] = 'La pression systolique doit etre comprise entre 60 et 250 mmHg.';
  }

  if ($apLoValue < 40 || $apLoValue > 180) {
    $errors[] = 'La pression diastolique doit etre comprise entre 40 et 180 mmHg.';
  }

  if ($errors === []) {
    $pulsePressure = $apHiValue - $apLoValue;
    $meanArterialPressure = round($apLoValue + ($apHiValue - $apLoValue) / 3, 2);
    $category = bloodPressureCategory($apHiValue, $apLoValue);

    $result = [
      'ap_hi' => $apHiValue,
      'ap_lo' => $apLoValue,
      'pulse_pressure' => $pulsePressure,
      'mean_arterial_pressure' => $meanArterialPressure,
      'category' => $category,
    ];

    $pdo = getOptionalDatabaseConnection();
    if ($pdo) {
      try {
        $stmt = $pdo->prepare(
          'INSERT INTO blood_pressure_history
            (user_id, ap_hi, ap_lo, pulse_pressure, mean_arterial_pressure, category)
           VALUES
            (:user_id, :ap_hi, :ap_lo, :pulse_pressure, :mean_arterial_pressure, :category)'
        );
        $stmt->execute([
          'user_id' => $_SESSION['user_id'] ?? null,
          'ap_hi' => $apHiValue,
          'ap_lo' => $apLoValue,
          'pulse_pressure' => $pulsePressure,
          'mean_arterial_pressure' => $meanArterialPressure,
          'category' => $category,
        ]);
      } catch (Throwable $exception) {
        error_log('Historique tension non enregistre : ' . $exception->getMessage());
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardioPredict - Tension</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <h1>Tension arterielle</h1>
        <p>Analyse rapidement une mesure systolique/diastolique et calcule deux indicateurs utiles.</p>
      </section>

      <section class="grid-2">
        <div class="card">
          <h2>Analyser une mesure</h2>

          <?php if ($errors !== []): ?>
            <div class="error-box">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post">
            <label for="ap_hi">Pression systolique (mmHg)</label>
            <input type="number" id="ap_hi" name="ap_hi" min="60" max="250" value="<?= htmlspecialchars($apHi) ?>" required>

            <label for="ap_lo">Pression diastolique (mmHg)</label>
            <input type="number" id="ap_lo" name="ap_lo" min="40" max="180" value="<?= htmlspecialchars($apLo) ?>" required>

            <button type="submit" class="btn btn-full">Analyser</button>
          </form>
        </div>

        <div class="card result-panel">
          <h2>Resultat</h2>
          <?php if ($result): ?>
            <div class="result-box">
              <p><strong>Categorie :</strong> <?= htmlspecialchars((string) $result['category']) ?></p>
              <p><strong>Pression pulsee :</strong> <?= htmlspecialchars((string) $result['pulse_pressure']) ?> mmHg</p>
              <p><strong>Pression arterielle moyenne :</strong> <?= htmlspecialchars((string) $result['mean_arterial_pressure']) ?> mmHg</p>
              <p class="small-note">Une mesure isolee ne suffit pas a poser un diagnostic. La tension doit etre interpretee dans son contexte.</p>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <p><strong>Aucune mesure pour le moment.</strong></p>
              <p>Entre une pression systolique et diastolique pour obtenir l'analyse.</p>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section class="info-strip">
        <p>Cette application est un outil pedagogique universitaire. Les resultats fournis sont des estimations statistiques et ne remplacent pas un diagnostic medical. En cas de doute, consultez un professionnel de sante.</p>
      </section>
    </div>
  </main>

  <?php include 'partials_footer.php'; ?>
</body>
</html>
