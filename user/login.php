<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header('Location: ../malangstay.php');
  exit;
}

require_once '../config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username && $password) {
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];

      if ($user['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
      } else {
        header('Location: ../malangstay.php');
      }
      exit;
    } else {
      $error = 'Username atau password salah.';
    }
  } else {
    $error = 'Mohon isi semua field.';
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — MalangStay</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
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
      <li><a href="login.php" class="btn-login">Sign in</a></li>
      <li><a href="register.php" class="btn-login">Sign Up</a></li>
    </ul>
  </nav>

  <section class="login-container">
    <div class="card">
      <div class="right">
        <img src="../assets/MalangStay_logo_cropped.png" alt="MalangStay"
          style="width:80px;display:block;margin:0 auto 16px;filter:brightness(0) invert(1);">
        <h2>Sign in</h2>
        <?php if ($error): ?>
          <div style="background:rgba(232,114,75,0.15);border:1px solid rgba(232,114,75,0.4);color:#e8724b;
                    padding:0.75rem 1rem;border-radius:8px;font-size:0.85rem;margin-bottom:1rem;">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <form class="form" method="POST" action="login.php">
          <div class="input-group">
            <input type="text" name="username" placeholder="Username" required
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
          <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
          </div>
          <a href="#" class="forgot">Forgot password?</a>
          <button type="submit" class="btn-login">Sign in</button>
          <div class="or-divider">
            <div class="or-line"></div>
            <span class="or-text">Don't have an account?</span>
            <div class="or-line"></div>
          </div>
          <a href="register.php" class="btn-register">Register</a>
        </form>
      </div>
    </div>
  </section>
  <script>lucide.createIcons();</script>
</body>

</html>