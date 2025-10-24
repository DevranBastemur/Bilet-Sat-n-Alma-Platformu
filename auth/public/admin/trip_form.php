<?php

require __DIR__ . '/../../src/bootstrap.php';

require_role('firma_admin');

$errors = [];
$trip = [
    'id' => '', 
    'departure_city' => '', 
    'arrival_city' => '', 
    'departure_time' => '', 
    'arrival_time' => '', 
    'price' => 0.0, 
    'capacity' => 40 
];
$is_edit = false;


$user_id = $_SESSION['user_id'];
$company = get_user_company_info($user_id); 

if (!$company || empty($company['company_id'])) {
    flash("Sefer yönetimi için hesabınıza atanmış bir firma bulunamadı. Lütfen Süper Admin ile iletişime geçin.", 'flash_error');
    redirect_to('trip_crud.php');
}
$company_id = $company['company_id'];
$company_name = $company['name'];


if (is_get_request()) {
    $id = $_GET['id'] ?? '';
    if ($id) {
        $trip_to_edit = get_trip_by_id($id); 

        if (!$trip_to_edit || $trip_to_edit['company_id'] !== $company_id) {
            flash('Bu seferi düzenleme yetkiniz bulunmamaktadır.', 'flash_error');
            redirect_to('trip_crud.php');
        }
        
        $trip = $trip_to_edit;
        $is_edit = true;
    }
} 
else if (is_post_request()) {
    
    $action = $_POST['action'] ?? 'save';
    $id = $_POST['id'] ?? null;

    if ($action === 'delete' && $id) {
        $trip_to_delete = get_trip_by_id($id);
        if (!$trip_to_delete || $trip_to_delete['company_id'] !== $company_id) {
             flash('Bu seferi silme yetkiniz bulunmamaktadır.', 'flash_error');
             redirect_to('trip_crud.php');
        }
        
        if (delete_trip($id)) {
            flash('Sefer başarıyla silindi!', 'flash_success');
        } else {
            flash('Sefer silinirken bir hata oluştu.', 'flash_error');
        }
        redirect_to('trip_crud.php');
    }
    
    if ($action === 'save') {
        
        $fields = [
            'departure_city' => 'string | required | min_len:2',
            'arrival_city' => 'string | required | min_len:2',
            'departure_datetime' => 'string | required',
            'arrival_datetime' => 'string | required',   
            'price' => 'float | required | min_value:1',
            'capacity' => 'int | required | min_value:10'
        ];
        
        [$inputs, $errors] = filter($_POST, $fields);
        
        $departure_time = $inputs['departure_datetime'] ?? null;
        $arrival_time = $inputs['arrival_datetime'] ?? null;
        $price = (float)($inputs['price'] ?? 0);
        $capacity = (int)($inputs['capacity'] ?? 0);

        if (strtotime($arrival_time) <= strtotime($departure_time)) {
            $errors['arrival_datetime'] = 'Varış zamanı, kalkış zamanından sonra olmalıdır.';
        }
        if (!$id && strtotime($departure_time) < time()) {
            $errors['departure_datetime'] = 'Kalkış zamanı geçmiş bir tarih olamaz.';
        }

        if (empty($errors)) {
            $success = false;

            if ($id) { 
                $success = update_trip($id, $inputs['departure_city'], $inputs['arrival_city'], $departure_time, $arrival_time, $price, $capacity); // `arrival_city` burada `destination_city`'ye denk geliyor
            } else { 
                $success = create_trip($company_id, $inputs['departure_city'], $inputs['arrival_city'], $departure_time, $arrival_time, $price, $capacity); // `arrival_city` burada `destination_city`'ye denk geliyor
            }
            
            if ($success) {
                $message = $id ? 'Sefer başarıyla güncellendi!' : 'Yeni sefer başarıyla eklendi!';
                flash($message, 'flash_success');
                redirect_to('trip_crud.php');
            } else {
                $trip = array_merge($trip, $inputs);
                $is_edit = (bool)$id;
            }
        } else {
            $trip = array_merge($trip, $inputs);
            $is_edit = (bool)$id;
        }
    }
}
?>

<?php view('header', ['title' => $is_edit ? 'Sefer Düzenle' : 'Yeni Sefer Tanımla']) ?>
<main class="admin-main form-page">
    <h1><?= $is_edit ? 'Sefer Düzenle' : 'Yeni Sefer Tanımla' ?></h1>
    <p class="subtitle">Firma: <strong><?= htmlspecialchars($company_name) ?></strong> <a href="trip_crud.php" class="back-link">← Sefer Listesine Dön</a></p>

    <?php flash() ?>

    <form action="trip_form.php<?= $is_edit ? '?id=' . htmlspecialchars($trip['id']) : '' ?>" method="post" class="admin-form">
        <input type="hidden" name="id" value="<?= htmlspecialchars($trip['id']) ?>">
        <input type="hidden" name="action" value="save">

        <h2>1. Güzergah Bilgileri</h2>
        <div class="form-group-double">
            <div>
                <strong>Kalkış Şehri:</strong>
                <input type="text" name="departure_city" id="departure_city" value="<?= htmlspecialchars($trip['departure_city']) ?>" class="<?= error_class($errors, 'departure_city') ?>" required>
                <small class="error"><?= $errors['departure_city'] ?? '' ?></small>
            </div>
            <div>
                <strong>Varış Şehri:</strong>
                <input type="text" name="arrival_city" id="arrival_city" value="<?= htmlspecialchars($trip['arrival_city']) ?>" class="<?= error_class($errors, 'arrival_city') ?>" required>
                <small class="error"><?= $errors['arrival_city'] ?? '' ?></small>
            </div>
        </div>

        <h2>2. Zamanlama</h2>
        <div class="form-group-double">
            <div>
                <strong>Kalkış Tarihi ve Saati:</strong>
                <input type="datetime-local" name="departure_datetime" id="departure_datetime" 
                       value="<?= date('Y-m-d\TH:i', strtotime(empty($trip['departure_time']) ? 'now' : $trip['departure_time'])) ?>" 
                       class="<?= error_class($errors, 'departure_datetime') ?>" required>
                <small class="error"><?= $errors['departure_datetime'] ?? '' ?></small>
            </div>
            <div>
                <strong>Tahmini Varış Tarihi ve Saati:</strong>
                <input type="datetime-local" name="arrival_datetime" id="arrival_datetime" 
                       value="<?= date('Y-m-d\TH:i', strtotime(empty($trip['arrival_time']) ? 'now' : $trip['arrival_time'])) ?>" 
                       class="<?= error_class($errors, 'arrival_datetime') ?>" required>
                <small class="error"><?= $errors['arrival_datetime'] ?? '' ?></small>
            </div>
        </div>
        
        <h2>3. Detaylar</h2>
        <div class="form-group-double">
            <div>
                <strong>Bilet Fiyatı (TL):</strong>
                <input type="number" name="price" id="price" min="1" step="0.01" value="<?= htmlspecialchars($trip['price']) ?>" class="<?= error_class($errors, 'price') ?>" required>
                <small class="error"><?= $errors['price'] ?? '' ?></small>
            </div>
            <div>
                <strong>Otobüs Kapasitesi (Koltuk Sayısı):</strong>
                <input type="number" name="capacity" id="capacity" min="10" value="<?= htmlspecialchars($trip['capacity']) ?>" class="<?= error_class($errors, 'capacity') ?>" required>
                <small class="error"><?= $errors['capacity'] ?? '' ?></small>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 30px;">
             <button type="submit" class="btn btn-save"><?= $is_edit ? 'Seferi Güncelle' : 'Seferi Kaydet' ?></button>
             <a href="trip_crud.php" class="btn btn-secondary">İptal / Geri Dön</a>
        </div>
    </form>
</main>
<?php view('footer') ?>
