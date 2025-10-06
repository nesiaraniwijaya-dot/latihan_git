<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'administrator') { header("Location: ../login.php"); exit(); }
$lamaran_id = $_GET['id'] ?? null;
if (!$lamaran_id) { header("Location: index.php"); exit(); }

$stmt = $conn->prepare("SELECT dp.nama_lengkap FROM lamaran l JOIN data_pelamar dp ON l.user_id = dp.user_id WHERE l.id = ?");
$stmt->bind_param("i", $lamaran_id);
$stmt->execute();
$pelamar = $stmt->get_result()->fetch_assoc();

$stmt_riwayat = $conn->prepare("SELECT * FROM riwayat_status WHERE lamaran_id = ? ORDER BY waktu_perubahan DESC");
$stmt_riwayat->bind_param("i", $lamaran_id);
$stmt_riwayat->execute();
$riwayat = $stmt_riwayat->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Riwayat Status Lamaran</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="container">
    <div class="nav-links"><a href="edit_pelamar.php?id=<?php echo $lamaran_id; ?>">Kembali ke Detail Pelamar</a></div>
    <h2>Riwayat Status untuk <?php echo htmlspecialchars($pelamar['nama_lengkap']); ?></h2>
    <table>
        <thead><tr><th>Waktu Perubahan</th><th>Status Lama</th><th>Status Baru</th><th>Diubah Oleh</th></tr></thead>
        <tbody>
            <?php if ($riwayat->num_rows > 0): while($row = $riwayat->fetch_assoc()): ?>
            <tr>
                <td><?php echo date('d M Y, H:i:s', strtotime($row['waktu_perubahan'])); ?></td>
                <td><?php echo htmlspecialchars($row['status_lama']); ?></td>
                <td><?php echo htmlspecialchars($row['status_baru']); ?></td>
                <td><?php echo htmlspecialchars($row['diubah_oleh']); ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="4">Belum ada riwayat perubahan status.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>