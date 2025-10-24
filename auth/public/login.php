<?php

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/login.php'; 

view('header', [
    'title'       => 'Login',
    'page_layout' => 'auth', 
]);
?>

<div class="form-page-wrapper">
  <div class="auth-card">
    <?php if (!empty($errors['login'])): ?>
      <div class="flash-message flash-error">
        <?= $errors['login'] ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="post" class="form-card" novalidate>
      <h1>Login</h1>

      <div>
        <label for="username">Username:</label>
        <input
          type="text"
          name="username"
          id="username"
          value="<?= $inputs['username'] ?? '' ?>"
          class="<?= error_class($errors, 'username') ?>"
          autocomplete="username"
          required
        >
        <small class="error"><?= $errors['username'] ?? '' ?></small>
      </div>

      <div>
        <label for="password">Password:</label>
        <input
          type="password"
          name="password"
          id="password"
          class="<?= error_class($errors, 'password') ?>"
          autocomplete="current-password"
          required
        >
        <small class="error"><?= $errors['password'] ?? '' ?></small>
      </div>

      <section>
        <button type="submit">Login</button>
      </section>

      <p class="link">Don’t have an account? <a href="register.php">Register here</a></p>
    </form>
  </div>
</div>

<?php view('footer'); ?>
