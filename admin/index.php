<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'administrator') { header("Location: ../login.php"); exit(); }
$admin_name = $_SESSION['user_nama'];

$stmt_direksi_cuti = $conn->prepare("
    SELECT pc.tanggal_mulai, pc.tanggal_selesai 
    FROM pengajuan_cuti pc 
    JOIN karyawan k ON pc.karyawan_id = k.id 
    JOIN users u ON k.user_id = u.id 
    WHERE u.role = 'direksi' 
    AND pc.status_pengajuan = 'Disetujui' 
    AND CURDATE() BETWEEN pc.tanggal_mulai AND pc.tanggal_selesai 
    LIMIT 1
");
$stmt_direksi_cuti->execute();
$direksi_on_leave = $stmt_direksi_cuti->get_result()->fetch_assoc();;

$query_admin = "SELECT l.id as lamaran_id, dp.nama_lengkap, l.posisi_yang_dilamar FROM lamaran l JOIN data_pelamar dp ON l.user_id = dp.user_id WHERE l.status = 'Seleksi Administratif' ORDER BY l.tanggal_lamaran DESC";
$result_admin = $conn->query($query_admin);

$query_tes = "SELECT l.id as lamaran_id, dp.nama_lengkap, l.posisi_yang_dilamar, l.status_psikotes, l.status_kesehatan FROM lamaran l JOIN data_pelamar dp ON l.user_id = dp.user_id WHERE l.status IN ('Lolos Administratif', 'Seleksi Psikotes & Kesehatan') ORDER BY l.tanggal_lamaran DESC";
$result_tes = $conn->query($query_tes);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Admin Dashboard - Daftar Lamaran</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="container">
        <h1>Selamat Datang, <?php echo htmlspecialchars($admin_name); ?>!</h1>
        <p>Anda login sebagai Administrator. Di bawah ini adalah daftar pelamar yang perlu diproses.</p>
        
        <?php if ($direksi_on_leave): ?>
                <div class="info-box warning">
                    <i class="fas fa-info-circle"></i>
                    <span><strong>PEMBERITAHUAN:</strong> Direksi sedang dalam masa cuti dari tanggal 
                    <?php echo date('d M Y', strtotime($direksi_on_leave['tanggal_mulai'])); ?> hingga <?php echo date('d M Y', strtotime($direksi_on_leave['tanggal_selesai'])); ?>.
                    </span>
                </div>
            <?php endif; ?>


        <div class="nav-links">
            <a href="index.php" class="active">Daftar Pelamar</a>
            <a href="karyawan.php">Daftar Karyawan</a>
            <a href="semua_cuti.php"> Daftar Pengajuan Cuti</a>
            <a href="riwayat_pelamar.php">Riwayat Pelamar</a>
            <a href="../logout.php">Logout</a>
        </div>
        
        <div class="section">
            <h2>Seleksi Administratif</h2>
            <table>
                <thead><tr><th>ID</th><th>Nama Lengkap</th><th>Posisi Dilamar</th><th>Data</th><th>Tindakan</th></tr></thead>
                <tbody>
                    <?php if ($result_admin && $result_admin->num_rows > 0): while($row = $result_admin->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['lamaran_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($row['posisi_yang_dilamar']); ?></td>
                        <td><a href="edit_pelamar.php?id=<?php echo $row['lamaran_id']; ?>" class="action-link-view">Lihat Data</a></td>
                        <td>
                            <a href="tindakan_seleksi.php?id=<?php echo $row['lamaran_id']; ?>&aksi=lolos_admin" class="action-btn-terima">Lolos</a>
                            <a href="tindakan_seleksi.php?id=<?php echo $row['lamaran_id']; ?>&aksi=gagal_admin" class="action-btn-tolak">Tidak Lolos</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5">Tidak ada pelamar di tahap ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Seleksi Psikotes dan Tes Kesehatan</h2>
            <table>
                <thead><tr><th>ID</th><th>Nama Lengkap</th><th>Psikotes</th><th>Tes Kesehatan</th><th>Final Status</th></tr></thead>
                <tbody>
                     <?php if ($result_tes && $result_tes->num_rows > 0): while($row = $result_tes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['lamaran_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td>
                            <span class="status-<?php echo strtolower(str_replace(' ', '-', $row['status_psikotes'])); ?>"><?php echo $row['status_psikotes']; ?></span><br>
                            <a href="tindakan_seleksi.php?id=<?php echo $row['lamaran_id']; ?>&tes=psikotes&hasil=lulus" class="action-btn-terima">Lolos</a>
                            <a href="tindakan_seleksi.php?id=<?php echo $row['lamaran_id']; ?>&tes=psikotes&hasil=gagal" class="action-btn-tolak">Tidak Lolos</a>
                        </td>
                         <td>
                            <span class="status-<?php echo strtolower(str_replace(' ', '-', $row['status_kesehatan'])); ?>"><?php echo $row['status_kesehatan']; ?></span><br>
                            <a href="tindakan_seleksi.php?id=<?php echo $row['lamaran_id']; ?>&tes=kesehatan&hasil=lulus" class="action-btn-terima">Lolos</a>
                            <a href="tindakan_seleksi.php?id=<?php echo $row['lamaran_id']; ?>&tes=kesehatan&hasil=gagal" class="action-btn-tolak">Tidak Lolos</a>
                        </td>
                         <td>
                            <a href="tindakan_seleksi.php?id=<?php echo $row['lamaran_id']; ?>&aksi=lolos_final" class="action-btn-terima">Terima</a>
                            <a href="tindakan_seleksi.php?id=<?php echo $row['lamaran_id']; ?>&aksi=ditolak_final" class="action-btn-tolak">Tolak</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5">Tidak ada pelamar di tahap ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>