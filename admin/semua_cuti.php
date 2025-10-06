<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'administrator') { header("Location: ../login.php"); exit(); }
$admin_name = $_SESSION['user_nama'];
$divisi_filter = $_GET['divisi'] ?? null;
$query = "
    SELECT pc.id, k.nama_lengkap, k.divisi, pc.jenis_cuti, pc.tanggal_mulai, pc.tanggal_selesai, pc.status_pengajuan
    FROM pengajuan_cuti pc JOIN karyawan k ON pc.karyawan_id = k.id
";
if ($divisi_filter) { $query .= " WHERE k.divisi = ?"; }
$query .= " ORDER BY pc.tanggal_pengajuan DESC";
$stmt = $conn->prepare($query);
if ($divisi_filter) { $stmt->bind_param("s", $divisi_filter); }
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Admin - Kelola Cuti</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="container">
        <h1>Kelola Cuti Karyawan</h1>
        <div class="nav-links">
            <a href="index.php">Daftar Pelamar</a>
            <a href="karyawan.php">Daftar Karyawan</a>
            <a href="semua_cuti.php" class="active">Daftar Pengajuan Cuti</a>
            <a href="riwayat_pelamar.php">Riwayat Pelamar</a>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="section">
            <h2>Daftar Semua Pengajuan Cuti</h2>
            <div class="filter-nav" style="margin:20px 0;padding:10px;background-color:#f8f9fa;border-radius:5px;">
                <strong>Filter per Divisi:</strong>
                <a href="semua_cuti.php" style="text-decoration:none;padding:8px 12px;margin:0 5px;border-radius:5px;font-weight:bold;<?php if(!$divisi_filter) echo 'background-color:#3B82F6;color:#fff;'; ?>">Semua</a>
                <a href="semua_cuti.php?divisi=Training" style="text-decoration:none;padding:8px 12px;margin:0 5px;border-radius:5px;font-weight:bold;<?php if($divisi_filter == 'Training') echo 'background-color:#3B82F6;color:#fff;'; ?>">Training</a>
                <a href="semua_cuti.php?divisi=Wisma" style="text-decoration:none;padding:8px 12px;margin:0 5px;border-radius:5px;font-weight:bold;<?php if($divisi_filter == 'Wisma') echo 'background-color:#3B82F6;color:#fff;'; ?>">Wisma</a>
                <a href="semua_cuti.php?divisi=Konsultasi" style="text-decoration:none;padding:8px 12px;margin:0 5px;border-radius:5px;font-weight:bold;<?php if($divisi_filter == 'Konsultasi') echo 'background-color:#3B82F6;color:#fff;'; ?>">Konsultasi</a>
            </div>
            <table>
                <thead><tr><th>ID</th><th>Nama Karyawan</th><th>Divisi</th><th>Jenis Cuti</th><th>Tanggal</th><th>Status</th></tr></thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                        <td><?php echo htmlspecialchars($row['jenis_cuti']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                        <td><?php echo htmlspecialchars($row['status_pengajuan']); ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6">Tidak ada data cuti.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>