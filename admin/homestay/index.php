<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../../user/login.php');
  exit;
}
require_once '../../config/db.php';

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
  $del = $conn->prepare("DELETE FROM homestay WHERE id = ?");
  $del->bind_param('i', $_GET['id']);
  $del->execute();
  header('Location: index.php?msg=deleted');
  exit;
}

$homestays = $conn->query("SELECT * FROM homestay ORDER BY created_at DESC");
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Homestay — MalangStay</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="../admin.css">
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

    .btn-add {
      background: var(--accent);
      color: white;
      border: none;
      padding: 0.55rem 1.1rem;
      border-radius: 6px;
      font-weight: 500;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.83rem;
      transition: 0.2s;
      text-decoration: none;
    }

    .btn-add:hover {
      background: var(--accent-dark);
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

    .badge-aktif {
      background: var(--accent-dim);
      color: var(--accent-light);
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 0.73rem;
    }

    .badge-nonaktif {
      background: rgba(232, 114, 75, 0.1);
      color: #e8724b;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 0.73rem;
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

    .thumb {
      width: 60px;
      height: 44px;
      object-fit: cover;
      border-radius: 6px;
    }
  </style>
</head>

<body>
  <div class="wrap">
    <a href="../dashboard.php" class="back-link"><i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
      Kembali ke Dashboard</a>

    <?php if ($msg === 'deleted'): ?>
      <div class="alert alert-success"> Homestay berhasil dihapus.</div>
    <?php elseif ($msg === 'saved'): ?>
      <div class="alert alert-success"> Homestay berhasil disimpan.</div>
    <?php elseif ($msg === 'updated'): ?>
      <div class="alert alert-success"> Homestay berhasil diupdate.</div>
    <?php endif; ?>

    <div class="top-bar">
      <h1>Kelola Homestay</h1>
      <a href="create.php" class="btn-add">
        <i data-lucide="plus" style="width:15px;height:15px;"></i> Tambah Homestay
      </a>
    </div>

    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>Foto</th>
            <th>Nama</th>
            <th>Lokasi</th>
            <th>Harga</th>
            <th>Rating</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($homestays && $homestays->num_rows > 0): ?>
            <?php while ($h = $homestays->fetch_assoc()): ?>
              <tr>
                <td>
                  <?php if ($h['foto']): ?>
                    <img src="../../uploads/homestay/<?= htmlspecialchars($h['foto']) ?>" class="thumb" alt="">
                  <?php else: ?>
                    <div
                      style="width:60px;height:44px;background:rgba(255,255,255,0.05);border-radius:6px;display:flex;align-items:center;justify-content:center;">
                      <i data-lucide="image" style="width:16px;opacity:0.3;"></i>
                    </div>
                  <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($h['nama']) ?></strong></td>
                <td><?= htmlspecialchars($h['lokasi']) ?></td>
                <td>Rp <?= number_format($h['harga'], 0, ',', '.') ?></td>
                <td><?= $h['rating'] ?></td>
                <td>
                  <?php if ($h['status'] === 'aktif'): ?>
                    <span class="badge-aktif">Aktif</span>
                  <?php else: ?>
                    <span class="badge-nonaktif">Nonaktif</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="actions-cell">
                    <a href="edit.php?id=<?= $h['id'] ?>" class="btn-action btn-edit">
                      <i data-lucide="pencil" style="width:12px;height:12px;"></i> Edit
                    </a>
                    <a href="index.php?action=delete&id=<?= $h['id'] ?>" class="btn-action btn-delete"
                      onclick="return confirm('Hapus homestay ini?')">
                      <i data-lucide="trash-2" style="width:12px;height:12px;"></i> Hapus
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align:center;color:var(--gray);padding:3rem;">
                Belum ada homestay. <a href="create.php" style="color:var(--accent-light);">Tambah sekarang →</a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <script>lucide.createIcons();</script>
</body>

</html>