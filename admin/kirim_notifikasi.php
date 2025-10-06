<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'administrator') { header("Location: ../login.php"); exit(); }

$lamaran_id = $_GET['id'] ?? null;
$status_baru_overall = $_GET['status_baru'] ?? null;
$tes = $_GET['tes'] ?? null;
$status_baru_tes = $_GET['status_baru'] ?? null;

if (!$lamaran_id) { header('Location: index.php'); exit(); }

// Ambil nama pelamar
$stmt_nama = $conn->prepare("SELECT dp.nama_lengkap, l.user_id FROM lamaran l JOIN data_pelamar dp ON l.user_id = dp.user_id WHERE l.id = ?");
$stmt_nama->bind_param("i", $lamaran_id);
$stmt_nama->execute();
$pelamar = $stmt_nama->get_result()->fetch_assoc();

// Tentukan pesan default berdasarkan tahap
$pesan_default = "Selamat, Anda telah lolos tahap ";
if ($status_baru_overall == 'Lolos Administratif') {
    $pesan_default .= "Administratif.";
} elseif ($tes == 'psikotes') {
    $pesan_default .= "Psikotes.";
} elseif ($tes == 'kesehatan') {
    $pesan_default .= "Tes Kesehatan.";
} elseif ($status_baru_overall == 'Lolos Final') {
    $pesan_default = "Selamat! Anda dinyatakan LULOS seleksi dan diterima sebagai karyawan. Tim HRD akan segera menghubungi Anda untuk proses selanjutnya.";
}
$pesan_default .= "\n\nInformasi selanjutnya akan kami sampaikan melalui pemberitahuan atau email. Mohon periksa secara berkala.";

// Proses pengiriman form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $pesan = $_POST['pesan'];
    $user_id_pelamar = $pelamar['user_id'];

    // Update status berdasarkan jenis notifikasi
    if ($tes) { // Jika ini notifikasi kelulusan tes
        $kolom_update = ($tes == 'psikotes') ? 'status_psikotes' : 'status_kesehatan';
        $stmt_update = $conn->prepare("UPDATE lamaran SET $kolom_update = 'Lulus' WHERE id = ?");
        $stmt_update->bind_param("i", $lamaran_id);
        $stmt_update->execute();
    } else { // Jika ini notifikasi status keseluruhan
        $stmt_update = $conn->prepare("UPDATE lamaran SET status = ? WHERE id = ?");
        $stmt_update->bind_param("si", $status_baru_overall, $lamaran_id);
        $stmt_update->execute();
    }

    // Masukkan notifikasi ke database
    $stmt_notif = $conn->prepare("INSERT INTO notifikasi_pelamar (user_id, judul_notifikasi, isi_pesan) VALUES (?, ?, ?)");
    $stmt_notif->bind_param("iss", $user_id_pelamar, $judul, $pesan);
    $stmt_notif->execute();

    header("Location: index.php?pesan=sukses");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kirim Notifikasi ke Pelamar</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="container">
    <h2>Kirim Notifikasi Kelulusan</h2>
    <p>Kirim pesan kepada: <strong><?php echo htmlspecialchars($pelamar['nama_lengkap']); ?></strong></p>

    <form action="" method="POST">
        <div class="form-group">
            <label for="judul">Judul Notifikasi</label>
            <input type="text" id="judul" name="judul" value="Informasi Status Lamaran Anda" required>
        </div>
        <div class="form-group">
            <label for="pesan">Isi Pesan (Manual)</label>
            <textarea id="pesan" name="pesan" rows="8" required><?php echo htmlspecialchars($pesan_default); ?></textarea>
        </div>
        <button type="submit">Kirim Notifikasi & Update Status</button>
    </form>
</div>
</body>
</html>