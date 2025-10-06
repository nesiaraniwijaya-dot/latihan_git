<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

$stmt_karyawan = $conn->prepare("SELECT id, divisi, nama_lengkap FROM karyawan WHERE user_id = ?");
$stmt_karyawan->bind_param("i", $user_id);
$stmt_karyawan->execute();
$pj_data = $stmt_karyawan->get_result()->fetch_assoc();
$karyawan_id = $pj_data['id'];
$nama_pj = $pj_data['nama_lengkap'];
$divisi_pj = $pj_data['divisi'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_proyek = $_POST['nama_proyek'];
    $tanggal_khl = $_POST['tanggal_khl'];
    $jam_mulai_kerja = $_POST['jam_mulai_kerja'];
    $jam_akhir_kerja = $_POST['jam_akhir_kerja'];
    $tanggal_cuti_khl = $_POST['tanggal_cuti_khl'];
    $jam_mulai_cuti = $_POST['jam_mulai_cuti'];
    $jam_akhir_cuti = $_POST['jam_akhir_cuti'];
    $alasan = $_POST['alasan'] ?? '';

    $tanggal_khl_dt = new DateTime($tanggal_khl);
    $tanggal_pengajuan_dt = new DateTime();
    if ($tanggal_pengajuan_dt->diff($tanggal_khl_dt)->days > 30) {
        $error_message = "Gagal: Pengajuan tidak boleh melebihi 30 hari dari Tanggal KHL.";
    } else {
        $stmt_insert = $conn->prepare("
            INSERT INTO pengajuan_cuti (karyawan_id, jenis_cuti, tanggal_mulai, tanggal_selesai, jumlah_hari, alasan, nama_proyek, tanggal_khl, jam_mulai_kerja, jam_akhir_kerja, jam_mulai_cuti, jam_akhir_cuti) 
            VALUES (?, 'KHL', ?, ?, 1, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_insert->bind_param("isssssssss", 
            $karyawan_id, $tanggal_cuti_khl, $tanggal_cuti_khl, $alasan, $nama_proyek, $tanggal_khl, 
            $jam_mulai_kerja, $jam_akhir_kerja, $jam_mulai_cuti, $jam_akhir_cuti
        );

        if ($stmt_insert->execute()) {
            $success_message = "Pengajuan Cuti KHL Anda telah berhasil dikirim ke Direksi.";
        } else {
            $error_message = "Gagal mengirim pengajuan KHL: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Pengajuan Cuti KHL Pribadi</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
       <aside class="sidebar">
            <div class="sidebar-header"><img src="../assets/img/logo.png" alt="Logo" class="sidebar-logo"><h3 class="sidebar-title">Yayasan Purba Danarta</h3></div>
             <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-title">Penanganan Cuti</li>
                <li class="active"><a href="pengajuan_cuti_pj.php"><i class="fas fa-plane-departure"></i> Ajukan Cuti Pribadi</a></li>
                <li><a href="riwayat_cuti_pribadi.php"><i class="fas fa-history"></i> Riwayat Cuti Pribadi</a></li>
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
</nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1 class="title">Pengajuan Cuti KHL Pribadi</h1><p class="subtitle">Formulir ini untuk Anda mengajukan Cuti KHL ke Direksi.</p></div>
                <div class="user-profile">
                    <i class="fas fa-user-circle icon"></i>
                    <div class="user-info"><strong><?php echo htmlspecialchars($nama_pj); ?></strong><span>Penanggung Jawab Divisi <?php echo htmlspecialchars($divisi_pj); ?></span></div>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            <section class="content-form card">
                 <?php if ($success_message): ?><div class="message success" style="background-color:#d4edda; color:#155724; padding:15px; border-radius:5px; margin-bottom: 20px;"><?php echo $success_message; ?></div><?php endif; ?>
                 <?php if ($error_message): ?><div class="message error" style="background-color:#f8d7da; color:#721c24; padding:15px; border-radius:5px; margin-bottom: 20px;"><?php echo $error_message; ?></div><?php endif; ?>
                <form action="pengajuan_cuti_khl_pribadi.php" method="POST">
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