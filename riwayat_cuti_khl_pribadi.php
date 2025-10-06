<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$nama_pengguna = $_SESSION['user_nama'];

$stmt_karyawan = $conn->prepare("SELECT id, divisi FROM karyawan WHERE user_id = ?");
$stmt_karyawan->bind_param("i", $user_id);
$stmt_karyawan->execute();
$pj_data = $stmt_karyawan->get_result()->fetch_assoc();
$karyawan_id = $pj_data['id'];
$divisi_pj = $pj_data['divisi'];

$stmt_riwayat = $conn->prepare("SELECT * FROM pengajuan_cuti WHERE karyawan_id = ? AND jenis_cuti = 'KHL' ORDER BY tanggal_pengajuan DESC");
$stmt_riwayat->bind_param("i", $karyawan_id);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Riwayat Cuti KHL Pribadi</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><img src="../assets/img/logo-lengkap.png" alt="Logo" class="sidebar-logo"></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-title">Cuti Pribadi</li>
                <li><a href="pengajuan_cuti_pj.php"><i class="fas fa-plane-departure"></i> Ajukan Cuti Pribadi</a></li>
                <li><a href="riwayat_cuti_pribadi.php"><i class="fas fa-history"></i> Riwayat Cuti Pribadi</a></li>
                <li><a href="pengajuan_cuti_khl_pribadi.php"><i class="fas fa-umbrella-beach"></i> Ajukan Cuti KHL</a></li>
                <li class="active"><a href="riwayat_cuti_khl_pribadi.php"><i class="fas fa-list-alt"></i> Riwayat Cuti KHL</a></li>
                <li class="nav-title">Manajemen Divisi</li>
                <li><a href="administrasi_cuti.php"><i class="fas fa-book"></i> Administrasi Cuti Karyawan</a></li>
                <li><a href="kalender_cuti.php"><i class="fas fa-calendar-alt"></i> Kalender Cuti Divisi</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1 class="title">Riwayat Cuti KHL Pribadi Anda</h1></div>
                <div class="user-profile">
                    <i class="fas fa-user-circle icon"></i>
                    <div class="user-info"><strong><?php echo htmlspecialchars($nama_pengguna); ?></strong><span>Penanggung Jawab Divisi <?php echo htmlspecialchars($divisi_pj); ?></span></div>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            <section class="content-table card">
                <table>
                    <thead><tr><th>Nama Proyek</th><th>Tanggal KHL</th><th>Tanggal Cuti</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if ($result_riwayat->num_rows > 0): while($row = $result_riwayat->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_proyek']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_khl'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                            <td><span class="status-badge-ajuan <?php echo strtolower(str_replace(' ', '-', $row['status_pengajuan'])); ?>"><?php echo htmlspecialchars($row['status_pengajuan']); ?></span></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4">Anda belum memiliki riwayat Cuti KHL.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>