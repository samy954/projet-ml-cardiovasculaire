<?php
declare(strict_types=1);

session_start();

$page = 'imc';
$errors = [];
$result = null;
$weight = trim((string) ($_POST['weight'] ?? ''));
$height = trim((string) ($_POST['height'] ?? ''));

require_once __DIR__ . '/config/db.php';

function bmiCategory(float $bmi): string
{
  if ($bmi < 18.5) {
    return 'Insuffisance ponderale';
  }

  if ($bmi < 25) {
    return 'Poids normal';
  }

  if ($bmi < 30) {
    return 'Surpoids';
  }

  return 'Obesite';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $weightValue = (float) str_replace(',', '.', $weight);
  $heightValue = (float) str_replace(',', '.', $height);

  if ($weightValue < 20 || $weightValue > 300) {
    $errors[] = 'Le poids doit etre compris entre 20 et 300 kg.';
  }

  if ($heightValue < 50 || $heightValue > 250) {
    $errors[] = 'La taille doit etre comprise entre 50 et 250 cm.';
  }

  if ($errors === []) {
    $heightMeters = $heightValue / 100;
    $bmi = round($weightValue / ($heightMeters * $heightMeters), 2);
    $category = bmiCategory($bmi);

    $result = [
      'weight' => $weightValue,
      'height' => $heightValue,
      'bmi' => $bmi,
      'category' => $category,
    ];

    $pdo = getOptionalDatabaseConnection();
    if ($pdo) {
      try {
        $stmt = $pdo->prepare(
          'INSERT INTO bmi_history (user_id, weight, height, bmi, category)
           VALUES (:user_id, :weight, :height, :bmi, :category)'
        );
        $stmt->execute([
          'user_id' => $_SESSION['user_id'] ?? null,
          'weight' => $weightValue,
          'height' => $heightValue,
          'bmi' => $bmi,
          'category' => $category,
        ]);
      } catch (Throwable $exception) {
        error_log('Historique IMC non enregistre : ' . $exception->getMessage());
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
  <title>CardioPredict - IMC</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <h1>Calculateur IMC</h1>
        <p>Calcule ton indice de masse corporelle a partir de ton poids et de ta taille.</p>
      </section>

      <section class="grid-2">
        <div class="card">
          <h2>Calculer mon IMC</h2>

          <?php if ($errors !== []): ?>
            <div class="error-box">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post">
            <label for="weight">Poids (kg)</label>
            <input type="number" id="weight" name="weight" min="20" max="300" step="0.1" value="<?= htmlspecialchars($weight) ?>" required>

            <label for="height">Taille (cm)</label>
            <input type="number" id="height" name="height" min="50" max="250" step="0.1" value="<?= htmlspecialchars($height) ?>" required>

            <button type="submit" class="btn btn-full">Calculer l'IMC</button>
          </form>
        </div>

        <div class="card result-panel">
          <h2>Resultat</h2>
          <?php if ($result): ?>
            <div class="result-box">
              <p><strong>IMC :</strong> <?= htmlspecialchars((string) $result['bmi']) ?></p>
              <p><strong>Categorie :</strong> <?= htmlspecialchars((string) $result['category']) ?></p>
              <p class="small-note">Un IMC est un indicateur general : il ne tient pas compte de la masse musculaire, de l'age ou du contexte medical.</p>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <p><strong>Aucun calcul pour le moment.</strong></p>
              <p>Renseigne ton poids et ta taille pour obtenir une estimation.</p>
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
