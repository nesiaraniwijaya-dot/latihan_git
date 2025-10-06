<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'administrator') { header("Location: ../login.php"); exit(); }

$lamaran_id = $_GET['id'] ?? null;
if (!$lamaran_id) { header("Location: index.php"); exit(); }

$success_message = '';

// Proses HANYA untuk update ID Kustom
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_pelamar_custom'])) {
    $id_pelamar_custom = $_POST['id_pelamar_custom'];

    $stmt_update = $conn->prepare("UPDATE lamaran SET id_pelamar_custom = ? WHERE id = ?");
    $stmt_update->bind_param("si", $id_pelamar_custom, $lamaran_id);
    
    if ($stmt_update->execute()) {
        $success_message = "ID Pelamar Kustom berhasil diperbarui!";
    }
}

// Ambil detail data pelamar
$stmt = $conn->prepare("SELECT dp.nama_lengkap, u.email, l.*, dp.* FROM lamaran l JOIN users u ON l.user_id = u.id JOIN data_pelamar dp ON l.user_id = dp.user_id WHERE l.id = ?");
$stmt->bind_param("i", $lamaran_id);
$stmt->execute();
$pelamar = $stmt->get_result()->fetch_assoc();

// Ambil daftar dokumen
$stmt_dokumen = $conn->prepare("SELECT * FROM dokumen_pelamar WHERE lamaran_id = ?");
$stmt_dokumen->bind_param("i", $lamaran_id);
$stmt_dokumen->execute();
$dokumen = $stmt_dokumen->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Pelamar</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="nav-links">
        <a href="index.php">Kembali ke Daftar Pelamar</a>
        <a href="riwayat_status.php?id=<?php echo $lamaran_id; ?>" class="action-link-view">Lihat Riwayat Status</a>
    </div>
    <h2>Detail Data Pelamar: <?php echo htmlspecialchars($pelamar['nama_lengkap']); ?></h2>

    <?php if ($success_message): ?><div class="success-msg"><?php echo $success_message; ?></div><?php endif; ?>

    <form action="edit_pelamar.php?id=<?php echo $lamaran_id; ?>" method="POST">
        <div class="form-group">
            <label for="id_pelamar_custom">ID Pelamar Kustom</label>
            <input type="text" id="id_pelamar_custom" name="id_pelamar_custom" value="<?php echo htmlspecialchars($pelamar['id_pelamar_custom'] ?? ''); ?>">
        </div>
        <button type="submit">Update ID Kustom</button>
    </form>

    <div class="details-section">
        <h3>Informasi Lamaran</h3>
        <p><strong>Status Saat Ini:</strong> <?php echo htmlspecialchars($pelamar['status']); ?></p>
        <p><strong>Posisi Dilamar:</strong> <?php echo htmlspecialchars($pelamar['divisi_lamaran']); ?></p>
        <p><strong>Tanggal Lamar:</strong> <?php echo date('d M Y, H:i', strtotime($pelamar['tanggal_lamaran'])); ?></p>
    </div>
    <div class="details-section">
        <h3>Informasi Pribadi</h3>
        <p><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($pelamar['nama_lengkap']); ?></p>
        <p><strong>NIK:</strong> <?php echo htmlspecialchars($pelamar['nik']); ?></p>
        <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($pelamar['jenis_kelamin']); ?></p>
        <p><strong>Agama:</strong> <?php echo htmlspecialchars($pelamar['agama']); ?></p>
        <p><strong>Alamat:</strong> <?php echo nl2br(htmlspecialchars($pelamar['alamat_lengkap'])); ?></p>
    </div>
    <div class="details-section">
        <h3>Kontak</h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($pelamar['email']); ?></p>
        <p><strong>Nomor Telepon:</strong> <?php echo htmlspecialchars($pelamar['no_telepon']); ?></p>
        <p><strong>Kontak Darurat:</strong> <?php echo htmlspecialchars($pelamar['kontak_darurat']); ?></p>
    </div>
    <div class="details-section">
        <h3>Pendidikan & Gaji</h3>
        <p><strong>Pendidikan Terakhir:</strong> <?php echo htmlspecialchars($pelamar['pendidikan_terakhir']); ?></p>
        <p><strong>IPK:</strong> <?php echo htmlspecialchars($pelamar['ipk']); ?></p>
        <p><strong>Perkiraan Gaji:</strong> Rp <?php echo number_format($pelamar['perkiraan_gaji'], 0, ',', '.'); ?></p>
    </div>

    <div class="details-section">
    <h3>Dokumen Terunggah</h3>
    <ul>
        <?php if ($dokumen && $dokumen->num_rows > 0): ?>
            <?php while($doc = $dokumen->fetch_assoc()): ?>
                <li>
                    <?php echo htmlspecialchars($doc['jenis_dokumen']); ?>:
                    <a href="../<?php echo htmlspecialchars($doc['path_file']); ?>" download="<?php echo htmlspecialchars($doc['nama_file']); ?>" class="action-link-view">
                        Download Berkas
                    </a>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>Tidak ada dokumen yang diunggah.</li>
        <?php endif; ?>
    </ul>
</div>
</div>
</body>
</html>