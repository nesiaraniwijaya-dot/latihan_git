<?php
session_start(); require_once '../config/database.php'; if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'direksi') { header("Location: ../login.php"); exit(); }
// Query untuk mengambil SEMUA data karyawan
$result_karyawan = $conn->query("SELECT k.*, u.role FROM karyawan k JOIN users u ON k.user_id = u.id ORDER BY k.nama_lengkap ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Seluruh Karyawan</title>
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
                <li><a href="riwayat_cuti_semua.php"><i class="fas fa-history"></i> Riwayat Cuti Karyawan</a></li>
                <li class="active"><a href="data_karyawan_semua.php"><i class="fas fa-users"></i> Data Semua Karyawan</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header"><div class="header-left"><h1 class="title">Data Seluruh Karyawan</h1></div></header>
            <section class="content-table card">
                <table>
                    <thead><tr><th>Nama Lengkap</th><th>Divisi</th><th>Peran (Role)</th><th>Tanggal Bergabung</th></tr></thead>
                    <tbody>
                        <?php if ($result_karyawan->num_rows > 0): while($row = $result_karyawan->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_bergabung'])); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4">Belum ada data karyawan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>