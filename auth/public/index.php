<?php

require __DIR__ . '/../src/bootstrap.php';



$user_logged_in = is_user_logged_in();
$user_name      = current_user();


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


view('header', [
    'title'       => 'Anında Bilet - Hemen Biletini Bul',
    'page_layout' => 'default', 
]);
?>

<div class="homepage-main">
  <header class="main-header">
    <div class="logo-area">
      <img src="img/Aninda_Bilet.png" alt="Anında Bilet Logo" class="app-logo">
      <h2 class="slogan">Nereye Gidersen Git Biz Seninleyiz</h2>
    </div>

    <nav class="auth-nav">
    <?php if ($user_logged_in): ?>
        <span class="welcome-message">Hoş Geldiniz, <?= htmlspecialchars($user_name) ?></span>
        
        <?php 
      
        if (has_minimum_role('firma_admin')): 
        ?>
            <a href="admin/index.php" class="nav-button">Yönetim Paneli</a>
        <?php endif; ?>
        <a href="my_tickets.php" class="nav-button">Biletlerim</a>
        <a href="logout.php" class="nav-button logout">Çıkış Yap</a>
    <?php else: ?>
        <a href="#login"   class="nav-button login"    data-open="login-modal">Giriş Yap</a>
        <a href="#register" class="nav-button register" data-open="register-modal">Kayıt Ol</a>
    <?php endif; ?>
</nav>
  </header>

  <section class="search-section">
    <div class="search-box">
      <h1>Hemen Otobüs Biletinizi Arayın</h1>
      <p class="search-slogan">Güvenilir ve Hızlı Biletleme Deneyimi</p>

      <form action="trips.php" method="get" class="main-search-form">
        <div class="form-row">
          <div class="input-group">
            <label for="departure">Kalkış Yeri:</label>
            <select name="departure" id="departure" required>
              <option value="">Seçiniz</option>
              <?php foreach ($cities as $city): ?>
                <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="input-group">
            <label for="arrival">Varış Yeri:</label>
            <select name="arrival" id="arrival" required>
              <option value="">Seçiniz</option>
              <?php foreach ($cities as $city): ?>
                <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="input-group date-input">
            <label for="date">Tarih:</label>
            <input type="date" name="date" id="date" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>

        <button type="submit" class="search-btn">SEFER ARA</button>
      </form>
    </div>
  </section>

  <section class="feature-section">
    <h2>Neden Anında Bilet?</h2>
    <div class="feature-grid">
      <div class="feature-card">✈️ Hızlı Biletleme</div>
      <div class="feature-card">🔒 Güvenli Ödeme</div>
      <div class="feature-card">📞 7/24 Destek</div>
    </div>
  </section>
</div>

<div id="login-modal" class="modal-overlay" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
    <button class="modal-close" type="button" data-close aria-label="Kapat">&times;</button>
    <div class="auth-card">
      <form action="login.php" method="post" class="form-card" novalidate>
        <h1 id="loginTitle">Giriş Yap</h1>

        <div>
          <label for="m_username">Kullanıcı Adı</label>
          <input id="m_username" name="username" type="text" autocomplete="username">
        </div>

        <div>
          <label for="m_password">Şifre</label>
          <input id="m_password" name="password" type="password" autocomplete="current-password">
        </div>

        <button type="submit" class="btn">Giriş Yap</button>
        <p class="link">Hesabın yok mu? <a href="#" data-open="register-modal">Kayıt Ol</a></p>
      </form>
    </div>
  </div>
</div>

<div id="register-modal" class="modal-overlay" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="registerTitle">
    <button class="modal-close" type="button" data-close aria-label="Kapat">&times;</button>
    <div class="auth-card">
      <form action="register.php" method="post" class="form-card" novalidate>
        <h1 id="registerTitle">Kayıt Ol</h1>

        <div>
          <label for="r_username">Kullanıcı Adı</label>
          <input id="r_username" name="username" type="text" autocomplete="username">
        </div>

        <div>
          <label for="r_email">E-posta</label>
          <input id="r_email" name="email" type="email" autocomplete="email">
        </div>

        <div>
          <label for="r_password">Şifre</label>
          <input id="r_password" name="password" type="password" autocomplete="new-password">
        </div>

        <div>
          <label for="r_password2">Şifre (Tekrar)</label>
          <input id="r_password2" name="password2" type="password" autocomplete="new-password">
        </div>

        <div class="full-width">
          <label><input type="checkbox" name="agree" required> Şartları kabul ediyorum</label>
        </div>

        <button type="submit" class="btn btn-accent">Kayıt Ol</button>
        <p class="link">Zaten üye misin? <a href="#" data-open="login-modal">Giriş Yap</a></p>
      </form>
    </div>
  </div>
</div>
<script>
(function () {
  function byId(id) { return document.getElementById(id); }
  function openModal(id) {
    document.body.classList.add('modal-lock');
    const ov = byId(id);
    ov.classList.add('is-open');
    const first = ov.querySelector('input,select,textarea,button');
    if (first) setTimeout(() => first.focus(), 50);
    const h = id.replace('-modal', '');
    if (location.hash !== ('#' + h)) history.replaceState(null, '', '#' + h);
  }
  function closeAll() {
    document.body.classList.remove('modal-lock');
    document.querySelectorAll('.modal-overlay.is-open').forEach(m => m.classList.remove('is-open'));
    if (location.hash === '#login' || location.hash === '#register') {
      history.replaceState(null, '', location.pathname + location.search);
    }
  }

  document.querySelectorAll('[data-open]').forEach(el => {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      const id = this.getAttribute('data-open');
      closeAll();
      openModal(id);
    });
  });

  document.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', closeAll));
  document.querySelectorAll('.modal-overlay').forEach(ov => {
    ov.addEventListener('click', e => { if (e.target === ov) closeAll(); });
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });

  if (location.hash === '#login') openModal('login-modal');
  if (location.hash === '#register') openModal('register-modal');
})();
</script>

<?php view('footer'); ?>