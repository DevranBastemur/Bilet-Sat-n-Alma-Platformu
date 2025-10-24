<?php

?>

<aside class="admin-sidebar">
    <nav class="sidebar-nav">
        <h2>Yönetim Menüsü</h2>
        <ul>
            <li>
                <a href="<?= BASE_URL ?>/admin/index.php">
                    <i class="fa fa-dashboard"></i> Dashboard
                </a>
            </li>

            <?php if (is_admin()):  ?>
            <li class="menu-group">Süper Admin</li>
            
            <li>
                <a href="<?= BASE_URL ?>/admin/company_crud.php">
                    <i class="fa fa-bus"></i> Firmaları Yönet
                </a>
            </li>
            
            <li>
                <a href="<?= BASE_URL ?>/admin/user_crud.php"> 
                    <i class="fa fa-users"></i> Kullanıcı/Rol Yönetimi 
                </a>
            </li>
            
            <li>
                <a href="<?= BASE_URL ?>/admin/coupon_crud.php">
                    <i class="fa fa-gift"></i> Global Kuponlar
                </a>
            </li>
            
            <?php endif; ?>
            
            <?php if (is_company_admin()):  ?>
            <li class="menu-group">Firma Yönetimi</li>
            
            <li>
                <a href="<?= BASE_URL ?>/admin/trip_crud.php">
                    <i class="fa fa-route"></i> Seferleri Yönet
                </a>
            </li>
            
            <li>
                <a href="<?= BASE_URL ?>/admin/coupon_crud.php">
                    <i class="fa fa-tags"></i> Firma Kuponları
                </a>
            </li>
            
            <?php endif; ?>

            <li class="logout-link">
                 <a href="<?= BASE_URL ?>/logout.php">
                    <i class="fa fa-sign-out"></i> Çıkış Yap
                </a>
            </li>
        </ul>
    </nav>
</aside>