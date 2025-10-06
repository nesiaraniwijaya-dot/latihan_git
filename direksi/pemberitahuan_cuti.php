<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'direksi') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$nama_pengguna = $_SESSION['user_nama'];
$success_message = '';
$error_message = '';

// Mengambil ID Karyawan dari Direksi yang login (sekarang datanya sudah ada)
$stmt_karyawan = $conn->prepare("SELECT id FROM karyawan WHERE user_id = ?");
$stmt_karyawan->bind_param("i", $user_id);
$stmt_karyawan->execute();
$karyawan = $stmt_karyawan->get_result()->fetch_assoc();

if (!$karyawan) {
    die("Error: Data karyawan untuk Direksi tidak ditemukan. Silakan jalankan SQL dari Langkah 1.");
}
$karyawan_id = $karyawan['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_cuti = $_POST['jenis_cuti'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $alasan = $_POST['alasan'];
    
    $tgl1 = new DateTime($tanggal_mulai);
    $tgl2 = new DateTime($tanggal_selesai);
    $jumlah_hari = $tgl2->diff($tgl1)->days + 1;
    
    $stmt_insert = $conn->prepare("INSERT INTO pengajuan_cuti (karyawan_id, jenis_cuti, tanggal_mulai, tanggal_selesai, jumlah_hari, alasan, status_pengajuan, diproses_oleh) VALUES (?, ?, ?, ?, ?, ?, 'Disetujui', ?)");
    $stmt_insert->bind_param("isssisi", $karyawan_id, $jenis_cuti, $tanggal_mulai, $tanggal_selesai, $jumlah_hari, $alasan, $user_id);
    
    if ($stmt_insert->execute()) {
        $success_message = "Pemberitahuan cuti Anda telah berhasil dicatat.";
    } else {
        $error_message = "Terjadi kesalahan saat menyimpan data: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Pemberitahuan Cuti Direksi</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><img src="../assets/img/logo-lengkap.png" alt="Logo" class="sidebar-logo"></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="active"><a href="pemberitahuan_cuti.php"><i class="fas fa-paper-plane"></i> Pemberitahuan Cuti</a></li>
                <li class="nav-title">Pemantauan</li>
                <li><a href="riwayat_cuti_semua.php"><i class="fas fa-history"></i> Riwayat Cuti Karyawan</a></li>
                <li><a href="data_karyawan_semua.php"><i class="fas fa-users"></i> Data Semua Karyawan</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1 class="title">Pemberitahuan Cuti (Direksi)</h1><p class="subtitle">Cuti yang Anda ajukan akan otomatis disetujui dan dicatat.</p></div>
                <div class="user-profile">
                    <i class="fas fa-user-shield icon"></i>
                    <div class="user-info"><strong><?php echo htmlspecialchars($nama_pengguna); ?></strong><span>Direksi</span></div>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            <section class="content-form card">
                 </section>
        </main>
    </div>
</body>
</html>