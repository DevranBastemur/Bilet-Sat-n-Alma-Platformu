<?php

require __DIR__ . '/../src/bootstrap.php';

if (!is_user_in_role('kullanici')) {
    flash("Bilet satın alma işlemi sadece normal kullanıcılar için geçerlidir.", 'flash_error');
    
    if (is_user_logged_in()) {
        redirect_to(BASE_URL . '/index.php');
    } else {
        redirect_to(BASE_URL . '/login.php');
    }
} 

$trip_id = $_GET['trip_id'] ?? '';
$trip = null;
$booked_seats = [];
$errors = [];

if (!$trip_id) {
    flash('Seçim yapmak için geçerli bir sefer ID\'si gereklidir.', 'flash_error');
    redirect_to(BASE_URL . '/trips.php');
}

$trip = get_trip_by_id($trip_id); 
if (!$trip) {
    flash('Seçilen sefer bulunamadı.', 'flash_error');
    redirect_to(BASE_URL . '/trips.php');
}

$booked_seat_numbers = get_booked_seats_by_trip_id($trip_id); 

if (is_post_request()) {
    
    $selected_seats = array_map('intval', $_POST['seats'] ?? []); 
    $trip_id_from_post = $_POST['trip_id'] ?? '';
    
    $re_check_booked = array_intersect($selected_seats, $booked_seat_numbers);

    if (empty($selected_seats)) {
        $errors['seats'] = 'Lütfen en az bir koltuk seçiniz.';
    } else if (!empty($re_check_booked)) {
        $errors['seats'] = 'Seçtiğiniz koltuklardan bazıları (' . implode(', ', $re_check_booked) . ') az önce rezerve edildi. Lütfen tekrar seçin.';
    } else if ($trip_id_from_post !== $trip_id) {
        
        flash('Geçersiz sefer bilgisi.', 'flash_error');
        redirect_to(BASE_URL . '/trips.php');
    } else {
        $_SESSION['booking'] = [
            'trip_id' => $trip_id,
            'seats' => $selected_seats,
            'unit_price' => $trip['price'], 
            'total_price' => count($selected_seats) * $trip['price']
        ];
        redirect_to(BASE_URL . '/checkout.php'); 
    }
}


$capacity = $trip['capacity'];
$rows = ceil($capacity / 4); 
?>

<?php view('header', ['title' => 'Koltuk Seçimi']) ?>
<main class="seat-selection-main">
    <h1>Koltuk Seçimi: <?= htmlspecialchars($trip['departure_city']) ?> &rarr; <?= htmlspecialchars($trip['arrival_city']) ?></h1> 
    <p class="trip-details-summary">
        Firma: <strong><?= get_company_by_id($trip['company_id'])['name'] ?? 'Bilinmiyor' ?></strong> | 
        Kalkış: <strong><?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></strong> | 
        Fiyat: <strong><?= number_format($trip['price'], 2, ',', '.') ?> TL</strong>
    </p>

    <form method="post" action="buy_ticket.php?trip_id=<?= $trip_id ?>" class="seat-form">
        <input type="hidden" name="trip_id" value="<?= $trip_id ?>">

        <div class="bus-layout-container">
            <div class="bus-layout">
                <div class="driver-seat">Şoför</div>
                
                <?php $seat_counter = 1; ?>
                <?php for ($r = 1; $r <= $rows; $r++): ?>
                    <div class="seat-row">
                        <?php for ($c = 1; $c <= 2; $c++): ?>
                            <?php if ($seat_counter <= $capacity): ?>
                                <?php 
                                $is_booked = in_array($seat_counter, $booked_seat_numbers);
                                $status_class = $is_booked ? 'booked' : 'available';
                                ?>
                                <label class="seat <?= $status_class ?>">
                                    <input type="checkbox" name="seats[]" value="<?= $seat_counter ?>" <?= $is_booked ? 'disabled' : '' ?>>
                                    <span><?= $seat_counter++ ?></span>
                                </label>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <div class="aisle"></div> <?php for ($c = 3; $c <= 4; $c++): ?>
                            <?php if ($seat_counter <= $capacity): ?>
                                <?php 
                                $is_booked = in_array($seat_counter, $booked_seat_numbers);
                                $status_class = $is_booked ? 'booked' : 'available';
                                ?>
                                <label class="seat <?= $status_class ?>">
                                    <input type="checkbox" name="seats[]" value="<?= $seat_counter ?>" <?= $is_booked ? 'disabled' : '' ?>>
                                    <span><?= $seat_counter++ ?></span>
                                </label>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <?php endfor; ?>
                
                </div>
        </div>

        <?php if (isset($errors['seats'])): ?>
             <div class="error-message"><?= $errors['seats'] ?></div>
        <?php endif; ?>

        <div class="checkout-actions">
             <div class="legend">
                 <span class="available">Boş</span>
                 <span class="booked">Dolu</span>
                 <span class="selected">Seçili</span>
             </div>
             <button type="submit" class="btn btn-proceed">Seçilen Koltuklarla Devam Et</button>
        </div>
    </form>
</main>
<?php view('footer') ?>