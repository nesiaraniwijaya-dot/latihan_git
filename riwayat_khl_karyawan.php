<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$nama_pengguna = $_SESSION['user_nama'];

// 1. Ambil DIVISI dari Penanggung Jawab yang login
$stmt_pj = $conn->prepare("SELECT divisi FROM karyawan WHERE user_id = ?");
$stmt_pj->bind_param("i", $user_id);
$stmt_pj->execute();
$pj_data = $stmt_pj->get_result()->fetch_assoc();
$divisi_pj = $pj_data['divisi'];

// 2. Query yang BENAR: Mencari riwayat Cuti KHL dari semua karyawan di dalam DIVISI tersebut
$stmt_riwayat = $conn->prepare("
    SELECT k.nama_lengkap, pc.nama_proyek, pc.tanggal_khl, pc.status_pengajuan
    FROM pengajuan_cuti pc
    JOIN karyawan k ON pc.karyawan_id = k.id
    WHERE k.divisi = ? AND pc.jenis_cuti = 'KHL'
    ORDER BY pc.tanggal_pengajuan DESC
");
$stmt_riwayat->bind_param("s", $divisi_pj);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Cuti Pribadi</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="dashboard-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/img/logo-lengkap.png" alt="Logo" class="sidebar-logo"> </div>
        <ul class="sidebar-nav">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="nav-title">Cuti Pribadi</li>
            <li><a href="pengajuan_cuti_pj.php"><i class="fas fa-plane-departure"></i> Ajukan Cuti Pribadi</a></li>
            <li class="active"><a href="riwayat_cuti_pribadi.php"><i class="fas fa-history"></i> Riwayat Cuti Pribadi</a></li>
            <li><a href="pengajuan_cuti_khl_pribadi.php"><i class="fas fa-umbrella-beach"></i> Ajukan Cuti KHL</a></li>
            <li><a href="riwayat_cuti_khl_pribadi.php"><i class="fas fa-list-alt"></i> Riwayat Cuti KHL</a></li>
            <li class="nav-title">Manajemen Divisi</li>
            <li><a href="administrasi_cuti.php"><i class="fas fa-book"></i> Administrasi Cuti Karyawan</a></li>
            <li><a href="kalender_cuti.php"><i class="fas fa-calendar-alt"></i> Kalender Cuti Divisi</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <h1 class="title">Riwayat Cuti Pribadi</h1>
                <p class="subtitle">Riwayat pengajuan cuti Anda sebagai Penanggung Jawab.</p>
            </div>
            <div class="user-profile">
                <i class="fas fa-user-circle icon"></i>
                <div class="user-info">
                    <strong><?php echo htmlspecialchars($nama_pj); ?></strong>
                    <span>Penanggung Jawab Divisi <?php echo htmlspecialchars($divisi_pj); ?></span>
                </div>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a> </div>
        </header>
        <section class="content-table card">
            <table>
                <thead><tr><th>Jenis Cuti</th><th>Tanggal</th><th>Status</th></tr></thead>
                <tbody>
                    <?php if ($result_cuti->num_rows > 0): while($row = $result_cuti->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['jenis_cuti']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                        <td><?php echo htmlspecialchars($row['status_pengajuan']); ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="3">Anda belum memiliki riwayat cuti pribadi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>