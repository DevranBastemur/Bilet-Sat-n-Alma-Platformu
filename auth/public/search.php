<?php

require __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth.php'; 

$user_name = current_user();


$cities = [
    'Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Amasya', 'Ankara', 'Antalya', 'Artvin', 
    'Aydın', 'Balıkesir', 'Bilecik', 'Bingöl', 'Bitlis', 'Bolu', 'Burdur', 'Bursa', 
    'Çanakkale', 'Çankırı', 'Çorum', 'Denizli', 'Diyarbakır', 'Düzce', 'Edirne', 'Elazığ', 
    'Erzincan', 'Erzurum', 'Eskişehir', 'Gaziantep', 'Giresun', 'Gümüşhane', 'Hakkari', 
    'Hatay', 'Isparta', 'Mersin', 'İstanbul', 'İzmir', 'Kars', 'Kastamonu', 'Kayseri', 
    'Kırklareli', 'Kırşehir', 'Kocaeli', 'Konya', 'Kütahya', 'Malatya', 'Manisa', 
    'Kahramanmaraş', 'Mardin', 'Muğla', 'Muş', 'Nevşehir', 'Niğde', 'Ordu', 'Rize', 
    'Sakarya', 'Samsun', 'Siirt', 'Sinop', 'Sivas', 'Tekirdağ', 'Tokat', 'Trabzon', 
    'Tunceli', 'Şanlıurfa', 'Uşak', 'Van', 'Yozgat', 'Zonguldak', 'Aksaray', 'Bayburt', 
    'Karaman', 'Kırıkkale', 'Batman', 'Şırnak', 'Bartın', 'Ardahan', 'Iğdır', 'Yalova', 
    'Karabük', 'Kilis', 'Osmaniye'
];
?>

<?php view('header', ['title' => 'Bilet Arama']) ?>
<main>
    <div class="header-section">
        <h1>Otobüs Bileti Arama</h1>
        <?php if ($user_name): ?>
            <p>Hoş Geldiniz, <?= htmlspecialchars($user_name) ?>! <a href="logout.php">Çıkış Yap</a></p>
        <?php else: ?>
            <p><a href="login.php">Giriş Yap</a> | <a href="register.php">Kayıt Ol</a></p>
        <?php endif; ?>
    </div>

    <form action="trips.php" method="get" class="search-form">
        <div class="form-row">
            
            <div>
                <label for="departure">Kalkış Yeri:</label>
                <select name="departure" id="departure" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="arrival">Varış Yeri:</label>
                <select name="arrival" id="arrival" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="date">Tarih:</label>
                <input type="date" name="date" id="date" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <button type="submit">Sefer Ara</button>
    </form>
</main>
<?php view('footer') ?>