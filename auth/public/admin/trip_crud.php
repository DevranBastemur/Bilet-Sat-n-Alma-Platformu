<?php

require __DIR__ . '/../../src/bootstrap.php';

require_role('firma_admin');

$user_id = $_SESSION['user_id'];
$company_name = "Tüm Firmalar"; 
$trips = [];

if (is_company_admin()) {
    $company = get_user_company_info($user_id); 
    if (!$company || empty($company['company_id'])) {
        flash("Sefer yönetimi için hesabınıza atanmış bir firma bulunamadı. Lütfen Süper Admin ile iletişime geçin.", 'flash_error');
        redirect_to(BASE_URL . '/admin/index.php');
    }
    $company_name = $company['name'];
    $trips = get_trips_for_management($company['company_id']);
} elseif (is_admin()) {
    
    $trips = get_trips_for_management(null); 
}
?>

<?php view('header', ['title' => 'Sefer Yönetimi']) ?>
<main class="admin-main">
    <div class="admin-header">
        <h1>Sefer Yönetimi - <?= htmlspecialchars($company_name) ?></h1> 
        <?php if (is_company_admin()):  ?>
            <a href="trip_form.php" class="btn btn-primary">➕ Yeni Sefer Tanımla</a>
        <?php endif; ?>
    </div>
    
    <?php flash() ?> 
    
    <?php if (empty($trips)): ?>
        <div class="alert-info">
            <?= is_admin() ? 'Sistemde' : "Firmanız **" . htmlspecialchars($company_name) . "** adına" ?> henüz kayıtlı sefer bulunmamaktadır.
        </div>
    <?php else: ?>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kalkış Şehir</th>
                    <th>Varış Şehir</th>
                    <?php if (is_admin()): ?>
                        <th>Firma</th>
                    <?php endif; ?>
                    <th>Kalkış Zamanı</th>
                    <th>Fiyat</th>
                    <th>Kapasite</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $trip): ?>
                <tr>
                    <td><?= htmlspecialchars($trip['departure_city']) ?></td>
                    <td><?= htmlspecialchars($trip['arrival_city']) ?></td>
                    <?php if (is_admin()): ?>
                        <td><?= htmlspecialchars($trip['company_name']) ?></td>
                    <?php endif; ?>
                    <td><?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></td>
                    <td><?= number_format($trip['price'], 2, ',', '.') ?> TL</td>
                    <td><?= htmlspecialchars($trip['capacity']) ?></td>
                    <td class="action-buttons">
                        <?php  ?>
                        <?php if (is_company_admin()): ?>
                            <a href="trip_seat_report.php?trip_id=<?= $trip['id'] ?>" class="btn-sm btn-info">Koltuk Raporu</a>
                            <a href="trip_form.php?id=<?= $trip['id'] ?>" class="btn-sm btn-edit">Düzenle</a>
                            <form action="trip_form.php" method="post" onsubmit="return confirm('Bu seferi silmek istediğinizden emin misiniz? Satılan biletler de iptal olur!');" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $trip['id'] ?>">
                                <button type="submit" class="btn-sm btn-delete">Sil</button>
                            </form>
                        <?php else: ?>
                            <span>-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
    
    <a href="index.php" class="back-link">← Yönetim Paneli Ana Sayfa</a>
</main>
<?php view('footer') ?>
