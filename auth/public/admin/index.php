<?php

require __DIR__ . '/../../src/bootstrap.php';


require_role('firma_admin'); 

$user_role = current_user_role();
?>

<?php view('header', ['title' => 'Yönetim Paneli']) ?>
<main class="admin-main">
    <h1>Yönetim Paneli - Hoş Geldiniz</h1>
    <p>Yetkiniz: <strong><?= htmlspecialchars($user_role) ?></strong></p>
    
    <nav class="admin-nav">
        
        <?php 
        if (has_minimum_role('admin')): 
        ?>
            <div class="admin-group">
                <h3>Süper Yönetim</h3>
                <h4>Kulanıcı yönetimi</h4>
                <a href="user_crud.php" class="nav-link">Kullanıcı Yönetimi</a>
                <h4>Firma Yöneticileri</h4>
                <a href="company_crud.php" class="nav-link">Firma Yöneticileri CRUD</a>
            </div>
        <?php endif; ?>

        <?php 
        if (has_minimum_role('firma_admin')): 
        ?>
            <div class="admin-group">
                <h3>     :]    Firma ve Sefer Yönetimi</h3>
                <h4>Sefer Yönetimi</h4>
                <a href="trip_crud.php" class="nav-link">Sefer Yönetimi (CRUD)</a>
                <h4>Kupon Yönetimi</h4>
                <a href="coupon_crud.php" class="nav-link">Kupon Yönetimi</a>
            </div>
        <?php endif; ?>
        
    </nav>

    <p style="margin-top: 30px;"><a href="../index.php">← Ana Sayfaya Dön</a></p>
</main>
<?php view('footer') ?>