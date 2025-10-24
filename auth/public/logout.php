<?php

require __DIR__ . '/../src/bootstrap.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

setcookie('remember', '', time() - 3600, '/');

session_destroy();

if (function_exists('flash')) {
    flash('success', 'Çıkış yapıldı.');
}

header('Location: index.php');
exit;
