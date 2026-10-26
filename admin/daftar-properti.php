<?php
session_start();
require_once '../config/db.php';

// Filter & search
$where  = "WHERE status = 'aktif'";
$params = [];
$types  = '';

$q      = trim($_GET['q'] ?? '');
$lokasi = trim($_GET['lokasi'] ?? '');
$promo  = isset($_GET['promo']) ? 1 : null;
$maxHarga = intval($_GET['max_harga'] ?? 0);
$minRating = floatval($_GET['min_rating'] ?? 0);

if ($q) {
    $where .= " AND (nama LIKE ? OR lokasi LIKE ? OR fasilitas LIKE ?)";
    $like = "%$q%";
    array_push($params, $like, $like, $like);
    $types .= 'sss';
}
if ($lokasi) {
    $where .= " AND lokasi LIKE ?";
    $params[] = "%$lokasi%";
    $types   .= 's';
}
if ($promo) {
    $where .= " AND promo = 1";
}
if ($maxHarga > 0) {
    $where .= " AND harga <= ?";
    $params[] = $maxHarga;
    $types   .= 'i';
}
if ($minRating > 0) {
    $where .= " AND rating >= ?";
    $params[] = $minRating;
    $types   .= 'd';
}

$sql = "SELECT * FROM homestay $where ORDER BY rating DESC";
if ($types) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$total = $result ? $result->num_rows : 0;
$homestays = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Homestay — MalangStay</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <link rel="stylesheet" href="daftar-properti.css">
</head>
<body>

<nav>
  <a href="../malangstay.php" class="logo">
    <img src="../assets/MalangStay_logo_cropped.png" alt="MalangStay" class="logo-img">
  </a>
  <div class="search-bar">
    <i data-lucide="search" style="width:14px;height:14px;"></i>
    <form method="GET" style="display:contents;">
      <input type="text" name="q" placeholder="Homestay di Malang" value="<?= htmlspecialchars($q) ?>">
    </form>
  </div>
  <ul class="nav-links">
    <li><a href="../malangstay.php#unggulan">Unggulan</a></li>
    <li><a href="../malangstay.php#kontak">Kontak</a></li>
    <li><a href="daftar-properti.php">Daftar Penginapan</a></li>
    <?php if (isset($_SESSION['user_id'])): ?>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="dashboard.php" class="btn-login">Dashboard</a></li>
      <?php else: ?>
        <li><a href="../user/riwayat-booking.php" class="btn-login">Booking Saya</a></li>
      <?php endif; ?>
      <li><a href="../user/logout.php" class="btn-login">Logout</a></li>
    <?php else: ?>
      <li><a href="../user/login.php" class="btn-login">Sign In</a></li>
      <li><a href="../user/register.php" class="btn-login">Sign Up</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- PAGE HEADER: header page simple, title + description buat quick context -->
<div class="page-header">
  <h1>Daftar <span>Homestay</span></h1>
  <p>Malang & sekitarnya — <?= $total ?> properti ditemukan</p>
</div>

<!-- FILTER BAR: filter row buat cari atau sort properti dengan cepat -->
<form method="GET" action="daftar-properti.php">
<div class="filter-bar">
  <span class="filter-label">Filter:</span>
  <button type="submit" name="" class="filter-chip <?= !$lokasi && !$promo && !$maxHarga && !$minRating ? 'active' : '' ?>">Semua</button>
  <button type="submit" name="lokasi" value="Batu" class="filter-chip <?= $lokasi === 'Batu' ? 'active' : '' ?>">Batu</button>
  <button type="submit" name="lokasi" value="Kota Malang" class="filter-chip <?= $lokasi === 'Kota Malang' ? 'active' : '' ?>">Kota Malang</button>
  <button type="submit" name="min_rating" value="9" class="filter-chip <?= $minRating == 9 ? 'active' : '' ?>">Rating 9+</button>
  <button type="submit" name="max_harga" value="200000" class="filter-chip <?= $maxHarga == 200000 ? 'active' : '' ?>">Di bawah Rp 200rb</button>
  <button type="submit" name="promo" value="1" class="filter-chip <?= $promo ? 'active' : '' ?>">Ada Promo</button>
  <span class="result-count"><strong><?= $total ?></strong> properti</span>
</div>
</form>

<!-- MAIN: content utama, list properti + sidebar quick stats -->
<div class="main">

  <!-- SIDEBAR: sidebar kecil buat ringkasan dan action cepat -->
  <aside class="sidebar">
    <form method="GET" action="daftar-properti.php">
      <div class="sidebar-card">
        <div class="sidebar-title">Lokasi</div>
        <?php foreach (['Batu, Malang','Kota Malang','Lowokwaru','Blimbing','Klojen'] as $loc): ?>
        <div class="filter-group">
          <label>
            <input type="checkbox" name="lokasi[]" value="<?= $loc ?>"
              <?= (is_array($_GET['lokasi'] ?? null) && in_array($loc, $_GET['lokasi'])) ? 'checked' : '' ?>>
            <?= $loc ?>
          </label>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="sidebar-btn">Terapkan Filter</button>
    </form>
  </aside>

  <!-- HOTEL LIST: daftar properti ditampilkan sebagai cards atau rows -->
  <div class="hotel-list">
    <?php if (empty($homestays)): ?>
      <div style="text-align:center;padding:4rem;color:#8a8d6a;">
        <i data-lucide="search-x" style="width:48px;height:48px;opacity:0.3;margin-bottom:1rem;"></i>
        <p>Tidak ada homestay yang sesuai filter.</p>
        <a href="daftar-properti.php" style="color:#b5c47e;margin-top:0.5rem;display:inline-block;">Reset filter →</a>
      </div>
    <?php else: ?>
    <?php foreach ($homestays as $h): ?>
    <div class="hotel-card">
      <div class="hotel-img-wrap">
        <img src="../uploads/homestay/<?= htmlspecialchars($h['foto'] ?: 'placeholder.jpg') ?>"
             class="hotel-img" alt="<?= htmlspecialchars($h['nama']) ?>">
        <?php if ($h['promo']): ?>
          <div class="hotel-badge">PROMO</div>
        <?php endif; ?>
        <button class="hotel-fav">♡</button>
      </div>
      <div class="hotel-info">
        <a href="detail.php?id=<?= $h['id'] ?>" class="hotel-name"><?= htmlspecialchars($h['nama']) ?></a>
        <div class="rating-row">
          <span class="rating-badge <?= $h['rating'] >= 9 ? 'high' : 'mid' ?>"><?= $h['rating'] ?></span>
          <span class="rating-text"><?= $h['rating'] >= 9 ? 'Istimewa' : 'Mengesankan' ?></span>
          <span class="rating-count">(<?= $h['jumlah_ulasan'] ?? 0 ?> ulasan)</span>
        </div>
        <div class="location-row">
          <i data-lucide="map-pin" style="width:12px;height:12px;"></i>
          Homestay · <?= htmlspecialchars($h['lokasi']) ?>
        </div>
        <?php if ($h['promo'] && $h['kode_promo']): ?>
        <div class="promo-tag">
          <i data-lucide="tag" style="width:10px;height:10px;"></i>
          Kupon <?= htmlspecialchars($h['kode_promo']) ?> — Diskon <?= $h['diskon'] ?>%
        </div>
        <?php endif; ?>
        <?php if ($h['review_snippet']): ?>
          <div class="review-snippet">"<?= htmlspecialchars($h['review_snippet']) ?>"</div>
        <?php endif; ?>
        <div class="facilities">
          <?php foreach (explode(',', $h['fasilitas'] ?? '') as $f): ?>
            <?php $f = trim($f); if (!$f) continue; ?>
            <span class="fac-pill"><?= htmlspecialchars($f) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="hotel-price">
        <div class="price-top">
          <div class="price-label">Harga mulai</div>
          <div class="price-amount">Rp <?= number_format($h['harga'],0,',','.') ?></div>
          <div class="price-per">/malam/kamar</div>
          <div class="price-note">Sudah termasuk pajak</div>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="../user/booking.php?id=<?= $h['id'] ?>" class="btn-pesan">Pesan Sekarang</a>
        <?php else: ?>
          <a href="../user/login.php" class="btn-pesan">Pesan Sekarang</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- SLIDESHOW PANEL: panel foto slideshow buat preview visual properti -->
  <?php if (!empty($homestays)): ?>
  <aside class="slideshow-panel">
    <div class="ss-header">Foto Homestay</div>
    <div class="ss-main" id="ssMain">
      <?php foreach ($homestays as $i => $h): ?>
      <div class="ss-slide <?= $i === 0 ? 'active' : '' ?>"
           style="background:url('../uploads/homestay/<?= htmlspecialchars($h['foto'] ?: 'placeholder.jpg') ?>') center/cover no-repeat;">
        <div class="ss-overlay"></div>
        <span class="ss-rating"><?= $h['rating'] ?></span>
        <div class="ss-info">
          <div class="ss-name"><?= htmlspecialchars($h['nama']) ?></div>
          <div class="ss-price">Rp <?= number_format($h['harga'],0,',','.') ?> / malam</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="ss-dots" id="ssDots"></div>
    <div class="ss-thumbs">
      <?php foreach ($homestays as $i => $h): ?>
      <div class="ss-thumb <?= $i === 0 ? 'active' : '' ?>" data-i="<?= $i ?>"
           style="background:url('../uploads/homestay/<?= htmlspecialchars($h['foto'] ?: 'placeholder.jpg') ?>') center/cover no-repeat;"></div>
      <?php endforeach; ?>
    </div>
  </aside>
  <?php endif; ?>

</div>

<script>
  lucide.createIcons();

  document.querySelectorAll('.hotel-fav').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.style.color = btn.style.color === 'rgb(231, 76, 60)' ? '#ccc' : '#e74c3c';
    });
  });

  const slides = document.querySelectorAll('.ss-slide');
  const dotsContainer = document.getElementById('ssDots');
  const thumbs = document.querySelectorAll('.ss-thumb');
  let cur = 0;

  if (slides.length) {
    slides.forEach((_, i) => {
      const d = document.createElement('div');
      d.className = 'ss-dot' + (i === 0 ? ' active' : '');
      d.addEventListener('click', () => goTo(i));
      dotsContainer.appendChild(d);
    });

    function goTo(n) {
      slides[cur].classList.remove('active');
      document.querySelectorAll('.ss-dot')[cur].classList.remove('active');
      thumbs[cur].classList.remove('active');
      cur = n;
      slides[cur].classList.add('active');
      document.querySelectorAll('.ss-dot')[cur].classList.add('active');
      thumbs[cur].classList.add('active');
    }

    thumbs.forEach(t => t.addEventListener('click', () => goTo(+t.dataset.i)));
    setInterval(() => goTo((cur + 1) % slides.length), 3000);
  }
</script>
</body>
</html>
