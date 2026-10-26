<?php
session_start();
require_once 'config/db.php';

// Fetch featured homestays
$featured = $conn->query("SELECT * FROM homestay WHERE status = 'aktif' ORDER BY rating DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MalangStay - Homestay di Malang</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>

<nav>
  <a href="malangstay.php" class="logo">
    <img src="assets/MalangStay_logo_cropped.png" alt="MalangStay" class="logo-img">
  </a>
  <div class="search-bar">
    <i data-lucide="search" style="width:14px;height:14px;"></i>
    <input type="text" placeholder="Homestay di Malang">
  </div>
  <ul class="nav-links">
    <li><a href="#unggulan">Unggulan</a></li>
    <li><a href="#kontak">Kontak</a></li>
    <li><a href="admin/daftar-properti.php">Daftar Penginapan</a></li>
    <?php if (isset($_SESSION['user_id'])): ?>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="admin/dashboard.php" class="btn-login">Dashboard</a></li>
      <?php else: ?>
        <li><a href="user/profil.php" class="btn-login">Profil</a></li>
      <?php endif; ?>
      <li><a href="user/logout.php" class="btn-login">Logout</a></li>
    <?php else: ?>
      <li><a href="user/login.php" class="btn-login">Sign in</a></li>
      <li><a href="user/register.php" class="btn-login">Sign Up</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-overlay-img"></div>
  <div class="hero-content">
    <h1>Malang<br><span>Stay.</span></h1>
    <p class="hero-sub">Temukan homestay terbaik di Malang — suasana hangat, fasilitas lengkap, harga terjangkau.</p>
    <div class="hero-actions">
      <a href="#unggulan" class="hero-btn">Lihat Penginapan <i data-lucide="arrow-right" style="display:inline-block;vertical-align:middle;width:15px;height:15px;margin-left:5px;"></i></a>
    </div>
  </div>

  <div class="booking-widget">
    <div class="widget-group">
      <label><i data-lucide="map-pin" style="width:12px;height:12px;"></i> Destinasi</label>
      <select class="widget-select">
        <option>Kota Malang</option>
        <option selected>Batu, Malang</option>
        <option>Pujon, Malang</option>
        <option>Lawang, Malang</option>
        <option>Tumpang, Malang</option>
        <option>Singosari, Malang</option>
        <option>Kepanjen, Malang</option>
        <option>Sendang Biru, Malang</option>
      </select>
    </div>
    <div class="widget-divider"></div>
    <div class="widget-group">
      <label><i data-lucide="calendar" style="width:12px;height:12px;"></i> Check-In</label>
      <input type="date" class="widget-date">
    </div>
    <div class="widget-divider"></div>
    <div class="widget-group">
      <label><i data-lucide="calendar" style="width:12px;height:12px;"></i> Check-Out</label>
      <input type="date" class="widget-date">
    </div>
    <div class="widget-divider"></div>
    <div class="widget-group">
      <label><i data-lucide="users" style="width:12px;height:12px;"></i> Tamu</label>
      <input type="number" min="1" max="20" value="2" class="widget-num">
    </div>
    <button class="btn-search-widget">
      <i data-lucide="search" style="width:14px;height:14px;"></i> Cari
    </button>
  </div>
</section>

<!-- UNGGULAN -->
<section class="unggulan" id="unggulan">
  <div class="section-header">
    <h2>Penginapan <span>Unggulan</span></h2>
    <p>Homestay harian dan villa privat terbaik di Malang dan Batu.</p>
  </div>
  <div class="cards-grid">
    <?php if ($featured && $featured->num_rows > 0): ?>
      <?php while ($h = $featured->fetch_assoc()): ?>
      <div class="card">
        <div class="card-img">
          <img src="uploads/homestay/<?= htmlspecialchars($h['foto'] ?: 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($h['nama']) ?>">
          <div class="card-rating"><i data-lucide="star" style="width:11px;height:11px;fill:#e8b84b;stroke:#e8b84b;"></i> <?= $h['rating'] ?></div>
          <button class="card-fav">♡</button>
        </div>
        <div class="card-body">
          <h3><?= htmlspecialchars($h['nama']) ?></h3>
          <div class="card-meta">
            <span><i data-lucide="map-pin" style="width:11px;height:11px;"></i> <?= htmlspecialchars($h['lokasi']) ?></span>
            <span><i data-lucide="users" style="width:11px;height:11px;"></i> 2–4 orang</span>
          </div>
          <?php if ($h['fasilitas']): ?>
            <span class="badge"><?= htmlspecialchars(explode(',', $h['fasilitas'])[0]) ?></span>
          <?php endif; ?>
          <div class="card-divider"></div>
          <div class="card-price-label">Mulai Dari</div>
          <div class="card-price-row">
            <div class="card-price">Rp <?= number_format($h['harga'], 0, ',', '.') ?> <span>/ Malam</span></div>
            <a href="admin/detail.php?id=<?= $h['id'] ?>" class="card-btn">Pesan</a>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <!-- Fallback static cards jika DB kosong -->
             <div class="card card-empty">
        <div class="card-empty-inner">
          <i data-lucide="home" style="width:28px;height:28px;opacity:0.25;"></i>
          <p>Segera hadir</p>
          <span>Properti baru sedang disiapkan</span>
        </div>
      </div>
      <div class="card card-empty">
        <div class="card-empty-inner">
          <i data-lucide="home" style="width:28px;height:28px;opacity:0.25;"></i>
          <p>Segera hadir</p>
          <span>Properti baru sedang disiapkan</span>
        </div>
      </div>
      <div class="card card-empty">
        <div class="card-empty-inner">
          <i data-lucide="home" style="width:28px;height:28px;opacity:0.25;"></i>
          <p>Segera hadir</p>
          <span>Properti baru sedang disiapkan</span>
        </div>
      </div>
      <div class="card card-empty">
        <div class="card-empty-inner">
          <i data-lucide="plus-circle" style="width:28px;height:28px;opacity:0.25;"></i>
          <p>Daftarkan properti</p>
          <span><a href="admin/daftar-properti.php">Klik di sini</a></span>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="unggulan-cta">
    <a href="admin/daftar-properti.php" class="btn-lihat-semua">
      Lihat Semua Penginapan <i data-lucide="arrow-right" style="display:inline-block;vertical-align:middle;width:14px;height:14px;margin-left:5px;"></i>
    </a>
  </div>
</section>

<!-- SEARCH SECTION -->
<div class="search-section">
  <p class="search-section-label">Belum nemu yang cocok?</p>
  <div class="search-big">
    <input type="text" id="searchInput" placeholder="Cari nama homestay, lokasi, atau fasilitas...">
    <button onclick="window.location.href='admin/daftar-properti.php?q='+document.getElementById('searchInput').value">
      <i data-lucide="search"></i>
    </button>
  </div>
</div>

<!-- KONTAK -->
<section class="contact" id="kontak">
  <div class="section-header">
    <h2>Ada yang bisa<br><span>kami bantu?</span></h2>
    <p>Booking, pertanyaan, atau mau kolaborasi — langsung sapa kami.</p>
  </div>
  <div class="contact-inner">
    <div class="contact-left">
      <h3>Info Kontak</h3>
      <div class="contact-item">
        <div class="contact-icon"><i data-lucide="mail"></i></div>
        <div class="contact-item-text">
          <strong>Email</strong>
          <p>info@malangstay.id<br>home@malang.id</p>
        </div>
      </div>
      <div class="contact-item">
        <div class="contact-icon"><i data-lucide="phone"></i></div>
        <div class="contact-item-text">
          <strong>Telepon / WhatsApp</strong>
          <p>0822 4597 5089<br>0676 7676 6767</p>
        </div>
      </div>
      <div class="contact-item">
        <div class="contact-icon"><i data-lucide="map-pin"></i></div>
        <div class="contact-item-text">
          <strong>Kantor</strong>
          <p>Jl. Tanimbar No. 22, Klojen<br>Kota Malang, Jawa Timur</p>
        </div>
      </div>
      <div class="jam-ops">
        <strong>Jam Buka</strong>
        <div class="jam-row"><span class="hari">Senin – Jumat</span><span class="waktu">08.00 – 17.00</span></div>
        <div class="jam-row"><span class="hari">Sabtu</span><span class="waktu">08.00 – 16.00</span></div>
        <div class="jam-row"><span class="hari">Minggu</span><span class="waktu">09.00 – 14.00</span></div>
      </div>
    </div>
    <div class="contact-right">
      <h3>Tulis Pesan</h3>
      <div id="form-alert" class="form-alert" style="display:none;"></div>
      <div class="form-row">
        <div class="form-group">
          <label>Nama</label>
          <input type="text" id="contactNama" placeholder="Nama kamu">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" id="contactEmail" placeholder="email@kamu.com">
        </div>
      </div>
      <div class="form-group">
        <label>Subjek</label>
        <input type="text" id="contactSubjek" placeholder="Perihal pesan">
      </div>
      <div class="form-group">
        <label>Pesan</label>
        <textarea id="contactPesan" placeholder="Ceritain keperluanmu di sini..."></textarea>
      </div>
      <button class="btn-submit" id="btnKirim">
        Kirim Pesan <i data-lucide="send" style="display:inline-block;vertical-align:middle;width:14px;height:14px;margin-left:6px;"></i>
      </button>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-brand">
    <a href="malangstay.php" class="logo" style="display:flex;align-items:center;gap:0.6rem;text-decoration:none;color:var(--white);font-family:'Cormorant Garamond',serif;font-weight:700;font-size:1.2rem;margin-bottom:0.8rem;">
      <img src="assets/MalangStay_logo_cropped.png" alt="MalangStay" style="height:80px;width:auto;filter:brightness(0) invert(1);">
    </a>
    <p>Platform booking homestay lokal Malang yang cepat, terpercaya, dan terasa seperti pulang ke rumah.</p>
  </div>
  <div>
    <h4>Hubungi Kami</h4>
    <div class="footer-links">
      <a href="mailto:info@malangstay.id"><i data-lucide="mail" style="width:14px;height:14px;"></i> info@malangstay.id</a>
      <a href="#"><i data-lucide="map-pin" style="width:14px;height:14px;"></i> Jl. Tanimbar No. 22, Malang</a>
      <a href="tel:08810366245"><i data-lucide="phone" style="width:14px;height:14px;"></i> 0881 0366 24551</a>
    </div>
  </div>
  <div class="footer-sosmed">
    <h4>Ikuti Kami</h4>
    <div class="social-links">
      <a href="#" title="TikTok"><i data-lucide="music"></i></a>
      <a href="#" title="WhatsApp"><i data-lucide="message-square"></i></a>
    </div>
    <p>Update properti baru & promo spesial tiap minggu.</p>
  </div>
</footer>

<script>
  lucide.createIcons();

  // Form validation
  document.getElementById('btnKirim').addEventListener('click', function () {
    const nama   = document.getElementById('contactNama').value.trim();
    const email  = document.getElementById('contactEmail').value.trim();
    const subjek = document.getElementById('contactSubjek').value.trim();
    const pesan  = document.getElementById('contactPesan').value.trim();
    const alert  = document.getElementById('form-alert');

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!nama || !email || !subjek || !pesan) {
      showAlert('error', 'Semua kolom wajib diisi sebelum mengirim pesan.');
      return;
    }
    if (!emailRegex.test(email)) {
      showAlert('error', 'Format email tidak valid. Coba cek kembali.');
      return;
    }

    // Simulate send (replace with actual fetch/AJAX to your PHP endpoint)
    const btn = document.getElementById('btnKirim');
    btn.disabled = true;
    btn.textContent = 'Mengirim...';

    setTimeout(() => {
      showAlert('success', 'Pesan berhasil dikirim! Kami akan menghubungi kamu segera.');
      document.getElementById('contactNama').value = '';
      document.getElementById('contactEmail').value = '';
      document.getElementById('contactSubjek').value = '';
      document.getElementById('contactPesan').value = '';
      btn.disabled = false;
      btn.innerHTML = 'Kirim Pesan <i data-lucide="send" style="display:inline-block;vertical-align:middle;width:14px;height:14px;margin-left:6px;"></i>';
      lucide.createIcons();
    }, 1200);
  });

  function showAlert(type, msg) {
    const el = document.getElementById('form-alert');
    el.className = 'form-alert form-alert--' + type;
    el.textContent = msg;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 5000);
  }

  // Search enter key
  document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      window.location.href = 'admin/daftar-properti.php?q=' + this.value;
    }
  });

  // Navbar shadow on scroll
  window.addEventListener('scroll', function() {
    document.querySelector('nav').style.boxShadow =
      window.scrollY > 10 ? '0 4px 24px rgba(0,0,0,0.35)' : 'none';
  });
</script>
</body>
</html>