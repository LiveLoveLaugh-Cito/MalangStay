<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../user/login.php');
  exit;
}
require_once '../config/db.php';

// Stats
$totalHomestay = $conn->query("SELECT COUNT(*) as c FROM homestay")->fetch_assoc()['c'];
$totalBooking = $conn->query("SELECT COUNT(*) as c FROM booking")->fetch_assoc()['c'];
$pendingBooking = $conn->query("SELECT COUNT(*) as c FROM booking WHERE status='pending'")->fetch_assoc()['c'];
$totalUser = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];

// Recent bookings
$recentBookings = $conn->query("
    SELECT b.*, u.username, h.nama AS homestay_nama
    FROM booking b
    JOIN users u ON b.user_id = u.id
    JOIN homestay h ON b.homestay_id = h.id
    ORDER BY b.created_at DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — MalangStay</title>
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
      --dark2: #1a1b14;
      --dark3: #1e1f17;
      --warm2: #1f2116;
      --white: #f5f3ed;
      --gray: #8a8d6a;
      --gold: #e8b84b;
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
      overflow-x: hidden;
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }

    /* SIDEBAR */
    .sidebar {
      width: 260px;
      background: var(--dark3);
      border-right: 1px solid rgba(139, 158, 90, 0.12);
      padding: 1.5rem 1.2rem;
      display: flex;
      flex-direction: column;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
    }

    .sidebar-brand {
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid rgba(139, 158, 90, 0.1);
    }

    .sidebar-brand img {
      height: 50px;
    }

    .sidebar-menu {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex: 1;
    }

    .sidebar-menu li a {
      display: flex;
      align-items: center;
      gap: 0.8rem;
      padding: 0.7rem 0.9rem;
      color: rgba(245, 243, 237, 0.6);
      text-decoration: none;
      font-size: 0.87rem;
      font-weight: 500;
      border-radius: 8px;
      transition: all 0.2s;
    }

    .sidebar-menu li a svg {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
    }

    .sidebar-menu li.active a,
    .sidebar-menu li a:hover {
      background: var(--accent-dim);
      color: var(--accent-light);
    }

    .sidebar-menu li.logout {
      margin-top: auto;
      padding-top: 1rem;
    }

    .sidebar-menu li.logout a:hover {
      background: rgba(232, 114, 75, 0.1);
      color: #e8724b;
    }

    /* MAIN */
    .main-content {
      flex: 1;
      margin-left: 260px;
      padding: 2rem;
      background: radial-gradient(circle at top right, var(--warm2), var(--dark));
    }

    .top-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1.2rem;
      border-bottom: 1px solid rgba(139, 158, 90, 0.08);
    }

    .top-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
    }

    .top-header p {
      color: var(--gray);
      font-size: 0.85rem;
    }

    /* STAT CARDS */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: var(--dark3);
      border: 1px solid rgba(139, 158, 90, 0.1);
      border-radius: 12px;
      padding: 1.2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .stat-icon {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .stat-icon.green {
      background: var(--accent-dim);
    }

    .stat-icon.gold {
      background: rgba(232, 184, 75, 0.12);
    }

    .stat-icon.red {
      background: rgba(232, 114, 75, 0.1);
    }

    .stat-icon.blue {
      background: rgba(75, 130, 232, 0.1);
    }

    .stat-icon svg {
      width: 20px;
      height: 20px;
    }

    .stat-icon.green svg {
      color: var(--accent-light);
    }

    .stat-icon.gold svg {
      color: var(--gold);
    }

    .stat-icon.red svg {
      color: #e8724b;
    }

    .stat-icon.blue svg {
      color: #4b82e8;
    }

    .stat-info .num {
      font-size: 1.6rem;
      font-weight: 700;
    }

    .stat-info .lbl {
      font-size: 0.78rem;
      color: var(--gray);
      margin-top: 2px;
    }

    /* TABLE */
    .table-section {
      background: var(--dark3);
      border: 1px solid rgba(139, 158, 90, 0.1);
      border-radius: 14px;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }

    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.2rem;
    }

    .table-header h2 {
      font-size: 1rem;
      font-weight: 600;
    }

    .btn-add {
      background: var(--accent);
      color: white;
      border: none;
      padding: 0.55rem 1.1rem;
      border-radius: 6px;
      font-weight: 500;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.83rem;
      transition: 0.2s;
      text-decoration: none;
    }

    .btn-add:hover {
      background: var(--accent-dark);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.85rem;
    }

    th,
    td {
      padding: 0.85rem;
      border-bottom: 1px solid rgba(139, 158, 90, 0.06);
    }

    th {
      color: var(--gray);
      font-weight: 500;
    }

    td {
      color: rgba(245, 243, 237, 0.8);
    }

    .status {
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 0.73rem;
      font-weight: 600;
    }

    .status-pending {
      background: rgba(232, 184, 75, 0.15);
      color: var(--gold);
    }

    .status-confirmed {
      background: var(--accent-dim);
      color: var(--accent-light);
    }

    .status-cancelled {
      background: rgba(232, 114, 75, 0.1);
      color: #e8724b;
    }

    .actions-cell {
      display: flex;
      gap: 6px;
    }

    .btn-action {
      border: none;
      padding: 5px 10px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.78rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 3px;
      transition: 0.2s;
      text-decoration: none;
    }

    .btn-edit {
      background: rgba(232, 184, 75, 0.1);
      color: var(--gold);
      border: 1px solid rgba(232, 184, 75, 0.2);
    }

    .btn-edit:hover {
      background: var(--gold);
      color: var(--dark);
    }

    .btn-delete {
      background: rgba(232, 114, 75, 0.1);
      color: #e8724b;
      border: 1px solid rgba(232, 114, 75, 0.2);
    }

    .btn-delete:hover {
      background: #e8724b;
      color: white;
    }

    @media(max-width:992px) {
      .sidebar {
        width: 70px;
        padding: 1rem 0.4rem;
      }

      .sidebar-brand img,
      .sidebar-menu li a span {
        display: none;
      }

      .sidebar-menu li a {
        justify-content: center;
        padding: 0.8rem;
      }

      .main-content {
        margin-left: 70px;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
  </style>
</head>

<body>
  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <img src="../assets/MalangStay_logo_cropped.png" alt="MalangStay">
      </div>
      <ul class="sidebar-menu">
        <li class="active"><a href="dashboard.php"><i data-lucide="layout-dashboard"></i> <span>Dashboard</span></a>
        </li>
        <li><a href="homestay/index.php"><i data-lucide="home"></i> <span>Kelola Homestay</span></a></li>
        <li><a href="booking/index.php"><i data-lucide="calendar"></i> <span>Kelola Booking</span></a></li>
        <li><a href="daftar-properti.php"><i data-lucide="list"></i> <span>Lihat Listing</span></a></li>
        <li class="logout"><a href="../user/logout.php"><i data-lucide="log-out"></i> <span>Keluar</span></a></li>
      </ul>
    </aside>

    <main class="main-content">
      <header class="top-header">
        <div>
          <h1>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?></h1>
          <p>Ringkasan aktivitas MalangStay hari ini.</p>
        </div>
      </header>

      <!-- STATS: ringkasan metrik utama admin buat preview cepat -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon green"><i data-lucide="home"></i></div>
          <div class="stat-info">
            <div class="num"><?= $totalHomestay ?></div>
            <div class="lbl">Total Homestay</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon gold"><i data-lucide="calendar"></i></div>
          <div class="stat-info">
            <div class="num"><?= $totalBooking ?></div>
            <div class="lbl">Total Booking</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red"><i data-lucide="clock"></i></div>
          <div class="stat-info">
            <div class="num"><?= $pendingBooking ?></div>
            <div class="lbl">Booking Pending</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue"><i data-lucide="users"></i></div>
          <div class="stat-info">
            <div class="num"><?= $totalUser ?></div>
            <div class="lbl">Total User</div>
          </div>
        </div>
      </div>

      <!-- RECENT BOOKINGS: tabel booking terbaru buat admin review langsung -->
      <div class="table-section">
        <div class="table-header">
          <h2>Booking Terbaru</h2>
          <a href="booking/index.php" class="btn-add">Lihat Semua</a>
        </div>
        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>Tamu</th>
                <th>Homestay</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($recentBookings && $recentBookings->num_rows > 0): ?>
                <?php while ($b = $recentBookings->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($b['username']) ?></td>
                    <td><?= htmlspecialchars($b['homestay_nama']) ?></td>
                    <td><?= date('d M Y', strtotime($b['tanggal_checkin'])) ?></td>
                    <td><?= date('d M Y', strtotime($b['tanggal_checkout'])) ?></td>
                    <td>Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></td>
                    <td><span class="status status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                    <td>
                      <div class="actions-cell">
                        <a href="booking/index.php?action=confirm&id=<?= $b['id'] ?>"
                          class="btn-action btn-edit">✓Konfirmasi</a>
                        <a href="booking/index.php?action=cancel&id=<?= $b['id'] ?>" class="btn-action btn-delete" 
                            onclick="return confirm('Batalkan booking ini?')">✕ Batalkan</a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" style="text-align:center;color:var(--gray);padding:2rem;">Belum ada booking.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- QUICK LINK TO HOMESTAY: shortcut section buat tambah / kelola homestay cepat -->
      <div class="table-section">
        <div class="table-header">
          <h2>Kelola Homestay</h2>
          <a href="homestay/create.php" class="btn-add">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> Tambah Homestay
          </a>
        </div>
        <p style="color:var(--gray);font-size:0.85rem;">
          Tambah, edit, atau hapus data homestay. →
          <a href="homestay/index.php" style="color:var(--accent-light);">Buka halaman kelola homestay</a>
        </p>
      </div>
    </main>
  </div>
  <script>lucide.createIcons();</script>
</body>

</html>