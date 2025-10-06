<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'direksi') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$nama_pengguna = $_SESSION['user_nama'];
$success_message = '';
$error_message = '';

$stmt_karyawan = $conn->prepare("SELECT id FROM karyawan WHERE user_id = ?");
$stmt_karyawan->bind_param("i", $user_id);
$stmt_karyawan->execute();
$karyawan = $stmt_karyawan->get_result()->fetch_assoc();
$karyawan_id = $karyawan['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_cuti = $_POST['jenis_cuti'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $alasan = $_POST['alasan'];
    $tgl1 = new DateTime($tanggal_mulai);
    $tgl2 = new DateTime($tanggal_selesai);
    $jumlah_hari = $tgl2->diff($tgl1)->days + 1;
    
    // Untuk direksi, cuti dianggap langsung disetujui (auto-approved)
    $stmt_insert = $conn->prepare("INSERT INTO pengajuan_cuti (karyawan_id, jenis_cuti, tanggal_mulai, tanggal_selesai, jumlah_hari, alasan, status_pengajuan, diproses_oleh) VALUES (?, ?, ?, ?, ?, ?, 'Disetujui', ?)");
    $stmt_insert->bind_param("isssisi", $karyawan_id, $jenis_cuti, $tanggal_mulai, $tanggal_selesai, $jumlah_hari, $alasan, $user_id);
    if ($stmt_insert->execute()) {
        $success_message = "Pemberitahuan cuti Anda telah berhasil dicatat.";
        // Di sini bisa ditambahkan logika notifikasi ke admin/pj di masa depan
    } else {
        $error_message = "Terjadi kesalahan: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Pemberitahuan Cuti Direksi</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>.form-group.hidden { display: none; }</style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><img src="../assets/img/logo-lengkap.png" alt="Logo" class="sidebar-logo"></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="active"><a href="pemberitahuan_cuti.php"><i class="fas fa-paper-plane"></i> Pemberitahuan Cuti</a></li>
                <li class="nav-title">Pemantauan</li>
                <li><a href="riwayat_cuti_semua.php"><i class="fas fa-history"></i> Riwayat Cuti Karyawan</a></li>
                <li><a href="data_karyawan_semua.php"><i class="fas fa-users"></i> Data Semua Karyawan</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1 class="title">Pemberitahuan Cuti (Direksi)</h1><p class="subtitle">Cuti yang Anda ajukan akan otomatis disetujui dan dicatat.</p></div>
                <div class="user-profile">
                    <i class="fas fa-user-shield icon"></i>
                    <div class="user-info"><strong><?php echo htmlspecialchars($nama_pengguna); ?></strong><span>Direksi</span></div>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            <section class="content-form card">
                 <?php if ($success_message): ?><div class="message success" style="background-color:#d4edda; color:#155724; padding:15px; border-radius:5px; margin-bottom: 20px;"><?php echo $success_message; ?></div><?php endif; ?>
                 <?php if ($error_message): ?><div class="message error" style="background-color:#f8d7da; color:#721c24; padding:15px; border-radius:5px; margin-bottom: 20px;"><?php echo $error_message; ?></div><?php endif; ?>
                 <form action="pemberitahuan_cuti.php" method="POST" enctype="multipart/form-data">
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
                        <label for="bukti_sakit">Upload Bukti Surat Sakit (Wajib)</label>
                        <input type="file" id="bukti_sakit" name="bukti_sakit">
                    </div>
                    <div class="form-group-row">
                        <div class="form-group"><label for="tanggal_mulai">Tanggal Mulai</label><input type="date" id="tanggal_mulai" name="tanggal_mulai" required></div>
                        <div class="form-group"><label for="tanggal_selesai">Tanggal Selesai</label><input type="date" id="tanggal_selesai" name="tanggal_selesai" required></div>
                    </div>
                    <div class="form-group"><label for="alasan">Alasan / Keterangan</label><textarea id="alasan" name="alasan" rows="5" required></textarea></div>
                    <button type="submit" class="submit-button">Kirim Pemberitahuan</button>
                </form>
            </section>
        </main>
    </div>
    <script>
        const jenisCutiSelect = document.getElementById('jenis_cuti');
        const uploadFormGroup = document.getElementById('upload_bukti_sakit');
        if (jenisCutiSelect) {
            jenisCutiSelect.addEventListener('change', function() {
                if (this.value === 'Sakit') { uploadFormGroup.classList.remove('hidden'); }
                else { uploadFormGroup.classList.add('hidden'); }
            });
        }
    </script>
</body>
</html>