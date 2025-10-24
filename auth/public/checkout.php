<?php

require __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/inc/coupons.php'; 


require_once __DIR__ . '/../src/inc/companies.php'; 

if (!is_user_in_role('kullanici')) {
    flash("Ödeme işlemi sadece normal kullanıcılar için geçerlidir. Yetkiniz yetersiz.", 'flash_error');
    if (is_user_logged_in()) {
        redirect_to(BASE_URL . '/index.php');
    } else {
        redirect_to(BASE_URL . '/login.php');
    }
}

$booking = $_SESSION['booking'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$booking) {
    flash('Ödeme işlemine başlamak için önce koltuk seçimi yapmalısınız.', 'flash_error');
    redirect_to(BASE_URL . '/index.php');
}

$user_balance = get_user_balance($user_id); 

$trip = get_trip_by_id($booking['trip_id']);
$company_name = get_company_by_id($trip['company_id'])['name'] ?? 'Bilinmiyor';

$total_price = (float)$booking['total_price'];
$discount = 0;
$final_price = $total_price;
$coupon_code_value = ''; 


if (is_post_request() && isset($_POST['coupon'])) {
    $coupon_code_input = trim($_POST['coupon']);
    $coupon_code_value = $coupon_code_input; 

    if (!empty($coupon_code_input)) {
        $coupon = validate_coupon($coupon_code_input, $trip['company_id']);
        
        if ($coupon) {
            $discount = $total_price * (float)$coupon['discount'];
            $final_price = $total_price - $discount;
            $final_price = max(0, $final_price);

            $_SESSION['applied_coupon'] = $coupon['code'];
            $_SESSION['discount_amount'] = $discount;
            
        } else {
            unset($_SESSION['applied_coupon'], $_SESSION['discount_amount']);
            flash("Geçersiz veya süresi dolmuş kupon kodu girdiniz. Lütfen tekrar deneyin.", 'flash_error');
        }
    } else {
        unset($_SESSION['applied_coupon'], $_SESSION['discount_amount']);
    }
}

$balance_alert = $user_balance < $final_price;

?>

<?php view('header', ['title' => 'Ödeme Özeti']) ?>
<main class="checkout-main">
    <h1>Ödeme Özeti ve Bakiye</h1>
    <?php flash() ?>

    <div class="checkout-summary-grid">
        <div class="summary-details">
            <h2>Sefer Bilgileri</h2>
            <p><strong>Firma:</strong> <?= htmlspecialchars($company_name) ?></p>
            <p><strong>Güzergah:</strong> <?= htmlspecialchars($trip['departure_city']) ?> &rarr; <?= htmlspecialchars($trip['arrival_city']) ?></p>
            <p><strong>Kalkış:</strong> <?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></p>
            <p><strong>Koltuklar:</strong> 
                <span class="seat-list"><?= implode(', ', $booking['seats']) ?></span>
                (<?= count($booking['seats']) ?> adet)
            </p>
            <p><strong>Birim Fiyat:</strong> <?= number_format($booking['unit_price'], 2, ',', '.') ?> TL</p>
        </div>

        <div class="summary-payment">
            <h2>Ödeme</h2>
            <div class="price-line">
                <span>Toplam Koltuk Ücreti:</span>
                <strong><?= number_format($total_price, 2, ',', '.') ?> TL</strong>
            </div>
            
            <div class="price-line discount-line">
                <span>Kupon İndirimi:</span>
                <strong>- <?= number_format($discount, 2, ',', '.') ?> TL</strong>
            </div>

            <div class="total-price-line">
                <span>Ödenecek Tutar:</span>
                <strong><?= number_format($final_price, 2, ',', '.') ?> TL</strong>
            </div>

            <div class="balance-line">
                <span>Mevcut Bakiyeniz:</span>
                <strong class="<?= $balance_alert ? 'text-error' : 'text-success' ?>"><?= number_format($user_balance, 2, ',', '.') ?> TL</strong>
            </div>
            
            <?php if ($balance_alert): ?>
                <div class="alert-error">
                    Bakiyeniz yetersizdir! Lütfen bakiye yükleyin.
                </div>
                <a href="<?= BASE_URL ?>/deposit.php" class="btn btn-warning full-width" style="margin-top:15px;">Bakiye Yükle</a>
            <?php endif; ?>
        </div>
    </div>

    <form action="process_payment.php" method="post" class="payment-form">
        <input type="hidden" name="trip_id" value="<?= $booking['trip_id'] ?>">
        
        <div class="coupon-section">
            <label for="coupon">İndirim Kuponu Kodu:</label>
            <input type="text" name="coupon" id="coupon" placeholder="Kupon Kodunu Giriniz" value="<?= htmlspecialchars($coupon_code_value) ?>">
            <button type="submit" formaction="checkout.php" formmethod="post" class="btn btn-secondary">Uygula</button>
        </div>
        
        <?php if (!$balance_alert): ?>
            <button type="submit" class="btn btn-proceed full-width" style="margin-top:20px;">
                Ödemeyi Tamamla (<?= number_format($final_price, 2, ',', '.') ?> TL)
            </button>
        <?php endif; ?>
    </form>
    
    <p style="margin-top: 20px;"><a href="<?= BASE_URL ?>/buy_ticket.php?trip_id=<?= $booking['trip_id'] ?>">← Koltukları Düzenle</a></p>
</main>
<?php view('footer') ?>