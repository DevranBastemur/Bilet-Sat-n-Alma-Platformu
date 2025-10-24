<?php

require __DIR__ . '/../src/bootstrap.php';

if (!is_post_request() || !is_user_in_role('kullanici')) {
    redirect_to(BASE_URL . '/index.php');
}

$booking = $_SESSION['booking'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$booking || !$user_id) {
    flash("İşlem verisi eksik veya geçersiz. Lütfen tekrar deneyin.", 'flash_error');
    redirect_to(BASE_URL . '/index.php');
}

$original_price = (float)$booking['total_price'];
$final_price = $original_price; 
$discount_amount = 0; 

$coupon_code_from_post = trim($_POST['coupon'] ?? '');
$coupon_code_from_session = $_SESSION['applied_coupon'] ?? null;

$coupon_code_to_process = !empty($coupon_code_from_post) ? $coupon_code_from_post : $coupon_code_from_session;

if (!empty($coupon_code_to_process)) {
    $trip = get_trip_by_id($booking['trip_id']);
    $coupon = validate_coupon($coupon_code_to_process, $trip['company_id']);

    if ($coupon) {
        $discount_amount = $original_price * (float)$coupon['discount'];
        $final_price = $original_price - $discount_amount;
        
        $final_price = max(0, $final_price);
        
        $_SESSION['applied_coupon'] = $coupon['code'];
        $_SESSION['discount_amount'] = $discount_amount;

    } else {
        flash("Geçersiz veya süresi dolmuş kupon kodu girdiniz.", 'flash_error');
        unset($_SESSION['applied_coupon'], $_SESSION['discount_amount']); 
        redirect_to(BASE_URL . '/checkout.php');
    }
}

$success = process_ticket_purchase($user_id, $booking, $final_price, $coupon_code_to_process);

if ($success) {
    unset($_SESSION['booking'], $_SESSION['applied_coupon'], $_SESSION['discount_amount']); 
    flash('🎉 Tebrikler! Biletiniz başarıyla satın alındı. Detayları biletlerim sayfasında görebilirsiniz.', 'flash_success');
    redirect_to(BASE_URL . '/my_tickets.php'); 
} else {
    redirect_to(BASE_URL . '/checkout.php');
}
