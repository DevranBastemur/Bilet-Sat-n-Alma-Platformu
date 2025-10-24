<?php

require __DIR__ . '/../../src/bootstrap.php';

require_role('firma_admin');

$trip_id = $_GET['trip_id'] ?? '';
$trip = null;
$booked_seats_data = []; 
$company_id = get_user_company_id($_SESSION['user_id']);

if (!$trip_id) {
    flash('Koltuk raporu için geçerli bir sefer ID\'si gereklidir.', 'flash_error');
    redirect_to(BASE_URL . '/admin/trip_crud.php');
}

$trip = get_trip_by_id($trip_id);

if (!$trip || $trip['company_id'] !== $company_id) {
    flash('Bu sefere ait koltuk raporunu görüntüleme yetkiniz bulunmamaktadır.', 'flash_error');
    redirect_to(BASE_URL . '/admin/trip_crud.php');
}

$booked_seats_data = get_trip_seat_status_for_admin($trip_id);

$capacity = $trip['capacity'];
$rows = ceil($capacity / 4); 
?>

<?php view('header', ['title' => 'Koltuk Raporu']) ?>
<main class="admin-main">
    <div class="admin-header">
        <h1>Koltuk Raporu: <?= htmlspecialchars($trip['departure_city']) ?> &rarr; <?= htmlspecialchars($trip['arrival_city']) ?></h1>
        <p class="subtitle">
            Firma: <strong><?= get_company_by_id($trip['company_id'])['name'] ?? 'Bilinmiyor' ?></strong> |
            Kalkış: <strong><?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></strong> |
            Kapasite: <strong><?= htmlspecialchars($trip['capacity']) ?></strong>
        </p>
    </div>

    <?php flash() ?>

    <div class="bus-layout-container admin-bus-layout">
        <div class="bus-layout">
            <div class="driver-seat">Şoför</div>

            <?php $seat_counter = 1; ?>
            <?php for ($r = 1; $r <= $rows; $r++): ?>
                <div class="seat-row">
                    <?php for ($c = 1; $c <= 2; $c++): ?>
                        <?php if ($seat_counter <= $capacity): ?>
                            <?php
                            $seat_info = $booked_seats_data[$seat_counter] ?? null;
                            $status_class = $seat_info ? 'booked' : 'available';
                            $tooltip_text = $seat_info ?
                                "Yolcu: " . htmlspecialchars($seat_info['booked_by_user']) .
                                "\nBilet ID: " . htmlspecialchars($seat_info['ticket_id']) .
                                "\nÖdenen: " . number_format($seat_info['ticket_price'], 2, ',', '.') . " TL"
                                : "Boş";
                            ?>
                            <div class="seat <?= $status_class ?>" title="<?= $tooltip_text ?>">
                                <span><?= $seat_counter++ ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <div class="aisle"></div>

                    <?php for ($c = 3; $c <= 4; $c++): ?>
                        <?php if ($seat_counter <= $capacity): ?>
                            <?php
                            $seat_info = $booked_seats_data[$seat_counter] ?? null;
                            $status_class = $seat_info ? 'booked' : 'available';
                            $tooltip_text = $seat_info ?
                                "Yolcu: " . htmlspecialchars($seat_info['booked_by_user']) .
                                "\nBilet ID: " . htmlspecialchars($seat_info['ticket_id']) .
                                "\nÖdenen: " . number_format($seat_info['ticket_price'], 2, ',', '.') . " TL"
                                : "Boş";
                            ?>
                            <div class="seat <?= $status_class ?>" title="<?= $tooltip_text ?>">
                                <span><?= $seat_counter++ ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="checkout-actions" style="margin-top: 20px;">
        <div class="legend">
            <span class="available">Boş</span>
            <span class="booked">Dolu</span>
        </div>
    </div>

    <a href="trip_crud.php" class="back-link" style="margin-top: 20px;">← Sefer Yönetimine Geri Dön</a>
</main>
<?php view('footer') ?>