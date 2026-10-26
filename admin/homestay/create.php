<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../../user/login.php');
  exit;
}
require_once '../../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = trim($_POST['nama'] ?? '');
  $lokasi = trim($_POST['lokasi'] ?? '');
  $harga = intval($_POST['harga'] ?? 0);
  $rating = floatval($_POST['rating'] ?? 0);
  $fasilitas = trim($_POST['fasilitas'] ?? '');
  $deskripsi = trim($_POST['deskripsi'] ?? '');
  $status = $_POST['status'] ?? 'aktif';
  $promo = isset($_POST['promo']) ? 1 : 0;
  $kode_promo = trim($_POST['kode_promo'] ?? '');
  $diskon = intval($_POST['diskon'] ?? 0);
  $review_snippet = trim($_POST['review_snippet'] ?? '');
  $jumlah_ulasan = intval($_POST['jumlah_ulasan'] ?? 0);
  $foto = '';

  if (!$nama || !$lokasi || !$harga) {
    $error = 'Nama, lokasi, dan harga wajib diisi.';
  } else {
    // Upload foto
    if (!empty($_FILES['foto']['name'])) {
      $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg', 'jpeg', 'png', 'webp'];
      if (!in_array($ext, $allowed)) {
        $error = 'Format foto harus JPG, PNG, atau WEBP.';
      } else {
        $foto = uniqid('hs_') . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../../uploads/homestay/' . $foto);
      }
    }

    if (!$error) {
      $stmt = $conn->prepare("INSERT INTO homestay (nama,lokasi,harga,rating,fasilitas,deskripsi,foto,status,promo,kode_promo,diskon,review_snippet,jumlah_ulasan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $stmt->bind_param('ssidssssisisi', $nama, $lokasi, $harga, $rating, $fasilitas, $deskripsi, $foto, $status, $promo, $kode_promo, $diskon, $review_snippet, $jumlah_ulasan);
      if ($stmt->execute()) {
        header('Location: index.php?msg=saved');
        exit;
      } else {
        $error = 'Gagal menyimpan data: ' . $conn->error;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Homestay — MalangStay</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    :root {
      --accent: #8b9e5a;
      --accent-dark: #5e6b35;
      --accent-light: #b5c47e;
      --accent-dim: rgba(139, 158, 90, 0.13);
      --dark: #191a13;
      --dark3: #1e1f17;
      --white: #f5f3ed;
      --gray: #8a8d6a;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: var(--dark);
      color: var(--white);
    }

    .wrap {
      max-width: 700px;
      margin: 0 auto;
      padding: 2rem;
    }

    .back-link {
      color: var(--accent-light);
      text-decoration: none;
      font-size: 0.85rem;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      margin-bottom: 1rem;
    }

    h1 {
      font-size: 1.4rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
    }

    .card {
      background: var(--dark3);
      border: 1px solid rgba(139, 158, 90, 0.15);
      border-radius: 14px;
      padding: 2rem;
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    .form-group label {
      display: block;
      font-size: 0.83rem;
      color: var(--gray);
      margin-bottom: 6px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
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
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: var(--accent-light);
      background: rgba(139, 158, 90, 0.05);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 100px;
    }

    .form-group select option {
      background: var(--dark3);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.88rem;
    }

    .checkbox-group input {
      width: auto;
    }

    .btn-submit {
      background: var(--accent);
      color: white;
      border: none;
      padding: 0.85rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.95rem;
      cursor: pointer;
      transition: 0.2s;
      width: 100%;
      margin-top: 0.5rem;
    }

    .btn-submit:hover {
      background: var(--accent-dark);
    }

    .alert-error {
      background: rgba(232, 114, 75, 0.15);
      border: 1px solid rgba(232, 114, 75, 0.3);
      color: #e8724b;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      font-size: 0.85rem;
      margin-bottom: 1rem;
    }
  </style>
</head>

<body>
  <div class="wrap">
    <a href="index.php" class="back-link"><i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Kembali</a>
    <h1>Tambah Homestay Baru</h1>
    <?php if ($error): ?>
      <div class="alert-error"> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="card">
      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label>Nama Homestay *</label>
          <input type="text" name="nama" placeholder="Contoh: Villa Indah Batu" required
            value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Lokasi *</label>
            <input type="text" name="lokasi" placeholder="Contoh: Batu, Malang" required
              value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Harga per Malam (Rp) *</label>
            <input type="number" name="harga" placeholder="Contoh: 500000" required min="0"
              value="<?= intval($_POST['harga'] ?? 0) ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Rating (0–10)</label>
            <input type="number" name="rating" step="0.1" min="0" max="10" placeholder="9.4"
              value="<?= htmlspecialchars($_POST['rating'] ?? '0') ?>">
          </div>
          <div class="form-group">
            <label>Jumlah Ulasan</label>
            <input type="number" name="jumlah_ulasan" min="0" placeholder="89"
              value="<?= intval($_POST['jumlah_ulasan'] ?? 0) ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Fasilitas (pisahkan dengan koma)</label>
          <input type="text" name="fasilitas" placeholder="WiFi, AC, Parkir, Sarapan"
            value="<?= htmlspecialchars($_POST['fasilitas'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Deskripsi</label>
          <textarea name="deskripsi"
            placeholder="Ceritakan tentang homestay ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Kutipan Review</label>
          <input type="text" name="review_snippet" placeholder='"Sangat nyaman dan bersih!"'
            value="<?= htmlspecialchars($_POST['review_snippet'] ?? '') ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Status</label>
            <select name="status">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
          <div class="form-group">
            <label>Foto Homestay</label>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
          </div>
        </div>
        <div class="form-group">
          <label class="checkbox-group">
            <input type="checkbox" name="promo" <?= isset($_POST['promo']) ? 'checked' : '' ?>>
            Ada Promo
          </label>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Kode Promo</label>
            <input type="text" name="kode_promo" placeholder="SERUHOTEL"
              value="<?= htmlspecialchars($_POST['kode_promo'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Diskon (%)</label>
            <input type="number" name="diskon" min="0" max="100" placeholder="5"
              value="<?= intval($_POST['diskon'] ?? 0) ?>">
          </div>
        </div>
        <button type="submit" class="btn-submit">Simpan Homestay</button>
      </form>
    </div>
  </div>
  <script>lucide.createIcons();</script>
</body>

</html>