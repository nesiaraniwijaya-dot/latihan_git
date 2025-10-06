<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'administrator') {
    header("Location: ../login.php");
    exit();
}
$admin_name = $_SESSION['user_nama'];
$result = $conn->query("SELECT id, nama_lengkap, divisi, tanggal_bergabung FROM karyawan ORDER BY nama_lengkap");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Daftar Karyawan</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="container">
        <h1>Selamat Datang, <?php echo htmlspecialchars($admin_name); ?>!</h1>
        <div class="nav-links">
            <a href="index.php">Daftar Pelamar</a>
            <a href="karyawan.php" class="active">Daftar Karyawan</a>
            <a href="semua_cuti.php">Daftar Pengajuan Cuti</a>
            <a href="pengumuman.php">Riwayat Pelamar</a>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="section">
            <h2>Daftar Karyawan</h2>
            <table>
                <thead><tr><th>ID</th><th>Nama Lengkap</th><th>Divisi</th><th>Tanggal Bergabung</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['tanggal_bergabung'])); ?></td>
                        <td><a href="kelola_jatah_cuti.php?id=<?php echo $row['id']; ?>" class="action-link-view">Kelola Jatah Cuti</a></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5">Belum ada data karyawan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>