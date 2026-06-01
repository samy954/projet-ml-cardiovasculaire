<?php $isLoggedIn = !empty($_SESSION['user_id']); ?>
<header>
  <div class="container header-row">
    <div class="logo">
      <span class="logo-badge">CP</span>
      <span>CardioPredict</span>
    </div>
    <nav>
      <a href="index.php" class="<?= $page === 'accueil' ? 'active' : '' ?>">Accueil</a>
      <a href="prediction.php" class="<?= $page === 'prediction' ? 'active' : '' ?>">Prediction</a>
      <a href="imc.php" class="<?= $page === 'imc' ? 'active' : '' ?>">IMC</a>
      <a href="calories.php" class="<?= $page === 'calories' ? 'active' : '' ?>">Calories</a>
      <a href="tension.php" class="<?= $page === 'tension' ? 'active' : '' ?>">Tension</a>
      <a href="dashboard.php" class="<?= $page === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
      <a href="historique.php" class="<?= $page === 'historique' ? 'active' : '' ?>">Historique</a>
      <a href="visualisations.php" class="<?= $page === 'visualisations' ? 'active' : '' ?>">Visualisations</a>
      <a href="methode.php" class="<?= $page === 'methode' ? 'active' : '' ?>">Methode</a>
      <?php if ($isLoggedIn): ?>
        <a href="profile.php" class="<?= $page === 'profile' ? 'active' : '' ?>">Profil</a>
        <a href="logout.php">Deconnexion</a>
      <?php else: ?>
        <a href="login.php" class="<?= in_array($page, ['login', 'register'], true) ? 'active' : '' ?>">Connexion</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
