<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'administrator') {
    header("Location: ../login.php");
    exit();
}

// Validasi ID Karyawan dari URL
$karyawan_id = $_GET['id'] ?? null;
if (!$karyawan_id) {
    header("Location: karyawan.php");
    exit();
}

$success_message = '';

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jatah_tahunan = $_POST['jatah_tahunan'];
    $jatah_lustrum = $_POST['jatah_lustrum'];

    $stmt_update = $conn->prepare("UPDATE karyawan SET jatah_cuti_tahunan = ?, jatah_cuti_lustrum = ? WHERE id = ?");
    $stmt_update->bind_param("iii", $jatah_tahunan, $jatah_lustrum, $karyawan_id);
    
    if ($stmt_update->execute()) {
        $success_message = "Jatah cuti berhasil diperbarui!";
    }
}

// Ambil data karyawan
$stmt = $conn->prepare("SELECT id, nama_lengkap, tanggal_bergabung, jatah_cuti_tahunan, jatah_cuti_lustrum FROM karyawan WHERE id = ?");
$stmt->bind_param("i", $karyawan_id);
$stmt->execute();
$karyawan = $stmt->get_result()->fetch_assoc();

if (!$karyawan) {
    echo "Karyawan tidak ditemukan.";
    exit();
}

// --- LOGIKA PERHITUNGAN CUTI LUSTRUM OTOMATIS ---
$jatah_lustrum_otomatis = 0;
$tanggal_bergabung = new DateTime($karyawan['tanggal_bergabung']);
$hari_ini = new DateTime();
$masa_kerja = $hari_ini->diff($tanggal_bergabung);
$masa_kerja_tahun = $masa_kerja->y;

// Asumsi: Cuti lustrum diberikan setelah 5 tahun masa kerja.
// Anda bisa mengubah logika ini sesuai kebijakan perusahaan.
if ($masa_kerja_tahun >= 5) {
    // Contoh: jatah cuti lustrum adalah 30 hari setiap 5 tahun.
    // Logika ini bisa disesuaikan lebih lanjut jika perlu.
    $jatah_lustrum_otomatis = 30; 
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Jatah Cuti</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="container">
        <h1>Kelola Jatah Cuti</h1>
        <div class="nav-links">
            <a href="karyawan.php">Kembali ke Daftar Karyawan</a>
            <a href="index.php">Daftar Pelamar</a>
            <a href="semua_cuti.php">Kelola Cuti</a>
            <a href="../logout.php">Logout</a>
        </div>

        <h2>Karyawan: <?php echo htmlspecialchars($karyawan['nama_lengkap']); ?></h2>
        <p>Tanggal Bergabung: <?php echo date('d M Y', strtotime($karyawan['tanggal_bergabung'])); ?> (Masa Kerja: <?php echo $masa_kerja_tahun; ?> tahun)</p>

        <?php if ($success_message): ?>
            <div class="success-msg"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form action="kelola_jatah_cuti.php?id=<?php echo $karyawan['id']; ?>" method="POST">
            <div class="form-group">
                <label for="jatah_tahunan">Jatah Cuti Tahunan (Input Manual)</label>
                <input type="number" id="jatah_tahunan" name="jatah_tahunan" value="<?php echo htmlspecialchars($karyawan['jatah_cuti_tahunan']); ?>" required>
                <small>Masukkan sisa jatah cuti tahunan untuk karyawan ini.</small>
            </div>
            <div class="form-group">
                <label for="jatah_lustrum">Jatah Cuti Lustrum (Bisa Diedit)</label>
                <input type="number" id="jatah_lustrum" name="jatah_lustrum" value="<?php echo htmlspecialchars($karyawan['jatah_cuti_lustrum']); ?>" required>
                <small><b>Rekomendasi Otomatis berdasarkan masa kerja: <?php echo $jatah_lustrum_otomatis; ?> hari.</b> Anda bisa mengubah angka ini jika ada kebijakan khusus.</small>
            </div>
            <button type="submit">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>