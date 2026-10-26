<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php?redirect=booking');
  exit;
}

require_once '../config/db.php';

$id = intval($_GET['id'] ?? 0);
$homestay = null;
$error = '';
$success = '';

if ($id) {
  $stmt = $conn->prepare("SELECT * FROM homestay WHERE id = ? AND status = 'aktif'");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $homestay = $stmt->get_result()->fetch_assoc();
}

if (!$homestay) {
  header('Location: ../admin/daftar-properti.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $checkin = $_POST['checkin'] ?? '';
  $checkout = $_POST['checkout'] ?? '';
  $tamu = intval($_POST['tamu'] ?? 1);
  $catatan = trim($_POST['catatan'] ?? '');

  if (!$checkin || !$checkout) {
    $error = 'Tanggal check-in dan check-out wajib diisi.';
  } elseif ($checkout <= $checkin) {
    $error = 'Tanggal check-out harus setelah check-in.';
  } else {
    $malam = (strtotime($checkout) - strtotime($checkin)) / 86400;
    $total = $malam * $homestay['harga'];

    $ins = $conn->prepare("INSERT INTO booking (homestay_id, user_id, tanggal_checkin, tanggal_checkout, jumlah_tamu, total_harga, catatan, status)
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $ins->bind_param('iissids', $id, $_SESSION['user_id'], $checkin, $checkout, $tamu, $total, $catatan);

    if ($ins->execute()) {
      $success = 'Booking berhasil! Kami akan konfirmasi dalam 1x24 jam.';
    } else {
      $error = 'Gagal membuat booking. Coba lagi.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking <?= htmlspecialchars($homestay['nama']) ?> — MalangStay</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="login.css">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <style>
    .booking-card {
      max-width: 560px;
    }

    .homestay-summary {
      background: rgba(139, 158, 90, 0.08);
      border: 1px solid rgba(139, 158, 90, 0.2);
      border-radius: 10px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .homestay-summary img {
      width: 80px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
    }

    .homestay-summary h4 {
      font-size: 0.95rem;
      margin-bottom: 4px;
    }

    .homestay-summary p {
      font-size: 0.8rem;
      color: var(--gray);
    }

    .price-calc {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(139, 158, 90, 0.15);
      border-radius: 10px;
      padding: 1rem;
      margin: 1rem 0;
      font-size: 0.85rem;
    }

    .price-calc .total {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--accent-light);
      margin-top: 0.5rem;
    }
  </style>
</head>

<body>
  <nav>
    <a href="../malangstay.php" class="logo">
      <img src="../assets/MalangStay_logo_cropped.png" alt="MalangStay" class="logo-img">
    </a>
    <ul class="nav-links">
      <li><a href="../malangstay.php#unggulan">Unggulan</a></li>
      <li><a href="../admin/daftar-properti.php">Daftar Penginapan</a></li>
      <li><a href="logout.php" class="btn-login">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
    </ul>
  </nav>

  <section class="login-container">
    <div class="card booking-card">
      <h2 style="margin-bottom:1.5rem;">Form Booking</h2>

      <div class="homestay-summary">
        <img src="../uploads/homestay/<?= htmlspecialchars($homestay['foto'] ?: 'placeholder.jpg') ?>"
          alt="<?= htmlspecialchars($homestay['nama']) ?>">
        <div>
          <h4><?= htmlspecialchars($homestay['nama']) ?></h4>
          <p><i data-lucide="map-pin" style="width:11px;height:11px;"></i> <?= htmlspecialchars($homestay['lokasi']) ?>
          </p>
          <p style="color:var(--accent-light);font-weight:600;">Rp <?= number_format($homestay['harga'], 0, ',', '.') ?>
            /
            malam</p>
        </div>
      </div>

      <?php if ($error): ?>
        <div style="background:rgba(232,114,75,0.15);border:1px solid rgba(232,114,75,0.4);color:#e8724b;
                  padding:0.75rem 1rem;border-radius:8px;font-size:0.85rem;margin-bottom:1rem;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div style="background:rgba(139,158,90,0.15);border:1px solid rgba(139,158,90,0.4);color:#b5c47e;
                  padding:0.75rem 1rem;border-radius:8px;font-size:0.85rem;margin-bottom:1rem;">
          ✅ <?= htmlspecialchars($success) ?>
          <br><a href="riwayat-booking.php" style="color:#b5c47e;font-weight:600;">Lihat riwayat booking →</a>
        </div>
      <?php endif; ?>

      <form class="form" method="POST">
        <div class="input-group">
          <label style="font-size:0.82rem;color:var(--gray);margin-bottom:4px;display:block;">Tanggal Check-In</label>
          <input type="date" name="checkin" required min="<?= date('Y-m-d') ?>"
            value="<?= htmlspecialchars($_POST['checkin'] ?? '') ?>">
        </div>
        <div class="input-group">
          <label style="font-size:0.82rem;color:var(--gray);margin-bottom:4px;display:block;">Tanggal Check-Out</label>
          <input type="date" name="checkout" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
            value="<?= htmlspecialchars($_POST['checkout'] ?? '') ?>">
        </div>
        <div class="input-group">
          <label style="font-size:0.82rem;color:var(--gray);margin-bottom:4px;display:block;">Jumlah Tamu</label>
          <input type="number" name="tamu" min="1" max="20" value="<?= intval($_POST['tamu'] ?? 2) ?>">
        </div>
        <div class="input-group">
          <label style="font-size:0.82rem;color:var(--gray);margin-bottom:4px;display:block;">Catatan (opsional)</label>
          <input type="text" name="catatan" placeholder="Permintaan khusus, dll."
            value="<?= htmlspecialchars($_POST['catatan'] ?? '') ?>">
        </div>

        <div class="price-calc" id="priceCalc" style="display:none;">
          <div id="calcDetail"></div>
          <div class="total" id="calcTotal"></div>
        </div>

        <button type="submit" class="btn-login">Konfirmasi Booking</button>
      </form>
    </div>
  </section>

  <script>
    lucide.createIcons();
    const harga = <?= $homestay['harga'] ?>;
    const checkinInput = document.querySelector('input[name="checkin"]');
    const checkoutInput = document.querySelector('input[name="checkout"]');

    function updateCalc() {
      const ci = new Date(checkinInput.value);
      const co = new Date(checkoutInput.value);
      if (checkinInput.value && checkoutInput.value && co > ci) {
        const malam = Math.round((co - ci) / 86400000);
        const total = malam * harga;
        document.getElementById('calcDetail').textContent = malam + ' malam × Rp ' + harga.toLocaleString('id-ID');
        document.getElementById('calcTotal').textContent = 'Total: Rp ' + total.toLocaleString('id-ID');
        document.getElementById('priceCalc').style.display = 'block';
      } else {
        document.getElementById('priceCalc').style.display = 'none';
      }
    }
    checkinInput.addEventListener('change', updateCalc);
    checkoutInput.addEventListener('change', updateCalc);
  </script>
</body>

</html>