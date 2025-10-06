<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Mengambil ID Karyawan dari Penanggung Jawab yang login
$stmt_karyawan = $conn->prepare("SELECT id FROM karyawan WHERE user_id = ?");
$stmt_karyawan->bind_param("i", $user_id);
$stmt_karyawan->execute();
$karyawan = $stmt_karyawan->get_result()->fetch_assoc();
$karyawan_id = $karyawan['id'];

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_cuti = $_POST['jenis_cuti'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $alasan = $_POST['alasan'];
    
    $tgl1 = new DateTime($tanggal_mulai);
    $tgl2 = new DateTime($tanggal_selesai);
    $jumlah_hari = $tgl2->diff($tgl1)->days + 1;

    // Logika validasi sisa cuti...
    if ($jenis_cuti == 'Tahunan' || $jenis_cuti == 'Lustrum') {
        $tahun_sekarang = date('Y');
        $stmt_sisa = $conn->prepare("SELECT sisa FROM jatah_cuti WHERE karyawan_id = ? AND jenis_cuti = ? AND tahun_berlaku = ?");
        $stmt_sisa->bind_param("isi", $karyawan_id, $jenis_cuti, $tahun_sekarang);
        $stmt_sisa->execute();
        $jatah = $stmt_sisa->get_result()->fetch_assoc();
        $sisa_cuti = $jatah['sisa'] ?? 0;
        if ($jumlah_hari > $sisa_cuti) {
            $error_message = "Gagal: Pengajuan cuti Anda ($jumlah_hari hari) melebihi sisa cuti " . strtolower($jenis_cuti) . " Anda ($sisa_cuti hari).";
        }
    }
    // Logika upload...
    $nama_file_surat_sakit = null;
    if (empty($error_message) && $jenis_cuti == 'Sakit') {
        if (isset($_FILES['bukti_sakit']) && $_FILES['bukti_sakit']['error'] == 0) {
            $target_dir = "../uploads/bukti_sakit/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $nama_file_surat_sakit = 'bukti_pj_' . $karyawan_id . '_' . time() . '.' . strtolower(pathinfo(basename($_FILES["bukti_sakit"]["name"]), PATHINFO_EXTENSION));
            if (!move_uploaded_file($_FILES["bukti_sakit"]["tmp_name"], $target_dir . $nama_file_surat_sakit)) {
                $error_message = "Gagal mengunggah file bukti sakit.";
            }
        } else { $error_message = "Gagal: Untuk Cuti Sakit, Anda wajib mengunggah bukti surat sakit."; }
    }
    // Proses insert jika tidak ada error...
    if (empty($error_message)) {
        $stmt_insert = $conn->prepare("INSERT INTO pengajuan_cuti (karyawan_id, jenis_cuti, tanggal_mulai, tanggal_selesai, jumlah_hari, alasan, file_surat_sakit) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("isssiss", $karyawan_id, $jenis_cuti, $tanggal_mulai, $tanggal_selesai, $jumlah_hari, $alasan, $nama_file_surat_sakit);
        if ($stmt_insert->execute()) {
            $success_message = "Pengajuan cuti Anda telah berhasil dikirim ke Direksi untuk persetujuan.";
        } else { $error_message = "Terjadi kesalahan. Gagal mengirim pengajuan cuti."; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Pengajuan Cuti Pribadi</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
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
            <div class="welcome-message">
                <h2>Formulir Pengajuan Cuti</h2>
                <p>Silakan isi detail pengajuan cuti Anda di bawah ini.</p>
            </div>
            <a href="../logout.php" class="logout-button-dashboard"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </header>
        <section class="content-form">
            <?php if ($success_message): ?><div class="message success"><?php echo $success_message; ?></div><?php endif; ?>
            <?php if ($error_message): ?><div class="message error"><?php echo $error_message; ?></div><?php endif; ?>
            
            <form action="pengajuan_cuti_pj.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="jenis_cuti">Jenis Cuti</label>
                    <select id="jenis_cuti" name="jenis_cuti" required>
                        <option value="">-- Pilih Jenis Cuti --</option>
                        <option value="Tahunan">Cuti Tahunan</option>
                        <option value="Lustrum">Cuti Lustrum</option>
                        <option value="Khusus">Cuti Khusus</option>
                        <option value="Sakit">Cuti Sakit</option>
                        <option value="Ibadah">Cuti Menjalankan Kewajiban Agama</option>
                    </select>
                </div>
                <div class="form-group hidden" id="upload_bukti_sakit">
                    <label for="bukti_sakit">Upload Bukti Surat Sakit (Wajib untuk Cuti Sakit)</label>
                    <input type="file" id="bukti_sakit" name="bukti_sakit">
                </div>
                <div class="form-group-row">
                    <div class="form-group"><label for="tanggal_mulai">Tanggal Mulai</label><input type="date" id="tanggal_mulai" name="tanggal_mulai" required></div>
                    <div class="form-group"><label for="tanggal_selesai">Tanggal Selesai</label><input type="date" id="tanggal_selesai" name="tanggal_selesai" required></div>
                </div>
                <div class="form-group"><label for="alasan">Alasan / Keterangan</label><textarea id="alasan" name="alasan" rows="5" required></textarea></div>
                <button type="submit" class="submit-button">Ajukan Cuti</button>
            </form>
        </section>
    </main>
</div>
<script>
    const jenisCutiSelect = document.getElementById('jenis_cuti');
    const uploadFormGroup = document.getElementById('upload_bukti_sakit');
    if(jenisCutiSelect) {
        jenisCutiSelect.addEventListener('change', function() {
            if (this.value === 'Sakit') { uploadFormGroup.classList.remove('hidden'); }
            else { uploadFormGroup.classList.add('hidden'); }
        });
    }
</script>
</body>
</html>