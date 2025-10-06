<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['karyawan', 'penanggung_jawab'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

$stmt_karyawan = $conn->prepare("SELECT id FROM karyawan WHERE user_id = ?");
$stmt_karyawan->bind_param("i", $user_id);
$stmt_karyawan->execute();
$karyawan = $stmt_karyawan->get_result()->fetch_assoc();
$karyawan_id = $karyawan['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_proyek = $_POST['nama_proyek'];
    $tanggal_khl = $_POST['tanggal_khl'];
    $jam_mulai_kerja = $_POST['jam_mulai_kerja'];
    $jam_akhir_kerja = $_POST['jam_akhir_kerja'];
    $tanggal_cuti_khl = $_POST['tanggal_cuti_khl'];
    $jam_mulai_cuti = $_POST['jam_mulai_cuti'];
    $jam_akhir_cuti = $_POST['jam_akhir_cuti'];
    $alasan = $_POST['alasan'] ?? ''; // Jadikan alasan opsional

    $tanggal_khl_dt = new DateTime($tanggal_khl);
    $tanggal_pengajuan_dt = new DateTime();
    $selisih = $tanggal_pengajuan_dt->diff($tanggal_khl_dt)->days;

    if ($selisih > 30) {
        $error_message = "Gagal: Pengajuan Cuti KHL tidak boleh melebihi 30 hari dari Tanggal KHL.";
    } else {
        // PERBAIKAN UTAMA ADA DI DALAM DUA PERINTAH DI BAWAH INI
        $stmt_insert = $conn->prepare("
            INSERT INTO pengajuan_cuti 
            (karyawan_id, jenis_cuti, tanggal_mulai, alasan, nama_proyek, tanggal_khl, jam_mulai_kerja, jam_akhir_kerja, jam_mulai_cuti, jam_akhir_cuti) 
            VALUES (?, 'KHL', ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_insert->bind_param("issssssss", 
            $karyawan_id, $tanggal_cuti_khl, $alasan, $nama_proyek, $tanggal_khl, 
            $jam_mulai_kerja, $jam_akhir_kerja, $jam_mulai_cuti, $jam_akhir_cuti
        );

        if ($stmt_insert->execute()) {
            $success_message = "Pengajuan Cuti KHL Anda telah berhasil dikirim.";
        } else {
            $error_message = "Terjadi kesalahan. Gagal mengirim pengajuan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Pengajuan Cuti KHL</title>
    <link rel="stylesheet" href="assets/css/dashboard_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="assets/img/logo.png" alt="Logo" class="sidebar-logo">
                <h3 class="sidebar-title">Yayasan Purba Danarta</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="karyawan_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="pengajuan_cuti.php"><i class="fas fa-plane-departure"></i> Pengajuan Cuti Pribadi</a></li>
                    <li><a href="riwayat_cuti.php"><i class="fas fa-history"></i> Riwayat Cuti Pribadi</a></li>
                    <li class="active"><a href="pengajuan_cuti_khl.php"><i class="fas fa-umbrella-beach"></i> Pengajuan Cuti KHL</a></li>
                    <li><a href="riwayat_cuti_khl.php"><i class="fas fa-list-alt"></i> Riwayat Cuti KHL</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="welcome-message"><h2>Formulir Pengajuan Cuti KHL</h2><p>Silakan isi detail pengajuan Cuti KHL Anda.</p></div>
                <a href="logout.php" class="logout-button-dashboard"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </header>
            <section class="content-form">
                <?php if ($success_message): ?><div class="message success"><?php echo $success_message; ?></div><?php endif; ?>
                <?php if ($error_message): ?><div class="message error"><?php echo $error_message; ?></div><?php endif; ?>
                <form action="pengajuan_cuti_khl.php" method="POST">
                    <div class="form-group"><label for="nama_proyek">Nama Proyek</label><input type="text" id="nama_proyek" name="nama_proyek" required></div>
                    <div class="form-group-row">
                        <div class="form-group"><label for="tanggal_khl">Tanggal KHL</label><input type="date" id="tanggal_khl" name="tanggal_khl" required></div>
                        <div class="form-group"><label for="jam_mulai_kerja">Jam Mulai Kerja</label><input type="time" id="jam_mulai_kerja" name="jam_mulai_kerja" required></div>
                        <div class="form-group"><label for="jam_akhir_kerja">Jam Akhir Kerja</label><input type="time" id="jam_akhir_kerja" name="jam_akhir_kerja" required></div>
                    </div>
                    <div class="form-group-row">
                        <div class="form-group"><label for="tanggal_cuti_khl">Tanggal Cuti KHL</label><input type="date" id="tanggal_cuti_khl" name="tanggal_cuti_khl" required></div>
                        <div class="form-group"><label for="jam_mulai_cuti">Jam Mulai Cuti KHL</label><input type="time" id="jam_mulai_cuti" name="jam_mulai_cuti" required></div>
                        <div class="form-group"><label for="jam_akhir_cuti">Jam Akhir Cuti KHL</label><input type="time" id="jam_akhir_cuti" name="jam_akhir_cuti" required></div>
                    </div>
                    <div class="form-group"><label for="alasan">Keterangan Tambahan</label><textarea id="alasan" name="alasan" rows="4"></textarea></div>
                    <button type="submit" class="submit-button">Ajukan Cuti KHL</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>