<?php
session_start();
require_once '../config/db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: daftar-properti.php'); exit; }

$stmt = $conn->prepare("SELECT * FROM homestay WHERE id = ? AND status = 'aktif'");
$stmt->bind_param('i', $id);
$stmt->execute();
$h = $stmt->get_result()->fetch_assoc();
if (!$h) { header('Location: daftar-properti.php'); exit; }

$fasilitas = array_filter(array_map('trim', explode(',', $h['fasilitas'] ?? '')));

$bookingError = '';
$bookingSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
  $checkin = $_POST['checkin'] ?? '';
  $checkout = $_POST['checkout'] ?? '';
  $tamu = intval($_POST['tamu'] ?? 1);

  if (!$checkin || !$checkout) {
    $bookingError = 'Tanggal check-in dan check-out wajib diisi.';
  } elseif ($checkout <= $checkin) {
    $bookingError = 'Tanggal check-out harus setelah check-in.';
  } else {
    $malam = (strtotime($checkout) - strtotime($checkin)) / 86400;
    $total = $malam * $h['harga'];

    $stmtBook = $conn->prepare("INSERT INTO booking (homestay_id, user_id, tanggal_checkin, tanggal_checkout, jumlah_tamu, total_harga, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmtBook->bind_param('iissid', $h['id'], $_SESSION['user_id'], $checkin, $checkout, $tamu, $total);

    if ($stmtBook->execute()) {
      $bookingSuccess = 'Booking berhasil! Kami akan konfirmasi dalam 1x24 jam.';
    } else {
      $bookingError = 'Gagal membuat booking. Coba lagi.';
    }
  }
}

$fasIcons = [
  'wifi'     => ['icon'=>'wifi',         'label'=>'WiFi Gratis'],
  'ac'       => ['icon'=>'wind',         'label'=>'AC'],
  'parkir'   => ['icon'=>'car',          'label'=>'Parkir Gratis'],
  'sarapan'  => ['icon'=>'coffee',       'label'=>'Sarapan'],
  'tv'       => ['icon'=>'tv',           'label'=>'TV'],
  'laundry'  => ['icon'=>'shirt',        'label'=>'Laundry'],
  'kolam'    => ['icon'=>'waves',        'label'=>'Kolam Renang'],
  'dapur'    => ['icon'=>'utensils',     'label'=>'Dapur'],
  'security' => ['icon'=>'shield-check', 'label'=>'24 Jam Security'],
  'taman'    => ['icon'=>'trees',        'label'=>'Taman'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($h['nama']) ?> — MalangStay</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <link rel="stylesheet" href="detail-homestay.css">
</head>
<body>

<nav>
  <a href="../malangstay.php" class="logo">
    <img src="../assets/MalangStay_logo_cropped.png" alt="MalangStay" class="logo-img">
  </a>
  <div class="search-bar">
    <i data-lucide="search" style="width:14px;height:14px;"></i>
    <input type="text" placeholder="Homestay di Malang">
  </div>
  <ul class="nav-links">
    <li><a href="../malangstay.php#unggulan">Unggulan</a></li>
    <li><a href="../malangstay.php#kontak">Kontak</a></li>
    <li><a href="daftar-properti.php">Daftar Penginapan</a></li>
    <?php if (isset($_SESSION['user_id'])): ?>
      <li><a href="../user/logout.php" class="btn-login">Logout</a></li>
    <?php else: ?>
      <li><a href="../user/login.php" class="btn-login">Sign In</a></li>
      <li><a href="../user/register.php" class="btn-login">Sign Up</a></li>
    <?php endif; ?>
  </ul>
</nav>

<div class="breadcrumb">
  <a href="../malangstay.php">Beranda</a>
  <i data-lucide="chevron-right" style="width:12px;height:12px;"></i>
  <a href="daftar-properti.php">Daftar Penginapan</a>
  <i data-lucide="chevron-right" style="width:12px;height:12px;"></i>
  <span><?= htmlspecialchars($h['nama']) ?></span>
</div>

<!-- HERO GALLERY: gallery hero buat preview foto utama + thumbnails -->
<div class="gallery">
  <div class="gallery-main">
    <img src="../uploads/homestay/<?= htmlspecialchars($h['foto'] ?: 'placeholder.jpg') ?>"
         alt="<?= htmlspecialchars($h['nama']) ?>" id="mainPhoto">
    <button class="fav-btn" id="favBtn">♡</button>
    <div class="gallery-counter" id="galleryCounter">1 / 1</div>
    <button class="gallery-arrow prev" id="galleryPrev">&#8249;</button>
    <button class="gallery-arrow next" id="galleryNext">&#8250;</button>
  </div>
  <div class="gallery-thumbs" id="galleryThumbs"></div>
</div>

<!-- MAIN CONTENT: detail homestay, deskripsi, fasilitas, dan lokasi di kiri -->
<div class="detail-wrap">

  <div class="detail-left">
    <div class="detail-title-row">
      <div>
        <h1 class="detail-title"><?= htmlspecialchars($h['nama']) ?></h1>
        <div class="detail-location">
          <i data-lucide="map-pin" style="width:14px;height:14px;"></i>
          <?= htmlspecialchars($h['lokasi']) ?>
        </div>
      </div>
      <div class="detail-rating-box">
        <div class="detail-rating-score"><?= $h['rating'] ?></div>
        <div class="detail-rating-label"><?= $h['rating'] >= 9 ? 'Istimewa' : 'Mengesankan' ?></div>
        <div class="detail-rating-count"><?= $h['jumlah_ulasan'] ?> ulasan</div>
      </div>
    </div>

    <div class="detail-tags">
      <span class="detail-tag"><i data-lucide="home" style="width:12px;height:12px;"></i> Homestay</span>
      <span class="detail-tag"><i data-lucide="map-pin" style="width:12px;height:12px;"></i> <?= htmlspecialchars($h['lokasi']) ?></span>
      <?php if ($h['promo']): ?>
        <span class="detail-tag" style="background:rgba(232,184,75,0.15);color:#e8b84b;border-color:rgba(232,184,75,0.3);">
          🏷 Promo <?= $h['diskon'] ?>%
        </span>
      <?php endif; ?>
    </div>

    <hr class="divider">

    <div class="detail-section">
      <h2 class="section-title">Tentang Tempat Ini</h2>
      <p class="detail-desc"><?= nl2br(htmlspecialchars($h['deskripsi'] ?: 'Homestay nyaman di ' . $h['lokasi'] . ' dengan fasilitas lengkap dan pelayanan ramah.')) ?></p>
    </div>

    <hr class="divider">

    <div class="detail-section">
      <h2 class="section-title">Fasilitas</h2>
      <div class="facilities-grid">
        <?php foreach ($fasilitas as $f): ?>
          <?php
            $key  = strtolower(trim($f));
            $icon = $fasIcons[$key]['icon']  ?? 'check-circle';
            $lbl  = $fasIcons[$key]['label'] ?? $f;
          ?>
          <div class="fac-item">
            <i data-lucide="<?= $icon ?>"></i>
            <span><?= htmlspecialchars($lbl) ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (empty($fasilitas)): ?>
          <p style="color:var(--gray);font-size:0.85rem;">Info fasilitas belum tersedia.</p>
        <?php endif; ?>
      </div>
    </div>

    <hr class="divider">

    <div class="detail-section">
      <h2 class="section-title">Lokasi</h2>
      <div class="map-placeholder" style="padding:0;overflow:hidden;height:300px;">
  <iframe
    src="https://maps.google.com/maps?q=<?= urlencode($h['lokasi'] . ', Malang') ?>&output=embed"
    width="100%"
    height="300"
    style="border:0;"
    allowfullscreen=""
    loading="lazy">
  </iframe>
</div>
<a href="https://maps.google.com/?q=<?= urlencode($h['lokasi']) ?>" target="_blank" class="map-link" style="display:block;margin-top:8px;">
  Buka di Google Maps <i data-lucide="arrow-right" style="display:inline-block;vertical-align:middle;width:15px;height:15px;margin-left:5px;"></i>
</a>
    </div>
  </div>

  <!-- BOOKING BOX: panel booking dan info rate di sidebar kanan -->
  <aside class="booking-box">
    <div class="booking-inner">
      <div class="booking-price-label">Harga mulai</div>
      <div class="booking-price">Rp <?= number_format($h['harga'],0,',','.') ?></div>
      <div class="booking-per">/malam/kamar · Sudah termasuk pajak</div>

      <div class="booking-rating-row">
        <span class="booking-badge"><?= $h['rating'] ?></span>
        <span class="booking-badge-label"><?= $h['rating'] >= 9 ? 'Istimewa' : 'Mengesankan' ?></span>
        <span class="booking-ulasan">· <?= $h['jumlah_ulasan'] ?> ulasan</span>
      </div>

      <hr class="divider" style="margin:1rem 0;">

      <?php if (isset($_SESSION['user_id'])): ?>
      <?php if ($bookingError): ?>
        <div style="background:rgba(232,114,75,0.15);border:1px solid rgba(232,114,75,0.4);color:#e8724b;padding:0.75rem 1rem;border-radius:8px;font-size:0.88rem;margin-bottom:1rem;">
          <?= htmlspecialchars($bookingError) ?>
        </div>
      <?php endif; ?>
      <?php if ($bookingSuccess): ?>
        <div style="background:rgba(139,158,90,0.15);border:1px solid rgba(139,158,90,0.4);color:#b5c47e;padding:0.75rem 1rem;border-radius:8px;font-size:0.88rem;margin-bottom:1rem;">
          <?= htmlspecialchars($bookingSuccess) ?>
        </div>
      <?php endif; ?>
      <form method="POST" action="<?= htmlspecialchars('detail.php?id=' . $h['id']) ?>" class="booking-form">
        <div class="form-row">
          <div class="form-group">
            <label>Check-in</label>
            <input type="date" name="checkin" class="form-input" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['checkin'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Check-out</label>
            <input type="date" name="checkout" class="form-input" value="<?= htmlspecialchars($_POST['checkout'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Tamu</label>
          <select name="tamu" class="form-input">
            <option value="1" <?= (intval($_POST['tamu'] ?? 2) === 1 ? 'selected' : '') ?>>1 tamu</option>
            <option value="2" <?= (intval($_POST['tamu'] ?? 2) === 2 ? 'selected' : '') ?>>2 tamu</option>
            <option value="3" <?= (intval($_POST['tamu'] ?? 2) === 3 ? 'selected' : '') ?>>3 tamu</option>
            <option value="4" <?= (intval($_POST['tamu'] ?? 2) === 4 ? 'selected' : '') ?>>4 tamu</option>
          </select>
        </div>
        <div class="booking-total" id="bookingTotal" style="display:none;">
          <div class="total-row">
            <span>Rp <?= number_format($h['harga'],0,',','.') ?> × <span id="nightCount">1</span> malam</span>
            <span id="totalPrice"></span>
          </div>
          <div class="total-row"><span>Pajak & biaya</span><span>Sudah termasuk</span></div>
          <hr style="border-color:rgba(139,158,90,0.2);margin:8px 0;">
          <div class="total-row total-final"><span>Total</span><span id="finalPrice"></span></div>
        </div>
        <button type="submit" class="btn-booking">Pesan Sekarang</button>
      </form>
      <?php else: ?>
      <div class="booking-form">
        <p style="color:var(--gray);font-size:0.85rem;text-align:center;margin-bottom:1rem;">Login untuk melakukan pemesanan.</p>
        <a href="../user/login.php" class="btn-booking" style="display:block;text-align:center;text-decoration:none;">Login & Pesan</a>
      </div>
      <?php endif; ?>

      <div class="booking-note">
        <i data-lucide="shield-check" style="width:12px;height:12px;"></i>
        Pembatalan gratis hingga 24 jam sebelum check-in
      </div>
    </div>
  </aside>

</div>

<script>
lucide.createIcons();

// Gallery
const photos = ['../uploads/homestay/<?= htmlspecialchars($h['foto'] ?: 'placeholder.jpg') ?>'];
let cur = 0;
const mainPhoto = document.getElementById('mainPhoto');
const counter   = document.getElementById('galleryCounter');
const thumbs    = document.getElementById('galleryThumbs');

photos.forEach((src, i) => {
  const t = document.createElement('div');
  t.className = 'gallery-thumb' + (i === 0 ? ' active' : '');
  t.style.backgroundImage = `url('${src}')`;
  t.addEventListener('click', () => goPhoto(i));
  thumbs.appendChild(t);
});

function goPhoto(n) {
  cur = (n + photos.length) % photos.length;
  mainPhoto.src = photos[cur];
  counter.textContent = `${cur + 1} / ${photos.length}`;
  document.querySelectorAll('.gallery-thumb').forEach((t,i) => t.classList.toggle('active', i === cur));
}

document.getElementById('galleryPrev').addEventListener('click', () => goPhoto(cur - 1));
document.getElementById('galleryNext').addEventListener('click', () => goPhoto(cur + 1));

document.getElementById('favBtn').addEventListener('click', function() {
  this.classList.toggle('active');
  this.textContent = this.classList.contains('active') ? '♥' : '♡';
});

// Booking total calc
const harga = <?= $h['harga'] ?>;
const ci = document.querySelector('input[name="checkin"]');
const co = document.querySelector('input[name="checkout"]');
if (ci && co) {
  function updateTotal() {
    if (!ci.value || !co.value) return;
    const nights = Math.max(1, Math.round((new Date(co.value) - new Date(ci.value)) / 86400000));
    const total  = harga * nights;
    document.getElementById('bookingTotal').style.display = 'block';
    document.getElementById('nightCount').textContent = nights;
    document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('finalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
  }
  ci.addEventListener('change', updateTotal);
  co.addEventListener('change', updateTotal);
}
</script>
</body>
</html>
