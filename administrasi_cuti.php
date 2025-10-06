<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$nama_pengguna = $_SESSION['user_nama'];
$stmt_pj = $conn->prepare("SELECT divisi FROM karyawan WHERE user_id = ?");
$stmt_pj->bind_param("i", $user_id);
$stmt_pj->execute();
$divisi_pj = $stmt_pj->get_result()->fetch_assoc()['divisi'];
$stmt_cuti = $conn->prepare("SELECT pc.id, k.nama_lengkap, pc.jenis_cuti, pc.tanggal_mulai, pc.tanggal_selesai, pc.alasan FROM pengajuan_cuti pc JOIN karyawan k ON pc.karyawan_id = k.id JOIN users u ON k.user_id = u.id WHERE k.divisi = ? AND pc.status_pengajuan = 'Menunggu Persetujuan' AND u.role = 'karyawan'");
$stmt_cuti->bind_param("s", $divisi_pj);
$stmt_cuti->execute();
$result_cuti = $stmt_cuti->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Administrasi Cuti Karyawan</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                <li><a href="riwayat_cuti_khl_pribadi.php"><i class="fas fa-list-alt"></i> Riwayat Cuti KHL</a></li>
                <li class="nav-title">Manajemen Divisi</li>
                <li class="active"><a href="administrasi_cuti.php"><i class="fas fa-book"></i> Administrasi Cuti Karyawan</a></li>
                <li><a href="riwayat_cuti_karyawan.php"><i class="fas fa-users"></i> Riwayat Cuti Divisi</a></li>
                <li><a href="kalender_cuti.php"><i class="fas fa-calendar-alt"></i> Kalender Cuti Divisi</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1 class="title">Administrasi Cuti Karyawan</h1><p class="subtitle">Daftar pengajuan cuti di divisi <?php echo htmlspecialchars($divisi_pj); ?></p></div>
                <div class="user-profile">
                    <i class="fas fa-user-circle icon"></i>
                    <div class="user-info"><strong><?php echo htmlspecialchars($nama_pengguna); ?></strong><span>Penanggung Jawab Divisi <?php echo htmlspecialchars($divisi_pj); ?></span></div>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            <section class="content-table card">
                <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?><div class="message success" style="background-color:#d4edda; color:#155724; padding:15px; border-radius:5px; margin-bottom: 20px;">Aksi berhasil diproses.</div><?php endif; ?>
                <table>
                    <thead><tr><th>Nama Karyawan</th><th>Jenis Cuti</th><th>Tanggal</th><th>Alasan</th><th>Persetujuan</th></tr></thead>
                    <tbody>
                        <?php if ($result_cuti->num_rows > 0): while($row = $result_cuti->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['jenis_cuti']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($row['tanggal_selesai'])); ?></td>
                            <td><?php echo htmlspecialchars($row['alasan']); ?></td>
                            <td>
                                <a href="proses_persetujuan.php?id=<?php echo $row['id']; ?>&aksi=setujui" class="action-btn-terima">Setujui</a>
                                <a href="proses_persetujuan.php?id=<?php echo $row['id']; ?>&aksi=tolak" class="action-btn-tolak">Tolak</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5">Tidak ada pengajuan cuti yang menunggu persetujuan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>