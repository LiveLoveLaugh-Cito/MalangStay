<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../../user/login.php');
  exit;
}
require_once '../../config/db.php';

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
  $bid = intval($_GET['id']);
  $action = $_GET['action'];
  $newStatus = match ($action) {
    'confirm' => 'confirmed',
    'cancel' => 'cancelled',
    default => null
  };
  if ($newStatus) {
    $upd = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
    $upd->bind_param('si', $newStatus, $bid);
    $upd->execute();
  }
  header('Location: index.php?msg=' . $action);
  exit;
}

$bookings = $conn->query("
    SELECT b.*, u.username, h.nama AS homestay_nama
    FROM booking b
    JOIN users u ON b.user_id = u.id
    JOIN homestay h ON b.homestay_id = h.id
    ORDER BY b.created_at DESC
");
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Booking — MalangStay</title>
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
    }

    .wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .top-bar h1 {
      font-size: 1.4rem;
      font-weight: 600;
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

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.85rem;
      background: var(--dark3);
      border-radius: 12px;
      overflow: hidden;
    }

    th,
    td {
      padding: 0.9rem 1rem;
      border-bottom: 1px solid rgba(139, 158, 90, 0.08);
    }

    th {
      color: var(--gray);
      font-weight: 500;
      background: rgba(139, 158, 90, 0.05);
    }

    td {
      color: rgba(245, 243, 237, 0.85);
    }

    .actions-cell {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
    }

    .btn-action {
      border: none;
      padding: 5px 10px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.78rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 3px;
      transition: 0.2s;
      text-decoration: none;
    }

    .btn-confirm {
      background: var(--accent-dim);
      color: var(--accent-light);
      border: 1px solid rgba(139, 158, 90, 0.2);
    }

    .btn-confirm:hover {
      background: var(--accent);
      color: white;
    }

    .btn-cancel {
      background: rgba(232, 114, 75, 0.1);
      color: #e8724b;
      border: 1px solid rgba(232, 114, 75, 0.2);
    }

    .btn-cancel:hover {
      background: #e8724b;
      color: white;
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

    .alert {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      font-size: 0.85rem;
    }

    .alert-success {
      background: rgba(139, 158, 90, 0.15);
      border: 1px solid rgba(139, 158, 90, 0.3);
      color: var(--accent-light);
    }
  </style>
</head>

<body>
  <div class="wrap">
    <a href="../dashboard.php" class="back-link"><i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
      Kembali ke Dashboard</a>

    <?php if ($msg === 'confirm'): ?>
      <div class="alert alert-success">Booking berhasil dikonfirmasi.</div>
    <?php elseif ($msg === 'cancel'): ?>
      <div class="alert alert-success">Booking dibatalkan.</div>
    <?php endif; ?>

    <div class="top-bar">
      <h1>Kelola Booking</h1>
    </div>

    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Tamu</th>
            <th>Homestay</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Tamu</th>
            <th>Total</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($bookings && $bookings->num_rows > 0): ?>
            <?php while ($b = $bookings->fetch_assoc()): ?>
              <tr>
                <td>#<?= $b['id'] ?></td>
                <td><?= htmlspecialchars($b['username']) ?></td>
                <td><?= htmlspecialchars($b['homestay_nama']) ?></td>
                <td><?= date('d M Y', strtotime($b['tanggal_checkin'])) ?></td>
                <td><?= date('d M Y', strtotime($b['tanggal_checkout'])) ?></td>
                <td><?= $b['jumlah_tamu'] ?> orang</td>
                <td>Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></td>
                <td><span class="status status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                <td>
                  <div class="actions-cell">
                    <?php if ($b['status'] === 'pending'): ?>
                      <a href="?action=confirm&id=<?= $b['id'] ?>" class="btn-action btn-confirm">✓ Konfirmasi</a>
                      <a href="?action=cancel&id=<?= $b['id'] ?>" class="btn-action btn-cancel"
                        onclick="return confirm('Batalkan booking ini?')">✕ Batalkan</a>
                    <?php else: ?>
                      <span style="color:var(--gray);font-size:0.78rem;">—</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" style="text-align:center;color:var(--gray);padding:3rem;">Belum ada booking.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <script>lucide.createIcons();</script>
</body>

</html>