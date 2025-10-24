<?php


require __DIR__ . '/../../src/bootstrap.php';

require_role('admin'); 

$companies = get_all_companies();
?>

<?php view('header', ['title' => 'Firma Yönetimi']) ?>
<main class="admin-main">
    <div class="admin-header">
        <h1>Otobüs Firmaları Yönetimi</h1>
        <a href="company_form.php" class="btn btn-primary">➕ Yeni Firma Ekle</a>
    </div>
    
    <?php flash() ?> 
    
    <?php if (empty($companies)): ?>
        <div class="alert-info">
            Henüz sisteme kayıtlı otobüs firması bulunmamaktadır. Hemen bir tane ekleyin!
        </div>
    <?php else: ?>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Logo</th>
                    <th>Firma Adı</th>
                    <th>Kayıt Tarihi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $company): ?>
                <tr>
                    <td><?= htmlspecialchars($company['id']) ?></td>
                    <td>
                        <?php if ($company['logo_path']): ?>
                            <img src="<?= BASE_URL . '/img/logos/' . htmlspecialchars($company['logo_path']) ?>" alt="<?= htmlspecialchars($company['name']) ?> Logo" class="company-logo-small">
                        <?php else: ?>
                            <span>Yok</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($company['name']) ?></td>
                    <td><?= date('d.m.Y', strtotime($company['created_at'])) ?></td>
                    <td class="action-buttons">
                        <a href="company_form.php?id=<?= $company['id'] ?>" class="btn-sm btn-edit">Düzenle</a>
                        <form action="company_form.php" method="post" onsubmit="return confirm('Bu firmayı SİLMEK istediğinizden emin misiniz? Tüm seferleri de silinebilir!');" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $company['id'] ?>">
                            <button type="submit" class="btn-sm btn-delete">Sil</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
    
    <a href="index.php" class="back-link">← Yönetim Paneli Ana Sayfa</a>
</main>
<?php view('footer') ?>