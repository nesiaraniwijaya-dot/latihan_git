<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$nama_pengguna = $_SESSION['user_nama'];

// Query untuk cek status cuti Direksi
$stmt_direksi_cuti = $conn->prepare("SELECT pc.tanggal_mulai, pc.tanggal_selesai FROM pengajuan_cuti pc JOIN karyawan k ON pc.karyawan_id = k.id JOIN users u ON k.user_id = u.id WHERE u.role = 'direksi' AND pc.status_pengajuan = 'Disetujui' AND CURDATE() BETWEEN pc.tanggal_mulai AND pc.tanggal_selesai LIMIT 1");
$stmt_direksi_cuti->execute();
$direksi_on_leave = $stmt_direksi_cuti->get_result()->fetch_assoc();

$stmt_pj = $conn->prepare("SELECT divisi, nama_lengkap FROM karyawan WHERE user_id = ?");
$stmt_pj->bind_param("i", $user_id);
$stmt_pj->execute();
$pj = $stmt_pj->get_result()->fetch_assoc();
$divisi_pj = $pj['divisi'];
$nama_pj = $pj['nama_lengkap'];
$stmt_menunggu = $conn->prepare("SELECT COUNT(pc.id) as total FROM pengajuan_cuti pc JOIN karyawan k ON pc.karyawan_id = k.id JOIN users u ON k.user_id = u.id WHERE k.divisi = ? AND pc.status_pengajuan = 'Menunggu Persetujuan' AND u.role = 'karyawan'");
$stmt_menunggu->bind_param("s", $divisi_pj);
$stmt_menunggu->execute();
$total_menunggu = $stmt_menunggu->get_result()->fetch_assoc()['total'];
$stmt_khl = $conn->prepare("SELECT COUNT(pc.id) as total FROM pengajuan_cuti pc JOIN karyawan k ON pc.karyawan_id = k.id WHERE k.divisi = ? AND pc.jenis_cuti = 'KHL'");
$stmt_khl->bind_param("s", $divisi_pj);
$stmt_khl->execute();
$total_khl = $stmt_khl->get_result()->fetch_assoc()['total'];
$stmt_karyawan = $conn->prepare("SELECT COUNT(k.id) as total FROM karyawan k JOIN users u ON k.user_id = u.id WHERE k.divisi = ? AND u.role = 'karyawan'");
$stmt_karyawan->bind_param("s", $divisi_pj);
$stmt_karyawan->execute();
$total_karyawan = $stmt_karyawan->get_result()->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Dashboard Penanggung Jawab</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><img src="../assets/img/logo.png" alt="Logo" class="sidebar-logo"></div>
            <ul class="sidebar-nav">
                <li class="active"><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-title">Cuti Pribadi</li>
                <li><a href="pengajuan_cuti_pj.php"><i class="fas fa-plane-departure"></i> Ajukan Cuti Pribadi</a></li>
                <li><a href="riwayat_cuti_pribadi.php"><i class="fas fa-history"></i> Riwayat Cuti Pribadi</a></li>
                <li><a href="pengajuan_cuti_khl_pribadi.php"><i class="fas fa-umbrella-beach"></i> Ajukan Cuti KHL</a></li>
                <li><a href="riwayat_cuti_khl_pribadi.php"><i class="fas fa-list-alt"></i> Riwayat Cuti KHL</a></li>
                <li class="nav-title">Manajemen Divisi</li>
                <li><a href="administrasi_cuti.php"><i class="fas fa-book"></i> Administrasi Cuti</a></li>
                <li><a href="riwayat_cuti_karyawan.php"><i class="fas fa-users"></i> Riwayat Cuti Divisi</a></li>
                <li><a href="kalender_cuti.php"><i class="fas fa-calendar-alt"></i> Kalender Cuti Divisi</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1 class="title">Dashboard Penanggung Jawab</h1><p class="subtitle">Welcome back, manage your division efficiently</p></div>
                <div class="user-profile">
                    <i class="fas fa-user-circle icon"></i>
                    <div class="user-info"><strong><?php echo htmlspecialchars($nama_pj); ?></strong><span>Penanggung Jawab Divisi <?php echo htmlspecialchars($divisi_pj); ?></span></div>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            <?php if ($direksi_on_leave): ?>
            <div class="info-box warning" style="display:flex;align-items:center;padding:15px;margin-bottom:25px;border-radius:8px;font-size:0.9rem;background-color:#FFFBEB;color:#B45309;border:1px solid #FDE68A;">
                <i class="fas fa-info-circle" style="font-size:1.2rem;margin-right:12px;"></i>
                <span><strong>PEMBERITAHUAN:</strong> Direksi sedang cuti dari tanggal <?php echo date('d M Y', strtotime($direksi_on_leave['tanggal_mulai'])); ?> hingga <?php echo date('d M Y', strtotime($direksi_on_leave['tanggal_selesai'])); ?>.</span>
            </div>
            <?php endif; ?>
            <section class="summary-cards">
                <div class="stats-card"><a href="administrasi_cuti.php" class="card-title highlight">Menunggu Persetujuan</a><p class="card-value"><?php echo $total_menunggu; ?></p><div class="card-icon highlight"><i class="fas fa-clock"></i></div></div>
                <div class="stats-card"><h4 class="card-title">Pengajuan Cuti KHL</h4><p class="card-value"><?php echo $total_khl; ?></p><div class="card-icon"><i class="fas fa-umbrella-beach"></i></div></div>
                <div class="stats-card"><h4 class="card-title">Total Karyawan Divisi</h4><p class="card-value"><?php echo $total_karyawan; ?></p><div class="card-icon"><i class="fas fa-users"></i></div></div>
            </section>
        </main>
    </div>
</body>
</html>