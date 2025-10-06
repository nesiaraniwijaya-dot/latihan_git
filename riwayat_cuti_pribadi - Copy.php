<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$stmt_pj = $conn->prepare("SELECT id, divisi FROM karyawan WHERE user_id = ?");
$stmt_pj->bind_param("i", $user_id);
$stmt_pj->execute();
$pj_data = $stmt_pj->get_result()->fetch_assoc();
$karyawan_id = $pj_data['id'];
$divisi_pj = $pj_data['divisi']; // Sekarang variabel $divisi_pj sudah ada

// --- PERBAIKAN QUERY DI SINI ---
// Query sekarang mengambil riwayat cuti PRIBADI milik Penanggung Jawab
$stmt_cuti = $conn->prepare("
    SELECT jenis_cuti, tanggal_mulai, status_pengajuan 
    FROM pengajuan_cuti 
    WHERE karyawan_id = ? AND jenis_cuti != 'KHL' 
    ORDER BY tanggal_pengajuan DESC
");
$stmt_cuti->bind_param("i", $karyawan_id);
$stmt_cuti->execute();
$result_cuti = $stmt_cuti->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Riwayat Cuti Pribadi</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><img src="../assets/img/logo.png" alt="Logo" class="sidebar-logo"><h3 class="sidebar-title">Yayasan Purba Danarta</h3></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-title">Penanganan Cuti</li>
                <li><a href="pengajuan_cuti_pj.php"><i class="fas fa-plane-departure"></i> Ajukan Cuti Pribadi</a></li>
                <li class="active"><a href="riwayat_cuti_pribadi.php"><i class="fas fa-history"></i> Riwayat Cuti Pribadi</a></li>
                <li><a href="administrasi_cuti.php"><i class="fas fa-book"></i> Administrasi Cuti</a></li>
                <li><a href="kalender_cuti.php"><i class="fas fa-calendar-alt"></i> Kalender Cuti</a></li>
                <li><a href="riwayat_cuti_karyawan"><i class="fas fa-history"></i> Riwayat Cuti Karyawan</a></li>
                <li class="nav-title">Penanganan KHL</li>
                <li><a href="persetujuan_cuti_khl.php"><i class="fas fa-check-square"></i> Ajukan Cuti KHL</a></li>
                <li><a href="riwayat_cuti_khl.php"><i class="fas fa-history"></i> Riwayat Cuti KHL Pribadi</a></li>
                <li><a href="administrasi_khl.php"><i class="fas fa-book-open"></i> Administrasi KHL</a></li>
                <li><a href="kalender_khl.php"><i class="fas fa-calendar-week"></i> Kalender KHL</a></li>
                <li><a href="riwayat_khl_karyawan"><i class="fas fa-history"></i> Riwayat Cuti KHL Karyawan</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1 class="title">Riwayat Cuti Pribadi</h1><p class="subtitle">Divisi <?php echo htmlspecialchars($divisi_pj); ?></p></div>
                <div class="user-profile">
        <i class="fas fa-user-circle icon"></i>
        <div class="user-info">
            <span>Penanggung Jawab Divisi <?php echo htmlspecialchars($divisi_pj); ?></span>
        </div>
        <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
            </header>
            <section class="content-table card">
                <table>
                    <thead>
                        <tr>
                            <th>Jenis Cuti</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
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