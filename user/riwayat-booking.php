<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

$uid = $_SESSION['user_id'];
$bookings = $conn->query("
    SELECT b.*, h.nama AS homestay_nama, h.lokasi, h.foto
    FROM booking b
    JOIN homestay h ON b.homestay_id = h.id
    WHERE b.user_id = $uid
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Booking — MalangStay</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="login.css">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <style>
    .riwayat-container { max-width: 800px; margin: 0 auto; padding: 120px 2rem 4rem; }
    .riwayat-container h1 { font-family:'Cormorant Garamond',serif; font-size:2rem; margin-bottom:2rem; }
    .riwayat-container h1 span { color: var(--accent-light); }
    .booking-item {
      background: var(--dark3);
      border: 1px solid rgba(139,158,90,0.15);
      border-radius: 14px;
      padding: 1.2rem;
      margin-bottom: 1rem;
      display: flex;
      gap: 1rem;
      align-items: center;
    }
    .booking-item img { width:90px;height:65px;object-fit:cover;border-radius:8px;flex-shrink:0; }
    .booking-item .info { flex:1; }
    .booking-item h4 { font-size:1rem;margin-bottom:4px; }
    .booking-item p  { font-size:0.82rem;color:var(--gray); }
    .status-badge {
      padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:600;
    }
    .status-pending   { background:rgba(232,184,75,0.15);color:var(--gold); }
    .status-confirmed { background:rgba(139,158,90,0.15);color:var(--accent-light); }
    .status-cancelled { background:rgba(232,114,75,0.15);color:#e8724b; }
    .empty-state { text-align:center;padding:3rem;color:var(--gray); }
  </style>
</head>
<body>
<nav>
  <a href="../malangstay.php" class="logo">
    <img src="../assets/MalangStay_logo_cropped.png" alt="MalangStay" class="logo-img">
  </a>
  <ul class="nav-links">
    <li><a href="../admin/daftar-properti.php">Daftar Penginapan</a></li>
    <li><a href="logout.php" class="btn-login">Logout</a></li>
  </ul>
</nav>

<div class="riwayat-container">
  <h1>Riwayat <span>Booking</span></h1>

  <?php if ($bookings && $bookings->num_rows > 0): ?>
    <?php while ($b = $bookings->fetch_assoc()): ?>
    <div class="booking-item">
      <img src="../uploads/homestay/<?= htmlspecialchars($b['foto'] ?: 'placeholder.jpg') ?>"
           alt="<?= htmlspecialchars($b['homestay_nama']) ?>">
      <div class="info">
        <h4><?= htmlspecialchars($b['homestay_nama']) ?></h4>
        <p><i data-lucide="map-pin" style="width:11px;height:11px;"></i> <?= htmlspecialchars($b['lokasi']) ?></p>
        <p><?= date('d M Y', strtotime($b['tanggal_checkin'])) ?> → <?= date('d M Y', strtotime($b['tanggal_checkout'])) ?></p>
        <p style="color:var(--accent-light);font-weight:600;margin-top:4px;">
          Rp <?= number_format($b['total_harga'],0,',','.') ?>
        </p>
      </div>
      <span class="status-badge status-<?= $b['status'] ?>">
        <?= ucfirst($b['status']) ?>
      </span>
    </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="empty-state">
      <i data-lucide="calendar-x" style="width:48px;height:48px;margin-bottom:1rem;opacity:0.3;"></i>
      <p>Belum ada riwayat booking.</p>
      <a href="../admin/daftar-properti.php" style="color:var(--accent-light);margin-top:0.5rem;display:inline-block;">
        Cari homestay →
      </a>
    </div>
  <?php endif; ?>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
