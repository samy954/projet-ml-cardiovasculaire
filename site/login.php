<?php
declare(strict_types=1);

session_start();

$page = 'login';
$errors = [];
$email = '';
$success = isset($_GET['logged_out']) ? 'Vous avez bien ete deconnecte.' : '';

require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if ($password === '') {
        $errors[] = 'Le mot de passe est obligatoire.';
    }

    if ($errors === []) {
        try {
            $pdo = getDatabaseConnection();
            $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, (string) $user['password_hash'])) {
                $errors[] = 'Email ou mot de passe incorrect.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = (string) $user['username'];

                header('Location: dashboard.php');
                exit;
            }
        } catch (Throwable $exception) {
            error_log('Erreur connexion : ' . $exception->getMessage());
            $errors[] = "Impossible de se connecter pour le moment. Verifie que la base SQL est configuree.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardioPredict - Connexion</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <h1>Connexion</h1>
        <p>Connecte-toi pour sauvegarder automatiquement tes predictions et tes calculs sante.</p>
      </section>

      <section class="grid-2">
        <div class="card">
          <h2>Se connecter</h2>

          <?php if ($success !== ''): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
          <?php endif; ?>

          <?php if ($errors !== []): ?>
            <div class="error-box">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn btn-full">Connexion</button>
          </form>
        </div>

        <div class="card">
          <h2>Pas encore de compte ?</h2>
          <p>L'inscription permet de conserver un historique personnel des predictions et outils sante.</p>
          <a class="btn btn-secondary" href="register.php">Creer un compte</a>
        </div>
      </section>
    </div>
  </main>

  <?php include 'partials_footer.php'; ?>
</body>
</html>
