<?php
session_start(); require_once '../config/database.php'; if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'direksi') { header("Location: ../login.php"); exit(); }
// Query untuk mengambil SEMUA riwayat cuti
$result_cuti = $conn->query("SELECT k.nama_lengkap, k.divisi, pc.* FROM pengajuan_cuti pc JOIN karyawan k ON pc.karyawan_id = k.id ORDER BY pc.tanggal_pengajuan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Riwayat Cuti Seluruh Karyawan</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><img src="../assets/img/logo.png" alt="Logo"><h3 class="sidebar-title">Yayasan Purba Danarta</h3></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="pengajuan_cuti_direksi.php"><i class="fas fa-plane-departure"></i> Ajukan Cuti</a></li>
                <li class="nav-title">Pemantauan</li>
                <li class="active"><a href="riwayat_cuti_semua.php"><i class="fas fa-history"></i> Riwayat Cuti Karyawan</a></li>
                <li><a href="data_karyawan_semua.php"><i class="fas fa-users"></i> Data Semua Karyawan</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header"><div class="header-left"><h1 class="title">Riwayat Cuti Seluruh Karyawan</h1></div></header>
            <section class="content-table card">
                <table>
                    <thead><tr><th>Nama Karyawan</th><th>Divisi</th><th>Jenis Cuti</th><th>Tanggal</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if ($result_cuti->num_rows > 0): while($row = $result_cuti->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                            <td><?php echo htmlspecialchars($row['jenis_cuti']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                            <td><?php echo htmlspecialchars($row['status_pengajuan']); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5">Belum ada riwayat cuti.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>