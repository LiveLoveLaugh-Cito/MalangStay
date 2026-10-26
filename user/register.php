<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../malangstay.php');
    exit;
}

require_once '../config/db.php';
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$username || !$email || !$password || !$confirm) {
        $error = 'Mohon isi semua field.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi password tidak sama.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // Cek duplikat username
        $chk = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $chk->bind_param('ss', $username, $email);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = 'Username atau email sudah digunakan.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $ins->bind_param('sss', $username, $email, $hash);
            if ($ins->execute()) {
                $success = 'Akun berhasil dibuat! Silakan login.';
            } else {
                $error = 'Gagal membuat akun. Coba lagi.';
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
  <title>Daftar — MalangStay</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="login.css">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
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
    <li><a href="../admin/daftar-properti.php">Daftar Penginapan</a></li>
    <li><a href="login.php" class="btn-login">Sign In</a></li>
    <li><a href="register.php" class="btn-login">Sign Up</a></li>
  </ul>
</nav>

<section class="login-container">
  <div class="card">
    <div class="right">
      <img src="../assets/MalangStay_logo_cropped.png" alt="MalangStay"
           style="width:80px;display:block;margin:0 auto 16px;filter:brightness(0) invert(1);">
      <h2>Sign Up</h2>
      <?php if ($error): ?>
        <div style="background:rgba(232,114,75,0.15);border:1px solid rgba(232,114,75,0.4);color:#e8724b;
                    padding:0.75rem 1rem;border-radius:8px;font-size:0.85rem;margin-bottom:1rem;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div style="background:rgba(139,158,90,0.15);border:1px solid rgba(139,158,90,0.4);color:#b5c47e;
                    padding:0.75rem 1rem;border-radius:8px;font-size:0.85rem;margin-bottom:1rem;">
          <?= htmlspecialchars($success) ?>
          <a href="login.php" style="color:#b5c47e;font-weight:600;"> Login sekarang </a>
        </div>
      <?php endif; ?>
      <form class="form" method="POST" action="register.php">
        <div class="input-group">
          <input type="text" name="username" placeholder="Username" required
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="input-group">
          <input type="email" name="email" placeholder="Email" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Password (min. 6 karakter)" required>
        </div>
        <div class="input-group">
          <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
        </div>
        <button type="submit" class="btn-login">Daftar Sekarang</button>
        <div class="or-divider">
          <div class="or-line"></div>
          <span class="or-text">Sudah punya akun?</span>
          <div class="or-line"></div>
        </div>
        <a href="login.php" class="btn-register">Sign In</a>
      </form>
    </div>
  </div>
</section>
<script>lucide.createIcons();</script>
</body>
</html>
