<?php

require __DIR__ . '/../../src/bootstrap.php';

if (!is_admin() && !is_company_admin()) {
    redirect_to(BASE_URL . '/login.php');
}


if (is_post_request()) {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);

    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $discount = filter_input(INPUT_POST, 'discount', FILTER_VALIDATE_FLOAT);
    $usage_limit = filter_input(INPUT_POST, 'usage_limit', FILTER_VALIDATE_INT);
    $expire_date = filter_input(INPUT_POST, 'expire_date', FILTER_SANITIZE_STRING);
    
    $company_id = is_company_admin() ? get_user_company_id($_SESSION['user_id']) : null;
    
    if (is_admin() && empty($company_id)) {
        $selected_company_id = filter_input(INPUT_POST, 'company_id', FILTER_SANITIZE_STRING);
        if ($selected_company_id !== 'global') {
            $company_id = $selected_company_id;
        }
    }


    $errors = [];
    if (empty($code)) $errors['code'] = 'Kupon kodu gereklidir.';
    if (empty($discount) || $discount <= 0 || $discount > 1) $errors['discount'] = 'İndirim oranı 0 ile 1 arasında olmalıdır (Örn: 0.15 = %15).';
    if (empty($usage_limit) || $usage_limit <= 0) $errors['usage_limit'] = 'Kullanım limiti 1 veya daha fazla olmalıdır.';
    if (empty($expire_date)) $errors['expire_date'] = 'Son kullanma tarihi gereklidir.';


    if ($action === 'delete' && $id) {
        if (delete_coupon($id)) {
            flash('Kupon başarıyla silindi.', 'Kupon ' . $id . ' silindi.', FLASH_SUCCESS);
        } else {
            flash('Kupon silinemedi.', 'Kupon silinirken bir hata oluştu.', FLASH_ERROR);
        }
    } 
    elseif ($action === 'create' || $action === 'update') {
        if (empty($errors)) {
            $success = false;
            if ($action === 'create') {
                $success = create_coupon($code, $discount, $usage_limit, $expire_date, $company_id);
                $msg = 'Yeni kupon başarıyla oluşturuldu.';
            } elseif ($action === 'update' && $id) {
                $success = update_coupon($id, $code, $discount, $usage_limit, $expire_date, $company_id);
                $msg = 'Kupon başarıyla güncellendi.';
            }

            if ($success) {
                flash($msg, $msg, FLASH_SUCCESS);
            } else {
                flash('İşlem Başarısız', 'Veritabanı işlemi sırasında bir hata oluştu.', FLASH_ERROR);
            }
        } else {
             redirect_with('coupon_crud.php?action=edit&id=' . ($id ?? ''), [
                'errors' => $errors,
                'coupon' => ['id' => $id, 'code' => $code, 'discount' => $discount, 'usage_limit' => $usage_limit, 'expire_date' => $expire_date, 'company_id' => $company_id]
            ]);
        }
    }
    
    redirect_to('admin/coupon_crud.php');
}



$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);

$company_id_filter = is_company_admin() ? get_user_company_id($_SESSION['user_id']) : null;
$coupons = get_all_coupons($company_id_filter);


if ($action === 'list'): 
?>

<?php view('header', ['title' => 'Kupon Yönetimi']) ?>
<main class="admin-main">
    <?php view('admin/inc/admin_sidebar') ?>
    <div class="admin-content">
        <h1>Kupon Yönetimi</h1>
        <?php flash() ?>
        
        <a href="coupon_crud.php?action=create" class="btn btn-primary">Yeni Kupon Ekle</a>

        <?php if (empty($coupons)): ?>
            <p class="alert-info">Henüz tanımlanmış kupon bulunmamaktadır.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kod</th>
                        <th>İndirim Oranı</th>
                        <th>Kullanım Limiti</th>
                        <th>Son Kullanma Tarihi</th>
                        <th>Firma</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td><?= htmlspecialchars($coupon['code']) ?></td>
                            <td>%<?= round($coupon['discount'] * 100) ?></td>
                            <td><?= htmlspecialchars($coupon['usage_limit']) ?></td>
                            <td><?= htmlspecialchars($coupon['expire_date']) ?></td>
                            <td class="<?= $coupon['company_name'] ? '' : 'text-primary' ?>">
                                <?= $coupon['company_name'] ? htmlspecialchars($coupon['company_name']) : 'GLOBAL' ?>
                            </td>
                            <td>
                                <a href="coupon_crud.php?action=edit&id=<?= $coupon['id'] ?>" class="btn-sm btn-edit">Düzenle</a>
                                <form method="POST" action="coupon_crud.php" style="display:inline;" onsubmit="return confirm('Bu kuponu silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $coupon['id'] ?>">
                                    <button type="submit" class="btn-sm btn-delete">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>
<?php view('footer') ?>


<?php elseif ($action === 'create' || $action === 'edit'): 
    
    $coupon = ['id' => '', 'code' => '', 'discount' => '', 'usage_limit' => '', 'expire_date' => '', 'company_id' => ''];
    $errors = [];
    $is_edit = ($action === 'edit' && $id);

    if ($is_edit) {
        $coupon_data = get_coupon_by_id($id);
        if ($coupon_data) {
            $coupon = array_merge($coupon, $coupon_data);
        } else {
            flash('Kupon bulunamadı.', 'Geçersiz Kupon ID.', FLASH_ERROR);
            redirect_to('admin/coupon_crud.php');
        }
        $page_title = 'Kupon Düzenle';
    } else {
        $page_title = 'Yeni Kupon Ekle';
    }

    list($temp_errors, $temp_coupon) = session_flash('errors', 'coupon');
    if (!empty($temp_errors)) $errors = $temp_errors;
    if (!empty($temp_coupon)) $coupon = array_merge($coupon, $temp_coupon);

    $companies = is_admin() ? get_all_companies() : [];

?>

<?php view('header', ['title' => $page_title]) ?>
<main class="admin-main">
    <?php view('admin/inc/admin_sidebar') ?>
    <div class="admin-content">
        <h1><?= $page_title ?></h1>
        <?php flash() ?>

        <form action="coupon_crud.php" method="POST" class="crud-form">
            <input type="hidden" name="action" value="<?= $is_edit ? 'update' : 'create' ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($coupon['id']) ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="code">Kupon Kodu:</label>
                <input type="text" id="code" name="code" value="<?= htmlspecialchars($coupon['code']) ?>" class="<?= error_class($errors, 'code') ?>" required>
                <small><?= $errors['code'] ?? '' ?></small>
            </div>

            <div class="form-group">
                <label for="discount">İndirim Oranı (0.01 - 1.00):</label>
                <input type="number" step="0.01" min="0.01" max="1.00" id="discount" name="discount" value="<?= htmlspecialchars($coupon['discount']) ?>" class="<?= error_class($errors, 'discount') ?>" required>
                <small><?= $errors['discount'] ?? 'Örn: 0.10 (%10 indirim)' ?></small>
            </div>

            <div class="form-group">
                <label for="usage_limit">Kullanım Limiti (Toplam Kaç Kez Kullanılabilir):</label>
                <input type="number" min="1" id="usage_limit" name="usage_limit" value="<?= htmlspecialchars($coupon['usage_limit']) ?>" class="<?= error_class($errors, 'usage_limit') ?>" required>
                <small><?= $errors['usage_limit'] ?? '' ?></small>
            </div>
            
            <div class="form-group">
                <label for="expire_date">Son Kullanma Tarihi:</label>
                <input type="date" id="expire_date" name="expire_date" value="<?= htmlspecialchars(date('Y-m-d', strtotime($coupon['expire_date'] ?? ''))) ?>" class="<?= error_class($errors, 'expire_date') ?>" required>
                <small><?= $errors['expire_date'] ?? '' ?></small>
            </div>

            <?php if (is_admin()):  ?>
                <div class="form-group">
                    <label for="company_id">Kuponun Geçerli Olduğu Firma (Opsiyonel):</label>
                    <select name="company_id" id="company_id">
                        <option value="global" <?= $coupon['company_id'] === null ? 'selected' : '' ?>>GLOBAL (Tüm Firmalar)</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= $company['id'] ?>" <?= $coupon['company_id'] === $company['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($company['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Seçim yapılmazsa kupon tüm firmalarda geçerli olur.</small>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary"><?= $is_edit ? 'Kuponu Güncelle' : 'Kuponu Ekle' ?></button>
            <a href="coupon_crud.php" class="btn btn-secondary">İptal</a>
        </form>
    </div>
</main>
<?php view('footer') ?>

<?php endif; ?>