<?php

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/register.php';

$errors = isset($errors) && is_array($errors) ? $errors : [];
$inputs = isset($inputs) && is_array($inputs) ? $inputs : [];

view('header', [
    'title'       => 'Register',
    'page_layout' => 'auth', 
]);
?>

<div class="form-page-wrapper">
  <div class="auth-card">
    <?php if (!empty($errors['register'])): ?>
      <div class="flash-message flash-error">
        <?= $errors['register'] ?>
      </div>
    <?php endif; ?>

    <form action="register.php" method="post" class="form-card" novalidate>
      <h1>Sign Up</h1>

      <div>
        <label for="username">Username:</label>
        <input
          type="text"
          name="username"
          id="username"
          value="<?= htmlspecialchars($inputs['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          class="<?= error_class($errors, 'username') ?>"
          autocomplete="username"
          required
        >
        <small class="error"><?= $errors['username'] ?? '' ?></small>
      </div>

      <div>
        <label for="email">Email:</label>
        <input
          type="email"
          name="email"
          id="email"
          value="<?= htmlspecialchars($inputs['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          class="<?= error_class($errors, 'email') ?>"
          autocomplete="email"
          required
        >
        <small class="error"><?= $errors['email'] ?? '' ?></small>
      </div>

      <div>
        <label for="password">Password:</label>
        <input
          type="password"
          name="password"
          id="password"
          value="<?= htmlspecialchars($inputs['password'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          class="<?= error_class($errors, 'password') ?>"
          autocomplete="new-password"
          required
        >
        <small class="error"><?= $errors['password'] ?? '' ?></small>
      </div>

      <div>
        <label for="password2">Password Again:</label>
        <input
          type="password"
          name="password2"
          id="password2"
          value="<?= htmlspecialchars($inputs['password2'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          class="<?= error_class($errors, 'password2') ?>"
          autocomplete="new-password"
          required
        >
        <small class="error"><?= $errors['password2'] ?? '' ?></small>
      </div>

      <div class="full-width">
        <label for="agree">
          <input
            type="checkbox"
            name="agree"
            id="agree"
            value="checked"
            <?= !empty($inputs['agree']) ? 'checked' : '' ?>
            required
          />
          I agree with the <a href="#" title="terms of service">terms of service</a>
        </label>
        <small class="error"><?= $errors['agree'] ?? '' ?></small>
      </div>

      <button type="submit">Register</button>

      <p class="link">Already a member? <a href="login.php">Login here</a></p>
    </form>
  </div>
</div>

<?php view('footer'); ?>
