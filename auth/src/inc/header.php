<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = $title ?? 'Home';

$layout = (($page_layout ?? 'default') === 'auth') ? 'layout-auth' : 'layout-default';
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title) ?></title>

  <link rel="stylesheet" href="/OTOBUS/auth/public/style.css">
</head>
<body class="<?= $layout ?>">
  <header class="site-header">
  </header>

  <main class="site-main">
    <?php if (function_exists('flash')) { flash(); } ?>
