<?php

require __DIR__ . '/../src/bootstrap.php';

if (!is_user_in_role('kullanici') && !is_user_in_role('firma_admin')) {
    flash("Bu sayfaya erişim yetkiniz yok.", 'flash_error');
    redirect_to(BASE_URL . '/index.php');
}

$tickets = [];
$page_title = "Biletlerim";
$intro_text = "Satın almış olduğunuz tüm biletleri burada görebilirsiniz.";
$show_user_name = false; 

if (is_user_in_role('firma_admin')) {
    $company_id = get_user_company_id($_SESSION['user_id']);
    if ($company_id) {
        $tickets = get_tickets_for_reporting($company_id); 
        $company_name = get_company_by_id($company_id)['name'] ?? 'Bilinmiyor';
        $page_title = "Firmanızdan Satın Alınan Biletler";
        $intro_text = "<strong>" . htmlspecialchars($company_name) . "</strong> firmasının seferlerinden satın alınan tüm biletleri burada görebilirsiniz.";
        $show_user_name = true; 
    } else {
        flash("Hesabınıza atanmış bir firma bulunamadığı için biletleri görüntüleyemiyorsunuz.", 'flash_error');
    }
} elseif (is_user_in_role('kullanici')) {
    $user_id = $_SESSION['user_id'];
    $tickets = get_tickets_by_user_id($user_id); 
}

?>

<?php view('header', ['title' => $page_title]) ?>
<main class="trip-listing-main">
    <div class="listing-header">
        <h1><?= htmlspecialchars($page_title) ?></h1>
        <p><?= $intro_text ?></p>
        <?php flash() ?>
    </div>

    <?php if (empty($tickets)): ?>
        <div class="no-results-card">
            <p>😔 Henüz satın alınmış bir biletiniz bulunmamaktadır.</p>
            <?php if (is_user_in_role('kullanici')): ?>
                <a href="index.php" class="back-to-search-btn" style="margin-top: 20px;">Hemen Bilet Ara</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="trips-list">
            <?php foreach ($tickets as $ticket): ?>
                <div class="trip-item-card">
                    <div class="trip-info">
                        <div class="company-logo">
                            <span class="company-name"><?= htmlspecialchars($ticket['company_name']) ?></span>
                        </div>
                        <div class="route-details">
                            <div class="time-city departure">
                                <span class="time"><?= date('d.m.Y H:i', strtotime($ticket['departure_time'])) ?></span>
                                <span class="city"><?= htmlspecialchars($ticket['departure_city']) ?></span>
                            </div>
                            <div class="duration-line"><span>&rarr;</span></div>
                            <div class="time-city arrival">
                                <span class="time">&nbsp;</span>
                                <span class="city"><?= htmlspecialchars($ticket['arrival_city']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="trip-actions">
                        <?php if ($show_user_name):  ?>
                            <div class="price-area">
                                <span class="price-label">Yolcu:</span>
                                <span class="price-value" style="font-size: 1.2em;"><?= htmlspecialchars($ticket['user_name']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="price-area">
                            <span class="price-label">Koltuklar:</span>
                            <span class="price-value" style="font-size: 1.2em;"><?= htmlspecialchars($ticket['seats']) ?></span>
                        </div>
                        <div class="price-area">
                            <span class="price-label">Ödenen Tutar:</span>
                            <span class="price-value"><?= number_format($ticket['total_price'], 2, ',', '.') ?> TL</span>
                        </div>
                        
                        <?php
                        
                        $can_cancel = is_user_in_role('kullanici') && 
                                      $ticket['status'] === 'active' && 
                                      (strtotime($ticket['departure_time']) > time() + 3600);
                        ?>
                        <?php if ($can_cancel): ?>
                            <form action="cancel_ticket.php" method="post" onsubmit="return confirm('Bu bileti iptal etmek istediğinizden emin misiniz? Ücreti bakiyenize iade edilecektir.');" style="margin-top: 10px;">
                                <input type="hidden" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">
                                <button type="submit" class="btn-sm btn-delete">Bileti İptal Et</button>
                            </form>
                        <?php elseif ($ticket['status'] === 'cancelled'): ?>
                            <div class="ticket-status-cancelled">İptal Edildi</div>
                        <?php endif; ?>

                        <?php if ($ticket['status'] === 'active' || $ticket['status'] === 'cancelled'): ?>
                            <a href="download_ticket.php?ticket_id=<?= $ticket['ticket_id'] ?>" class="btn-sm btn-info" style="margin-top: 10px;" target="_blank">PDF İndir</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php view('footer') ?>