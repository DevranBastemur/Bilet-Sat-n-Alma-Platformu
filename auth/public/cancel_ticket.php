<?php

require __DIR__ . '/../src/bootstrap.php';

require_role('kullanici');

if (!is_post_request()) {
    redirect_to(BASE_URL . '/my_tickets.php');
}

$ticket_id = $_POST['ticket_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$ticket_id || !$user_id) {
    flash('İptal işlemi için gerekli bilgiler eksik.', 'flash_error');
    redirect_to(BASE_URL . '/my_tickets.php');
}

$success = cancel_ticket($ticket_id, $user_id);

if ($success) {
    flash('Biletiniz başarıyla iptal edildi ve ücreti bakiyenize iade edildi.', 'flash_success');
}

redirect_to(BASE_URL . '/my_tickets.php');