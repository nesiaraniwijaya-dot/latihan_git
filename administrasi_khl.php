<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$stmt_pj = $conn->prepare("SELECT divisi FROM karyawan WHERE user_id = ?");
$stmt_pj->bind_param("i", $user_id);
$stmt_pj->execute();
$divisi_pj = $stmt_pj->get_result()->fetch_assoc()['divisi'];

// Mengambil HANYA Cuti KHL yang 'Menunggu Persetujuan'
$stmt_cuti = $conn->prepare("
    SELECT pc.id, k.nama_lengkap, pc.nama_proyek, pc.tanggal_khl
    FROM pengajuan_cuti pc JOIN karyawan k ON pc.karyawan_id = k.id
    WHERE k.divisi = ? AND pc.jenis_cuti = 'KHL' AND pc.status_pengajuan = 'Menunggu Persetujuan'
    ORDER BY pc.tanggal_pengajuan ASC
");
$stmt_cuti->bind_param("s", $divisi_pj);
$stmt_cuti->execute();
$result_cuti = $stmt_cuti->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Administrasi Cuti KHL</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><img src="../assets/img/logo.png" alt="Logo" class="sidebar-logo"><h3 class="sidebar-title">Yayasan Purba Danarta</h3></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-title">Penanganan Cuti</li>
                <li><a href="pengajuan_cuti_pj.php"><i class="fas fa-plane-departure"></i> Ajukan Cuti Pribadi</a></li>
                <li><a href="riwayat_cuti_pribadi.php"><i class="fas fa-history"></i> Riwayat Cuti Pribadi</a></li>
                <li><a href="administrasi_cuti.php"><i class="fas fa-book"></i> Administrasi Cuti</a></li>
                <li><a href="kalender_cuti.php"><i class="fas fa-calendar-alt"></i> Kalender Cuti</a></li>
                <li><a href="riwayat_cuti_karyawan"><i class="fas fa-history"></i> Riwayat Cuti Karyawan</a></li>
                <li class="nav-title">Penanganan KHL</li>
                <li><a href="persetujuan_cuti_khl.php"><i class="fas fa-check-square"></i> Ajukan Cuti KHL</a></li>
                <li><a href="riwayat_cuti_khl.php"><i class="fas fa-history"></i> Riwayat Cuti KHL Pribadi</a></li>
                <li class="active"><a href="administrasi_khl.php"><i class="fas fa-book-open"></i> Administrasi KHL</a></li>
                <li><a href="kalender_khl.php"><i class="fas fa-calendar-week"></i> Kalender KHL</a></li>
                <li><a href="riwayat_khl_karyawan"><i class="fas fa-history"></i> Riwayat Cuti KHL Karyawan</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1 class="title">Administrasi Cuti KHL</h1><p class="subtitle">Persetujuan Cuti KHL untuk Divisi <?php echo htmlspecialchars($divisi_pj); ?></p></div>
            </header>
            <section class="content-table card">
                <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?><div class="message success" style="background-color:#d4edda; color:#155724; padding:15px; border-radius:5px; margin-bottom: 20px;">Aksi berhasil diproses.</div><?php endif; ?>
                <table>
                    <thead><tr><th>Nama Karyawan</th><th>Nama Proyek</th><th>Tanggal KHL</th><th>Persetujuan</th></tr></thead>
                    <tbody>
                        <?php if ($result_cuti->num_rows > 0): while($row = $result_cuti->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_proyek']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_khl'])); ?></td>
                            <td>
                                <a href="proses_persetujuan.php?id=<?php echo $row['id']; ?>&aksi=setujui&sumber=administrasi_khl" class="action-btn-terima" onclick="return confirm('Anda yakin?')">Setujui</a>
                                <a href="proses_persetujuan.php?id=<?php echo $row['id']; ?>&aksi=tolak&sumber=administrasi_khl" class="action-btn-tolak" onclick="return confirm('Anda yakin?')">Tolak</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4">Tidak ada pengajuan Cuti KHL yang menunggu persetujuan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>