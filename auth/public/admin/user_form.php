<?php

require __DIR__ . '/../../src/bootstrap.php';

require_role('admin'); 

$errors = [];
$user = [
    'id' => '',
    'full_name' => '',
    'email' => '',
    'password' => '',
    'role' => 'kullanici',
    'company_id' => null,
];
$is_edit = false;

$companies = get_all_companies();
$available_roles = ['kullanici', 'firma_admin', 'admin'];

if (is_post_request()) {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($action === 'delete' && $id) {
        if ($id === $_SESSION['user_id']) {
            flash('Kendi hesabınızı silemezsiniz.', 'Güvenlik hatası.', FLASH_ERROR);
            redirect_to('user_crud.php');
        }

        if (delete_user($id)) {
            flash('Kullanıcı başarıyla silindi.', FLASH_SUCCESS);
        } else {
            flash('Kullanıcı silinirken bir hata oluştu.', FLASH_ERROR);
        }
        redirect_to('user_crud.php');
    }

    if ($action === 'update' || $action === 'create') {
        $fields = [
            'role' => 'string | required',
            'company_id' => 'string'
        ];
        
        if ($action === 'create') {
            $fields['full_name'] = 'string | required | min:3';
            $fields['email'] = 'email | required | unique:User,email';
            $fields['password'] = 'string | required | min:8';
        }

        [$inputs, $errors] = filter($_POST, $fields);
        
        $role = $inputs['role'];
        $company_id = $inputs['company_id'] ?? null;

        if ($role === 'firma_admin' && (empty($company_id) || $company_id === 'global')) {
            $errors['company_id'] = 'Firma Admini için bir firma seçimi zorunludur.';
        }

        if ($role !== 'firma_admin') {
            $company_id = null;
        }

        if (empty($errors)) {
            $success = false;
            if ($action === 'update' && $id) {
                $success = update_user_role_and_company($id, $role, $company_id);
                $message = 'Kullanıcı rolü ve ataması başarıyla güncellendi.';
            } elseif ($action === 'create') {
                $success = create_user($inputs['full_name'], $inputs['email'], $inputs['password'], $role, $company_id);
                $message = 'Yeni yönetici başarıyla oluşturuldu.';
            }

            if ($success) {
                flash($message, FLASH_SUCCESS);
                redirect_to('user_crud.php');
            } else {
                flash('İşlem sırasında bir veritabanı hatası oluştu.', FLASH_ERROR);
            }
        }
        
        $user = array_merge($user, $inputs);
        $is_edit = ($action === 'update');
    }
}
else {
    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);

    if ($action === 'edit' && $id) {
        $user_data = get_user_by_id($id);
        if (!$user_data) {
            flash('Kullanıcı bulunamadı.', FLASH_ERROR);
            redirect_to('user_crud.php');
        }
        $user = array_merge($user, $user_data);
        $is_edit = true;
    } elseif ($action === 'create') {
        $is_edit = false;
    } else {
        redirect_to('user_crud.php');
    }
}
?>

<?php view('header', ['title' => $is_edit ? 'Kullanıcı Düzenle' : 'Yeni Yönetici Oluştur']) ?>
<main class="admin-main form-page">
    <h1><?= $is_edit ? 'Kullanıcı Düzenle' : 'Yeni Yönetici Oluştur' ?></h1>
    
    <?php if ($is_edit): ?>
        <p>Kullanıcı: <strong><?= htmlspecialchars($user['full_name']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)</p>
    <?php endif; ?>

    <?php flash() ?>

    <form action="user_form.php" method="post" class="admin-form">
        <input type="hidden" name="action" value="<?= $is_edit ? 'update' : 'create' ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
        <?php endif; ?>

        <?php if (!$is_edit):  ?>
            <div class="form-group">
                <label for="full_name">Ad Soyad:</label>
                <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="<?= error_class($errors, 'full_name') ?>" required>
                <small class="error"><?= $errors['full_name'] ?? '' ?></small>
            </div>
            <div class="form-group">
                <label for="email">E-posta Adresi:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" class="<?= error_class($errors, 'email') ?>" required>
                <small class="error"><?= $errors['email'] ?? '' ?></small>
            </div>
            <div class="form-group">
                <label for="password">Şifre:</label>
                <input type="password" name="password" id="password" class="<?= error_class($errors, 'password') ?>" required>
                <small class="error"><?= $errors['password'] ?? 'En az 8 karakter olmalıdır.' ?></small>
            </div>
            <hr>
        <?php endif; ?>

        <div class="form-group">
            <label for="role">Rol Ataması:</label>
            <select name="role" id="role" required>
                <?php foreach ($available_roles as $role_option): ?>
                    <option value="<?= $role_option ?>" <?= $user['role'] === $role_option ? 'selected' : '' ?>>
                        <?= strtoupper($role_option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>Rol değiştirmek, kullanıcının erişim yetkilerini anında etkiler.</small>
        </div>

        <div class="form-group">
            <label for="company_id">Atanacak Firma (Sadece Firma Admini İçin):</label>
            <select name="company_id" id="company_id" class="<?= error_class($errors, 'company_id') ?>">
                <option value="global" <?= empty($user['company_id']) ? 'selected' : '' ?>>
                    Global / Firma Yok
                </option>
                <?php foreach ($companies as $company): ?>
                    <option value="<?= $company['id'] ?>" <?= ($user['company_id'] ?? null) === $company['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($company['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="error"><?= $errors['company_id'] ?? 'Firma Admini rolü verilirse, buradan firması seçilmelidir.' ?></small>
        </div>

        <button type="submit" class="btn btn-save"><?= $is_edit ? 'Kullanıcıyı Güncelle' : 'Yöneticiyi Oluştur' ?></button>
        <a href="user_crud.php" class="btn btn-secondary">İptal / Geri Dön</a>
    </form>
</main>
<?php view('footer') ?>
