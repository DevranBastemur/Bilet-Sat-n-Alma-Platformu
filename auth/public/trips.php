<?php

require __DIR__ . '/../src/bootstrap.php';

$departure = $_GET['departure'] ?? '';
$arrival = $_GET['arrival'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

$trips = []; 

if ($departure && $arrival && $date) {
    $trips = search_trips($departure, $arrival, $date); 

} else {
    flash('Lütfen kalkış, varış ve tarih seçiniz.', 'flash_error');
    redirect_to('index.php'); 
}
?>

<?php view('header', ['title' => "{$departure} - {$arrival} Seferleri"]) ?>
<main class="trip-listing-main">
    <div class="listing-header">
        <h1><?= $departure ?> &rarr; <?= $arrival ?> Seferleri</h1>
        <p class="date-info"><?= date('d.m.Y, l', strtotime($date)) ?> Tarihli Seferler</p>
        <a href="index.php" class="back-to-search-btn">Yeni Arama Yap</a>
        <?php flash() ?>
    </div>

    <?php if (empty($trips)): ?>
        <div class="no-results-card">
            <p>😔 Üzgünüz, seçtiğiniz kriterlere uygun sefer bulunamadı.</p>
            <p>Lütfen tarih veya güzergah değişikliği yaparak tekrar deneyiniz.</p>
        </div>
    <?php else: ?>
        
        <div class="trips-list">
            <?php foreach ($trips as $trip): ?>
                <div class="trip-item-card">
                    
                    <div class="trip-info">
                        <div class="company-logo">
                            <span class="company-name"><?= htmlspecialchars($trip['company_name']) ?></span>
                        </div>
                        
                        <div class="route-details">
                            <div class="time-city departure">
                                <span class="time"><?= substr($trip['departure_time'], 11, 5) ?></span> 
                                <span class="city"><?= htmlspecialchars($trip['departure_city']) ?></span>
                            </div>
                            
                            <div class="duration-line">
                                <span>&rarr;</span> 
                            </div>
                            
                            <div class="time-city arrival">
                                <span class="time"><?= substr($trip['arrival_time'], 11, 5) ?></span> 
                                <span class="city"><?= htmlspecialchars($trip['arrival_city']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="trip-actions">
                        <div class="price-area">
                            <span class="price-label">Fiyat:</span>
                            <span class="price-value"><?= number_format($trip['price'], 2, ',', '.') ?> TL</span>
                        </div>
                        <a href="buy_ticket.php?trip_id=<?= $trip['id'] ?>" class="buy-ticket-btn">Koltuk Seç/Bilet Al</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php view('footer') ?> 