<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once '../config/db.php';

$booking_id = intval($_GET['booking_id'] ?? 0);
$booking = null;

if ($booking_id) {
  $stmt = $conn->prepare("
        SELECT b.*, h.nama AS homestay_nama, h.lokasi, h.foto, h.harga
        FROM booking b
        JOIN homestay h ON b.homestay_id = h.id
        WHERE b.id = ? AND b.user_id = ?
    ");
  $stmt->bind_param('ii', $booking_id, $_SESSION['user_id']);
  $stmt->execute();
  $booking = $stmt->get_result()->fetch_assoc();
}

if (!$booking) {
  header('Location: riwayat-booking.php');
  exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $metode = $_POST['metode'] ?? '';
  $nama_pengirim = trim($_POST['nama_pengirim'] ?? '');

  if (!$metode || !$nama_pengirim) {
    $error = 'Mohon lengkapi semua field pembayaran.';
  } else {
    // Simpan bukti transfer jika ada
    $bukti = '';
    if (!empty($_FILES['bukti']['name'])) {
      $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
      if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
        $bukti = uniqid('bukti_') . '.' . $ext;
        move_uploaded_file($_FILES['bukti']['tmp_name'], '../uploads/pembayaran/' . $bukti);
      }
    }

    // Update booking status → waiting_payment (atau langsung confirmed jika auto)
    $upd = $conn->prepare("UPDATE booking SET status = 'confirmed', catatan = CONCAT(IFNULL(catatan,''), ' | Bayar via: ', ?, ', Atas nama: ', ?) WHERE id = ?");
    $upd->bind_param('ssi', $metode, $nama_pengirim, $booking_id);
    if ($upd->execute()) {
      $success = true;
    } else {
      $error = 'Gagal menyimpan pembayaran.';
    }
  }
}

$malam = (strtotime($booking['tanggal_checkout']) - strtotime($booking['tanggal_checkin'])) / 86400;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran — MalangStay</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700&family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <link rel="stylesheet" href="login.css">
  <style>
    .pay-wrap {
      max-width: 860px;
      margin: 0 auto;
      padding: 120px 1.5rem 4rem;
      display: grid;
      grid-template-columns: 1fr 360px;
      gap: 2rem;
      align-items: start;
    }

    @media(max-width:720px) {
      .pay-wrap {
        grid-template-columns: 1fr;
      }
    }

    h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.9rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }

    h1 span {
      color: var(--accent-light);
    }

    /* ORDER SUMMARY */
    .order-card {
      background: var(--dark3);
      border: 1px solid rgba(139, 158, 90, 0.15);
      border-radius: 16px;
      overflow: hidden;
      position: sticky;
      top: 100px;
    }

    .order-img {
      width: 100%;
      height: 160px;
      object-fit: cover;
    }

    .order-body {
      padding: 1.2rem;
    }

    .order-name {
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .order-loc {
      font-size: 0.8rem;
      color: var(--gray);
      display: flex;
      align-items: center;
      gap: 4px;
      margin-bottom: 1rem;
    }

    .order-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.83rem;
      padding: 6px 0;
      border-bottom: 1px solid rgba(139, 158, 90, 0.08);
      color: rgba(245, 243, 237, 0.7);
    }

    .order-row:last-child {
      border: none;
    }

    .order-total {
      display: flex;
      justify-content: space-between;
      font-size: 1rem;
      font-weight: 700;
      padding-top: 0.8rem;
      color: var(--accent-light);
    }

    /* PAYMENT FORM */
    .pay-form-card {
      background: var(--dark3);
      border: 1px solid rgba(139, 158, 90, 0.15);
      border-radius: 16px;
      padding: 1.8rem;
    }

    .pay-form-card h2 {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1.4rem;
    }

    .form-group {
      margin-bottom: 1.1rem;
    }

    .form-group label {
      display: block;
      font-size: 0.82rem;
      color: var(--gray);
      margin-bottom: 6px;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 0.75rem 1rem;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(139, 158, 90, 0.2);
      border-radius: 8px;
      color: white;
      font-size: 0.88rem;
      outline: none;
      transition: 0.2s;
      font-family: 'Poppins', sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: var(--accent-light);
      background: rgba(139, 158, 90, 0.05);
    }

    .form-group select option {
      background: var(--dark3);
    }

    /* METODE CARDS */
    .metode-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.6rem;
      margin-bottom: 1.2rem;
    }

    .metode-card {
      border: 1.5px solid rgba(139, 158, 90, 0.2);
      border-radius: 10px;
      padding: 0.75rem;
      cursor: pointer;
      transition: 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.83rem;
      color: rgba(245, 243, 237, 0.6);
      background: rgba(255, 255, 255, 0.03);
    }

    .metode-card:hover {
      border-color: var(--accent-light);
      color: var(--white);
    }

    .metode-card.selected {
      border-color: var(--accent);
      background: var(--accent-dim);
      color: var(--white);
    }

    .metode-card svg {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
    }

    /* REKENING INFO */
    .rekening-box {
      background: rgba(139, 158, 90, 0.07);
      border: 1px solid rgba(139, 158, 90, 0.2);
      border-radius: 10px;
      padding: 1rem;
      margin-bottom: 1.2rem;
      display: none;
    }

    .rekening-box.show {
      display: block;
    }

    .rek-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.85rem;
      margin-bottom: 6px;
    }

    .rek-row:last-child {
      margin: 0;
    }

    .rek-label {
      color: var(--gray);
    }

    .rek-val {
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .copy-btn {
      background: none;
      border: 1px solid rgba(139, 158, 90, 0.3);
      color: var(--accent-light);
      font-size: 0.72rem;
      padding: 2px 8px;
      border-radius: 4px;
      cursor: pointer;
      transition: 0.2s;
      font-family: 'Poppins', sans-serif;
    }

    .copy-btn:hover {
      background: var(--accent-dim);
    }

    /* ALERT */
    .alert {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      font-size: 0.85rem;
      margin-bottom: 1rem;
    }

    .alert-error {
      background: rgba(232, 114, 75, 0.15);
      border: 1px solid rgba(232, 114, 75, 0.3);
      color: #e8724b;
    }

    .alert-success {
      background: rgba(139, 158, 90, 0.15);
      border: 1px solid rgba(139, 158, 90, 0.3);
      color: var(--accent-light);
    }

    /* SUCCESS STATE */
    .success-state {
      text-align: center;
      padding: 3rem 1rem;
    }

    .success-state .check {
      width: 64px;
      height: 64px;
      background: var(--accent-dim);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.2rem;
    }

    .success-state h3 {
      font-size: 1.3rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .success-state p {
      color: var(--gray);
      font-size: 0.88rem;
      margin-bottom: 1.5rem;
    }

    .btn-pay {
      width: 100%;
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      color: white;
      border: none;
      padding: 0.9rem;
      border-radius: 10px;
      font-size: 0.95rem;
      font-weight: 700;
      cursor: pointer;
      transition: 0.3s;
      font-family: 'Poppins', sans-serif;
    }

    .btn-pay:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 18px rgba(94, 107, 53, 0.3);
    }

    .pay-note {
      font-size: 0.76rem;
      color: var(--gray);
      text-align: center;
      margin-top: 0.8rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
    }
  </style>
</head>

<body>

  <nav>
    <a href="../malangstay.php" class="logo">
      <img src="../assets/MalangStay_logo_cropped.png" alt="MalangStay" class="logo-img">
    </a>
    <ul class="nav-links">
      <li><a href="../admin/daftar-properti.php">Daftar Penginapan</a></li>
      <li><a href="riwayat-booking.php" class="btn-login">Booking Saya</a></li>
      <li><a href="logout.php" class="btn-login">Logout</a></li>
    </ul>
  </nav>

  <div class="pay-wrap">

    <!-- KIRI: FORM: left section untuk konfirmasi pembayaran + upload bukti -->
    <div>
      <h1>Konfirmasi <span>Pembayaran</span></h1>

      <?php if ($success): ?>
        <div class="pay-form-card">
          <div class="success-state">
            <div class="check">
              <i data-lucide="check" style="width:30px;height:30px;color:var(--accent-light);"></i>
            </div>
            <h3>Pembayaran Berhasil!</h3>
            <p>Booking kamu sudah dikonfirmasi. Kami akan mengirim detail ke email kamu.</p>
            <a href="riwayat-booking.php" class="btn-pay"
              style="display:inline-block;text-decoration:none;padding:0.75rem 2rem;width:auto;">
              Lihat Riwayat Booking
            </a>
          </div>
        </div>

      <?php else: ?>

        <?php if ($error): ?>
          <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="pay-form-card">
          <h2>Pilih Metode Pembayaran</h2>

          <div class="metode-grid" id="metodeGrid">
            <div class="metode-card" data-val="BCA Transfer" onclick="selectMetode(this)">
              <i data-lucide="building-2"></i> BCA Transfer
            </div>
            <div class="metode-card" data-val="Mandiri Transfer" onclick="selectMetode(this)">
              <i data-lucide="building-2"></i> Mandiri Transfer
            </div>
            <div class="metode-card" data-val="BNI Transfer" onclick="selectMetode(this)">
              <i data-lucide="building-2"></i> BNI Transfer
            </div>
            <div class="metode-card" data-val="GoPay" onclick="selectMetode(this)">
              <i data-lucide="smartphone"></i> GoPay
            </div>
            <div class="metode-card" data-val="OVO" onclick="selectMetode(this)">
              <i data-lucide="smartphone"></i> OVO
            </div>
            <div class="metode-card" data-val="DANA" onclick="selectMetode(this)">
              <i data-lucide="smartphone"></i> DANA
            </div>
          </div>

          <!-- Info rekening: muncul after select metode payment -->
          <div class="rekening-box" id="rekeningBox">
            <div class="rek-row">
              <span class="rek-label">Bank / Dompet</span>
              <span class="rek-val" id="rekBank">—</span>
            </div>
            <div class="rek-row">
              <span class="rek-label">No. Rekening / ID</span>
              <span class="rek-val">
                <span id="rekNo">—</span>
                <button class="copy-btn" onclick="copyRek()">Salin</button>
              </span>
            </div>
            <div class="rek-row">
              <span class="rek-label">Atas Nama</span>
              <span class="rek-val" id="rekName">MalangStay</span>
            </div>
            <div class="rek-row" style="margin-top:8px;">
              <span class="rek-label" style="font-size:0.75rem;color:rgba(245,243,237,0.4);">
                Transfer tepat sesuai jumlah total. Jangan tambah/kurangi nominal.
              </span>
            </div>
          </div>

          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="metode" id="metodeInput" value="">

            <div class="form-group">
              <label>Nama Pengirim / Pemilik Rekening *</label>
              <input type="text" name="nama_pengirim" placeholder="Sesuai nama rekening yang dipakai transfer" required
                value="<?= htmlspecialchars($_POST['nama_pengirim'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Bukti Transfer (opsional — JPG, PNG, atau PDF)</label>
              <input type="file" name="bukti" accept="image/jpeg,image/png,application/pdf" style="padding:0.5rem;">
            </div>

            <button type="submit" class="btn-pay" id="btnPay" disabled>
              <i data-lucide="lock"
                style="width:15px;height:15px;display:inline-block;vertical-align:middle;margin-right:6px;"></i>
              Konfirmasi Pembayaran
            </button>
            <div class="pay-note">
              <i data-lucide="shield-check" style="width:12px;height:12px;"></i>
              Transaksi aman & terenkripsi
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <!-- KANAN: ORDER SUMMARY: summary booking detail di panel kanan -->
    <aside>
      <div class="order-card">
        <img src="../uploads/homestay/<?= htmlspecialchars($booking['foto'] ?: 'placeholder.jpg') ?>" class="order-img"
          alt="<?= htmlspecialchars($booking['homestay_nama']) ?>">
        <div class="order-body">
          <div class="order-name"><?= htmlspecialchars($booking['homestay_nama']) ?></div>
          <div class="order-loc">
            <i data-lucide="map-pin" style="width:11px;height:11px;"></i>
            <?= htmlspecialchars($booking['lokasi']) ?>
          </div>
          <div class="order-row">
            <span>Check-in</span>
            <span><?= date('d M Y', strtotime($booking['tanggal_checkin'])) ?></span>
          </div>
          <div class="order-row">
            <span>Check-out</span>
            <span><?= date('d M Y', strtotime($booking['tanggal_checkout'])) ?></span>
          </div>
          <div class="order-row">
            <span>Durasi</span>
            <span><?= $malam ?> malam</span>
          </div>
          <div class="order-row">
            <span>Tamu</span>
            <span><?= $booking['jumlah_tamu'] ?> orang</span>
          </div>
          <div class="order-row">
            <span>Rp <?= number_format($booking['harga'], 0, ',', '.') ?> × <?= $malam ?> malam</span>
            <span>Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></span>
          </div>
          <div class="order-row">
            <span>Pajak & biaya</span>
            <span>Sudah termasuk</span>
          </div>
          <div class="order-total">
            <span>Total</span>
            <span>Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></span>
          </div>
        </div>
      </div>
    </aside>

  </div>

  <script>
    lucide.createIcons();

    const rekeningData = {
      'BCA Transfer': { no: '1234567890', name: 'MalangStay Indonesia' },
      'Mandiri Transfer': { no: '9876543210', name: 'MalangStay Indonesia' },
      'BNI Transfer': { no: '1122334455', name: 'MalangStay Indonesia' },
      'GoPay': { no: '0822-4597-5089', name: 'MalangStay' },
      'OVO': { no: '0822-4597-5089', name: 'MalangStay' },
      'DANA': { no: '0822-4597-5089', name: 'MalangStay' },
    };

    function selectMetode(el) {
      document.querySelectorAll('.metode-card').forEach(c => c.classList.remove('selected'));
      el.classList.add('selected');
      const val = el.dataset.val;
      document.getElementById('metodeInput').value = val;
      document.getElementById('btnPay').disabled = false;

      const data = rekeningData[val];
      document.getElementById('rekBank').textContent = val;
      document.getElementById('rekNo').textContent = data.no;
      document.getElementById('rekName').textContent = data.name;
      document.getElementById('rekeningBox').classList.add('show');
    }

    function copyRek() {
      const no = document.getElementById('rekNo').textContent;
      navigator.clipboard.writeText(no).then(() => {
        const btn = document.querySelector('.copy-btn');
        btn.textContent = 'Disalin!';
        setTimeout(() => btn.textContent = 'Salin', 1500);
      });
    }
  </script>
</body>

</html>