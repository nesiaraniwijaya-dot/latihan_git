<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'karyawan') { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$stmt_karyawan = $conn->prepare("SELECT id, nama_lengkap FROM karyawan WHERE user_id = ?");
$stmt_karyawan->bind_param("i", $user_id);
$stmt_karyawan->execute();
$karyawan = $stmt_karyawan->get_result()->fetch_assoc();
$karyawan_id = $karyawan['id'];
$tahun_sekarang = date('Y');
$stmt_cuti_tahunan = $conn->prepare("SELECT sisa FROM jatah_cuti WHERE karyawan_id = ? AND jenis_cuti = 'Tahunan' AND tahun_berlaku = ?");
$stmt_cuti_tahunan->bind_param("is", $karyawan_id, $tahun_sekarang);
$stmt_cuti_tahunan->execute();
$sisa_cuti_tahunan = $stmt_cuti_tahunan->get_result()->fetch_assoc()['sisa'] ?? 0;
$stmt_cuti_lustrum = $conn->prepare("SELECT sisa FROM jatah_cuti WHERE karyawan_id = ? AND jenis_cuti = 'Lustrum' AND tanggal_kadaluarsa >= CURDATE()");
$stmt_cuti_lustrum->bind_param("i", $karyawan_id);
$stmt_cuti_lustrum->execute();
$sisa_cuti_lustrum = $stmt_cuti_lustrum->get_result()->fetch_assoc()['sisa'] ?? 0;
$stmt_last_ajuan = $conn->prepare("SELECT jenis_cuti, tanggal_mulai, status_pengajuan FROM pengajuan_cuti WHERE karyawan_id = ? ORDER BY tanggal_pengajuan DESC LIMIT 1");
$stmt_last_ajuan->bind_param("i", $karyawan_id);
$stmt_last_ajuan->execute();
$last_ajuan = $stmt_last_ajuan->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Karyawan</title>
    <link rel="stylesheet" href="assets/css/dashboard_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
       <aside class="sidebar">
            <div class="sidebar-header">
                <img src="assets/img/logo.png" alt="Logo" class="sidebar-logo">
                <h3 class="sidebar-title">Yayasan Purba Danarta</h3>
            </div>
           <nav class="sidebar-nav">
    <ul>
        <li class="active"><a href="karyawan_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="pengajuan_cuti.php"><i class="fas fa-plane-departure"></i> Pengajuan Cuti Pribadi</a></li>
        <li><a href="riwayat_cuti.php"><i class="fas fa-history"></i> Riwayat Cuti Pribadi</a></li>
        <li><a href="pengajuan_cuti_khl.php"><i class="fas fa-umbrella-beach"></i> Pengajuan Cuti KHL</a></li>
        <li><a href="riwayat_cuti_khl.php"><i class="fas fa-list-alt"></i> Riwayat Cuti KHL</a></li>
    </ul>
</nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="welcome-message">
                    <h2>Selamat Datang, <?php echo htmlspecialchars($karyawan['nama_lengkap']); ?>!</h2>
                    <p>Dashboard Karyawan</p>
                </div>
                <a href="logout.php" class="logout-button-dashboard"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </header>
            <section class="summary-cards">
                <div class="card"><h4>Sisa Cuti Tahunan</h4><p class="card-value"><?php echo $sisa_cuti_tahunan; ?> <span>Hari</span></p><div class="card-icon"><i class="fas fa-calendar-alt"></i></div></div>
                <div class="card"><h4>Sisa Cuti Lustrum</h4><p class="card-value"><?php echo $sisa_cuti_lustrum; ?> <span>Hari</span></p><div class="card-icon"><i class="fas fa-star"></i></div></div>
                <div class="card">
                    <h4>Status Pengajuan Terakhir</h4>
                    <?php if ($last_ajuan): ?>
                        <p class="card-text"><strong><?php echo htmlspecialchars($last_ajuan['jenis_cuti']); ?></strong> (<?php echo date('d M Y', strtotime($last_ajuan['tanggal_mulai'])); ?>)</p>
                        <span class="status-badge-ajuan <?php echo strtolower(str_replace(' ', '-', $last_ajuan['status_pengajuan'])); ?>"><?php echo htmlspecialchars($last_ajuan['status_pengajuan']); ?></span>
                    <?php else: ?><p class="card-text">Belum ada pengajuan cuti.</p><?php endif; ?>
                    <div class="card-icon"><i class="fas fa-info-circle"></i></div>
                </div>
            </section>
            <section class="recent-activities"><h3>Aktivitas Terbaru</h3><p>Belum ada aktivitas terbaru.</p></section>
        </main>
    </div>
</body>
</html>