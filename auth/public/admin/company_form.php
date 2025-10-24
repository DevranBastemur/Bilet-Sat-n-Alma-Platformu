<?php


require __DIR__ . '/../../src/bootstrap.php';

require_role('admin'); 

$errors = [];
$company = [
    'id' => '', 
    'name' => '', 
    'logo_path' => ''
];
$is_edit = false;

if (is_get_request()) {
    $id = $_GET['id'] ?? '';
    if ($id) {
        $company = get_company_by_id($id);
        if (!$company) {
            flash('Firma bulunamadı.', 'flash_error');
            redirect_to('company_crud.php');
        }
        $is_edit = true;
    }
} 

else if (is_post_request()) {
    
    $action = $_POST['action'] ?? 'save';
    $id = $_POST['id'] ?? null;
    if ($action === 'delete') {
        if (delete_company($id)) {
            flash('Firma başarıyla silindi!', 'flash_success');
        } else {
            flash('Firma silinirken bir hata oluştu veya bu firmaya ait seferler var.', 'flash_error');
        }
        redirect_to('company_crud.php');
        exit;
    }


    $fields = [
        'name' => 'string | required | min_len:3'
        
    ];
    
    [$inputs, $errors] = filter($_POST, $fields);
    
    if (empty($errors)) {
    
        $logo_path = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logo_path = sanitize_file_upload($_FILES['logo']); 
        } else if (isset($_POST['current_logo_path'])) {
        
             $logo_path = $_POST['current_logo_path'];
        }

        if ($id) {
           
            if (update_company($id, $inputs['name'], $logo_path)) {
                flash('Firma başarıyla güncellendi!', 'flash_success');
            } else {
                flash('Firma güncellenirken bir hata oluştu.', 'flash_error');
            }
        } else {
          
            if (create_company($inputs['name'], $logo_path)) {
                flash('Yeni firma başarıyla eklendi!', 'flash_success');
            } else {
                flash('Yeni firma eklenirken bir hata oluştu.', 'flash_error');
            }
        }
        redirect_to('company_crud.php');
        exit;
    } else {
    
        $company = array_merge($company, $inputs);
        $is_edit = (bool)$id;
    }
}
?>

<?php view('header', ['title' => $is_edit ? 'Firma Düzenle' : 'Yeni Firma Ekle']) ?>
<main class="admin-main form-page">
    <h1><?= $is_edit ? 'Firma Düzenle' : 'Yeni Firma Ekle' ?></h1>

    <form action="company_form.php" method="post" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="id" value="<?= htmlspecialchars($company['id']) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="current_logo_path" value="<?= htmlspecialchars($company['logo_path']) ?>">

        <div>
            <label for="name">Firma Adı:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($company['name']) ?>" required>
            <small class="error"><?= $errors['name'] ?? '' ?></small>
        </div>

        <div>
            <label for="logo">Logo (Opsiyonel):</label>
            <?php if ($company['logo_path']): ?>
                <p>Mevcut Logo: <img src="<?= BASE_URL . '/img/logos/' . htmlspecialchars($company['logo_path']) ?>" alt="Mevcut Logo" class="company-logo-preview"></p>
            <?php endif; ?>
            <input type="file" name="logo" id="logo" accept="image/*">
        </div>

        <button type="submit" class="btn btn-save"><?= $is_edit ? 'Güncelle' : 'Kaydet' ?></button>
        <a href="company_crud.php" class="btn btn-secondary">İptal / Geri Dön</a>
    </form>
</main>
<?php view('footer') ?>