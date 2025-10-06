<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'direksi') { header("Location: ../login.php"); exit(); }
$nama_pengguna = $_SESSION['user_nama'];

// Query untuk kartu "Persetujuan Cuti PJ"
$stmt_menunggu = $conn->prepare("SELECT COUNT(pc.id) as total FROM pengajuan_cuti pc JOIN users u ON (SELECT user_id FROM karyawan WHERE id=pc.karyawan_id) = u.id WHERE u.role = 'penanggung_jawab' AND pc.status_pengajuan = 'Menunggu Persetujuan'");
$stmt_menunggu->execute();
$total_menunggu = $stmt_menunggu->get_result()->fetch_assoc()['total'];

// Query untuk kartu "Total Karyawan"
$total_karyawan = $conn->query("SELECT COUNT(id) as total FROM karyawan")->fetch_assoc()['total'];

// Query untuk kartu "Karyawan Cuti Hari Ini"
$stmt_cuti_hari_ini = $conn->prepare("SELECT COUNT(id) as total FROM pengajuan_cuti WHERE status_pengajuan = 'Disetujui' AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai");
$stmt_cuti_hari_ini->execute();
$total_cuti_hari_ini = $stmt_cuti_hari_ini->get_result()->fetch_assoc()['total'];

// Query untuk kartu "Persetujuan Cuti PJ"
$stmt_menunggu = $conn->prepare("SELECT COUNT(pc.id) as total FROM pengajuan_cuti pc JOIN users u ON (SELECT user_id FROM karyawan WHERE id=pc.karyawan_id) = u.id WHERE u.role = 'penanggung_jawab' AND pc.status_pengajuan = 'Menunggu Persetujuan'");
$stmt_menunggu->execute();
$total_menunggu = $stmt_menunggu->get_result()->fetch_assoc()['total'];

// Query untuk kartu "Total Karyawan"
$total_karyawan = $conn->query("SELECT COUNT(id) as total FROM karyawan")->fetch_assoc()['total'];

// Query untuk kartu "Karyawan Cuti Hari Ini"
$stmt_cuti_hari_ini = $conn->prepare("SELECT COUNT(id) as total FROM pengajuan_cuti WHERE status_pengajuan = 'Disetujui' AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai");
$stmt_cuti_hari_ini->execute();
$total_cuti_hari_ini = $stmt_cuti_hari_ini->get_result()->fetch_assoc()['total'];

// Query untuk tabel persetujuan
$stmt_list_cuti = $conn->prepare("SELECT pc.id, k.nama_lengkap, k.divisi, pc.jenis_cuti, pc.tanggal_mulai, pc.tanggal_selesai FROM pengajuan_cuti pc JOIN karyawan k ON pc.karyawan_id = k.id JOIN users u ON k.user_id = u.id WHERE u.role = 'penanggung_jawab' AND pc.status_pengajuan = 'Menunggu Persetujuan'");
$stmt_list_cuti->execute();
$result_cuti = $stmt_list_cuti->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Dashboard Direksi</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><img src="../assets/img/logo.png" alt="Logo"><h3 class="sidebar-title">Yayasan Purba Danarta</h3></div>
            <ul class="sidebar-nav">
                <li class="active"><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="pengajuan_cuti_direksi.php"><i class="fas fa-plane-departure"></i> Ajukan Cuti</a></li>
                <li class="nav-title">Pemantauan</li>
                <li><a href="riwayat_cuti_semua.php"><i class="fas fa-history"></i> Riwayat Cuti Karyawan</a></li>
                <li><a href="data_karyawan_semua.php"><i class="fas fa-users"></i> Data Semua Karyawan</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1 class="title">Dashboard Direksi</h1><p class="subtitle">Welcome back, manage your organization efficiently</p></div>
                <div class="user-profile"><i class="fas fa-user-shield icon"></i><div class="user-info"><strong><?php echo htmlspecialchars($nama_pengguna); ?></strong><span>Direksi</span></div></div>
            </header>
            <section class="summary-cards">
                <div class="stats-card"><h4 class="card-title highlight">Persetujuan Cuti PJ</h4><p class="card-value"><?php echo $total_menunggu; ?></p><div class="card-icon highlight"><i class="fas fa-user-clock"></i></div></div>
                <div class="stats-card"><h4 class="card-title">Karyawan Cuti Hari Ini</h4><p class="card-value"><?php echo $total_cuti_hari_ini; ?></p><div class="card-icon"><i class="fas fa-calendar-check"></i></div></div>
                <div class="stats-card"><h4 class="card-title">Total Karyawan</h4><p class="card-value"><?php echo $total_karyawan; ?></p><div class="card-icon"><i class="fas fa-users"></i></div></div>
            </section>
            <section class="summary-cards">
    <div class="stats-card">
        <h4 class="card-title highlight">Persetujuan Cuti PJ</h4>
        <p class="card-value"><?php echo $total_menunggu; ?></p>
        <div class="card-icon highlight"><i class="fas fa-user-clock"></i></div>
    </div>
    <div class="stats-card">
        <h4 class="card-title">Karyawan Cuti Hari Ini</h4>
        <p class="card-value"><?php echo $total_cuti_hari_ini; ?></p>
        <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
    </div>
    <div class="stats-card">
        <h4 class="card-title">Total Karyawan</h4>
        <p class="card-value"><?php echo $total_karyawan; ?></p>
        <div class="card-icon"><i class="fas fa-users"></i></div>
    </div>
</section>
            <section class="content-table card">
                <h3>Persetujuan Cuti Penanggung Jawab</h3>
                <table>
                    <thead><tr><th>Nama Penanggung Jawab</th><th>Divisi</th><th>Jenis Cuti</th><th>Tanggal</th><th>Persetujuan</th></tr></thead>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <tbody>
                        <?php if ($result_cuti->num_rows > 0): while($row = $result_cuti->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                            <td><?php echo htmlspecialchars($row['jenis_cuti']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                            <td>
                                <a href="proses_persetujuan_pj.php?id=<?php echo $row['id']; ?>&aksi=setujui" class="action-btn-terima">Setujui</a>
                                <a href="proses_persetujuan_pj.php?id=<?php echo $row['id']; ?>&aksi=tolak" class="action-btn-tolak">Tolak</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5">Tidak ada pengajuan cuti dari Penanggung Jawab.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>