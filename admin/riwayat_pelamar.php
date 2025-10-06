<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'administrator') { header("Location: ../login.php"); exit(); }
$admin_name = $_SESSION['user_nama'];
$query_riwayat = "SELECT l.id, dp.nama_lengkap, l.posisi_yang_dilamar, l.status, l.tanggal_lamaran FROM lamaran l JOIN data_pelamar dp ON l.user_id = dp.user_id WHERE l.status LIKE '%Gagal%' OR l.status LIKE '%Ditolak%' ORDER BY l.tanggal_lamaran DESC";
$result_riwayat = $conn->query($query_riwayat);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Admin - Riwayat Pelamar</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="container">
        <h1>Riwayat Pelamar</h1>
        <p>Daftar semua pelamar yang tidak lolos tahapan seleksi.</p>
        <div class="nav-links">
            <a href="index.php">Daftar Pelamar Aktif</a>
            <a href="karyawan.php">Daftar Karyawan</a>
            <a href="semua_cuti.php">Daftar Pengajuan Cuti</a>
            <a href="riwayat_pelamar.php" class="active">Riwayat Pelamar</a>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="section">
            <h2>Arsip Pelamar</h2>
            <table>
                <thead><tr><th>ID</th><th>Nama Lengkap</th><th>Posisi Dilamar</th><th>Tanggal Lamar</th><th>Status Terakhir</th></tr></thead>
                <tbody>
                    <?php if ($result_riwayat && $result_riwayat->num_rows > 0): while($row = $result_riwayat->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($row['posisi_yang_dilamar']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['tanggal_lamaran'])); ?></td>
                        <td><span class="status-gagal"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5">Tidak ada data riwayat pelamar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>