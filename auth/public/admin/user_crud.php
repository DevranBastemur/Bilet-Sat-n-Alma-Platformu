<?php

require __DIR__ . '/../../src/bootstrap.php';

require_role('admin'); 

$users = get_all_users_with_company(); 
?>

<?php view('header', ['title' => 'Kullanıcı ve Rol Yönetimi']) ?>
<main class="admin-main">      <div class="admin-header"></div>
    <div class="admin-header">
        <h1>Kullanıcı Yönetimi</h1>
        <h1>     .       </h1>
        <a href="user_form.php?action=create" class="btn btn-primary">➕ Yeni Yönetici Oluştur</a>
    
    <h1>     .       </h1>
    </div>
    
    <?php flash() ?> 
    
    <?php if (empty($users)): ?>
        <div class="alert-info">
            Sistemde kayıtlı kullanıcı bulunmamaktadır.
        </div>
    <?php else: ?>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kullanıcı Adı</th>
                    <th>E-posta</th>
                    <th>Rol</th>
                    <th>Atanan Firma</th>
                    <th>Bakiye</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><span class="role-badge role-<?= htmlspecialchars($user['role']) ?>"><?= strtoupper(htmlspecialchars($user['role'])) ?></span></td>
                    <td><?= htmlspecialchars($user['company_name'] ?? 'Yok/Global') ?></td>
                    <td><?= number_format($user['balance'], 2, ',', '.') ?> TL</td>
                    <td class="action-buttons">
                        <a href="user_form.php?action=edit&id=<?= $user['id'] ?>" class="btn-sm btn-edit">Düzenle</a>
                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <form action="user_form.php" method="post" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?');" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn-sm btn-delete">Sil</button>
                            </form>
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