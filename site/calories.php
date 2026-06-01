<?php
declare(strict_types=1);

session_start();

$page = 'calories';
$errors = [];
$result = null;

$age = trim((string) ($_POST['age'] ?? ''));
$gender = (string) ($_POST['gender'] ?? 'male');
$weight = trim((string) ($_POST['weight'] ?? ''));
$height = trim((string) ($_POST['height'] ?? ''));
$activityLevel = (string) ($_POST['activity_level'] ?? 'sedentary');

require_once __DIR__ . '/config/db.php';

$activityLevels = [
  'sedentary' => ['label' => 'Sedentaire', 'factor' => 1.2],
  'light' => ['label' => 'Activite legere', 'factor' => 1.375],
  'moderate' => ['label' => 'Activite moderee', 'factor' => 1.55],
  'intense' => ['label' => 'Activite intense', 'factor' => 1.725],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $ageValue = (int) $age;
  $weightValue = (float) str_replace(',', '.', $weight);
  $heightValue = (float) str_replace(',', '.', $height);

  if ($ageValue < 1 || $ageValue > 120) {
    $errors[] = "L'age doit etre compris entre 1 et 120 ans.";
  }

  if (!in_array($gender, ['male', 'female'], true)) {
    $errors[] = 'Le sexe selectionne est invalide.';
  }

  if ($weightValue < 20 || $weightValue > 300) {
    $errors[] = 'Le poids doit etre compris entre 20 et 300 kg.';
  }

  if ($heightValue < 50 || $heightValue > 250) {
    $errors[] = 'La taille doit etre comprise entre 50 et 250 cm.';
  }

  if (!isset($activityLevels[$activityLevel])) {
    $errors[] = "Le niveau d'activite est invalide.";
  }

  if ($errors === []) {
    $bmr = 10 * $weightValue + 6.25 * $heightValue - 5 * $ageValue;
    $bmr += $gender === 'male' ? 5 : -161;
    $dailyCalories = $bmr * $activityLevels[$activityLevel]['factor'];

    $result = [
      'bmr' => round($bmr, 2),
      'daily_calories' => round($dailyCalories, 2),
      'activity_label' => $activityLevels[$activityLevel]['label'],
    ];

    $pdo = getOptionalDatabaseConnection();
    if ($pdo) {
      try {
        $stmt = $pdo->prepare(
          'INSERT INTO calorie_history
            (user_id, age, gender, weight, height, activity_level, bmr, daily_calories)
           VALUES
            (:user_id, :age, :gender, :weight, :height, :activity_level, :bmr, :daily_calories)'
        );
        $stmt->execute([
          'user_id' => $_SESSION['user_id'] ?? null,
          'age' => $ageValue,
          'gender' => $gender,
          'weight' => $weightValue,
          'height' => $heightValue,
          'activity_level' => $activityLevel,
          'bmr' => $result['bmr'],
          'daily_calories' => $result['daily_calories'],
        ]);
      } catch (Throwable $exception) {
        error_log('Historique calories non enregistre : ' . $exception->getMessage());
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
  <title>CardioPredict - Calories</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <h1>Calculateur de calories</h1>
        <p>Estime ton metabolisme de base et tes besoins caloriques journaliers avec la formule de Mifflin-St Jeor.</p>
      </section>

      <section class="grid-2">
        <div class="card">
          <h2>Estimation calorique</h2>

          <?php if ($errors !== []): ?>
            <div class="error-box">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post">
            <label for="age">Age</label>
            <input type="number" id="age" name="age" min="1" max="120" value="<?= htmlspecialchars($age) ?>" required>

            <label for="gender">Sexe</label>
            <select id="gender" name="gender">
              <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Homme</option>
              <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Femme</option>
            </select>

            <label for="weight">Poids (kg)</label>
            <input type="number" id="weight" name="weight" min="20" max="300" step="0.1" value="<?= htmlspecialchars($weight) ?>" required>

            <label for="height">Taille (cm)</label>
            <input type="number" id="height" name="height" min="50" max="250" step="0.1" value="<?= htmlspecialchars($height) ?>" required>

            <label for="activity_level">Niveau d'activite</label>
            <select id="activity_level" name="activity_level">
              <?php foreach ($activityLevels as $key => $activity): ?>
                <option value="<?= htmlspecialchars($key) ?>" <?= $activityLevel === $key ? 'selected' : '' ?>>
                  <?= htmlspecialchars($activity['label']) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-full">Calculer</button>
          </form>
        </div>

        <div class="card result-panel">
          <h2>Resultat</h2>
          <?php if ($result): ?>
            <div class="result-box">
              <p><strong>Metabolisme de base :</strong> <?= htmlspecialchars((string) $result['bmr']) ?> kcal/jour</p>
              <p><strong>Besoin journalier estime :</strong> <?= htmlspecialchars((string) $result['daily_calories']) ?> kcal/jour</p>
              <p><strong>Activite :</strong> <?= htmlspecialchars((string) $result['activity_label']) ?></p>
              <p class="small-note">Cette estimation sert de repere general et peut varier selon le contexte medical, hormonal et sportif.</p>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <p><strong>Aucun calcul pour le moment.</strong></p>
              <p>Renseigne les informations du formulaire pour obtenir une estimation.</p>
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
