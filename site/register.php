<?php
declare(strict_types=1);

session_start();

$page = 'register';
$errors = [];
$username = '';
$email = '';

require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if ($username === '' || strlen($username) < 3) {
        $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caracteres.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caracteres.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Les deux mots de passe ne correspondent pas.';
    }

    if ($errors === []) {
        try {
            $pdo = getDatabaseConnection();

            $check = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
            $check->execute([
                'username' => $username,
                'email' => $email,
            ]);

            if ($check->fetch()) {
                $errors[] = "Ce nom d'utilisateur ou cet email est deja utilise.";
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)'
                );
                $insert->execute([
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);

                $_SESSION['user_id'] = (int) $pdo->lastInsertId();
                $_SESSION['username'] = $username;

                header('Location: dashboard.php');
                exit;
            }
        } catch (Throwable $exception) {
            error_log('Erreur inscription : ' . $exception->getMessage());
            $errors[] = "Impossible de creer le compte pour le moment. Verifie que la base SQL est configuree.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardioPredict - Inscription</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'partials_header.php'; ?>

  <main>
    <div class="container">
      <section class="hero">
        <h1>Creer un compte</h1>
        <p>Un compte permet de retrouver tes predictions et tes calculs sante dans l'historique.</p>
      </section>

      <section class="grid-2">
        <div class="card">
          <h2>Inscription</h2>

          <?php if ($errors !== []): ?>
            <div class="error-box">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required minlength="3">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required minlength="8">

            <label for="password_confirm">Confirmer le mot de passe</label>
            <input type="password" id="password_confirm" name="password_confirm" required minlength="8">

            <button type="submit" class="btn btn-full">Creer mon compte</button>
          </form>
        </div>

        <div class="card">
          <h2>Deja inscrit ?</h2>
          <p>Connecte-toi pour acceder a ton dashboard et consulter tes derniers resultats.</p>
          <a class="btn btn-secondary" href="login.php">Se connecter</a>
        </div>
      </section>
    </div>
  </main>

  <?php include 'partials_footer.php'; ?>
</body>
</html>
