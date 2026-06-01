<?php
declare(strict_types=1);

session_start();

$page = 'profile';
$user = null;
$predictionCount = 0;
$errors = [];

require_once __DIR__ . '/config/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $pdo = getDatabaseConnection();

    $stmt = $pdo->prepare('SELECT id, username, email, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    $countStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM prediction_history WHERE user_id = :user_id');
    $countStmt->execute(['user_id' => (int) $_SESSION['user_id']]);
    $predictionCount = (int) ($countStmt->fetch()['total'] ?? 0);
} catch (Throwable $exception) {
    error_log('Erreur profil : ' . $exception->getMessage());
    $errors[] = "Impossible de charger le profil pour le moment.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardioPredict - Profil</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <h1>Mon profil</h1>
        <p>Retrouve les informations de ton compte CardioPredict.</p>
      </section>

      <section class="grid-2">
        <div class="card">
          <h2>Informations du compte</h2>

          <?php if ($errors !== []): ?>
            <div class="error-box">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php elseif ($user): ?>
            <table class="table-like">
              <tr><th>Champ</th><th>Valeur</th></tr>
              <tr><td>Nom d'utilisateur</td><td><?= htmlspecialchars((string) $user['username']) ?></td></tr>
              <tr><td>Email</td><td><?= htmlspecialchars((string) $user['email']) ?></td></tr>
              <tr><td>Compte cree le</td><td><?= htmlspecialchars((string) $user['created_at']) ?></td></tr>
              <tr><td>Predictions enregistrees</td><td><?= $predictionCount ?></td></tr>
            </table>
          <?php endif; ?>
        </div>

        <div class="card">
          <h2>Acces rapides</h2>
          <p>Continue ton suivi depuis les pages principales de l'application.</p>
          <div class="hero-actions">
            <a class="btn" href="prediction.php">Faire une prediction</a>
            <a class="btn btn-secondary" href="dashboard.php">Dashboard</a>
            <a class="btn btn-secondary" href="historique.php">Historique</a>
            <a class="btn btn-secondary" href="logout.php">Deconnexion</a>
          </div>
        </div>
      </section>
    </div>
  </main>

  <?php include 'partials_footer.php'; ?>
</body>
</html>
