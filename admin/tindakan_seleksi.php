<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'administrator') { header("Location: ../login.php"); exit(); }

$lamaran_id = $_GET['id'] ?? null;
$aksi = $_GET['aksi'] ?? null;
$tes = $_GET['tes'] ?? null;
$hasil = $_GET['hasil'] ?? null;

if (!$lamaran_id) { header('Location: index.php'); exit(); }

// --- Logika untuk menangani hasil tes (Psikotes/Kesehatan) ---
if ($tes && $hasil) {
    if ($hasil == 'lulus') {
        // Jika LULUS, arahkan ke halaman tulis notifikasi
        header("Location: kirim_notifikasi.php?id=$lamaran_id&tes=$tes&status_baru=Lulus");
        exit();
    } elseif ($hasil == 'gagal') {
        // Jika GAGAL, langsung update status final menjadi 'Ditolak Final'
        // Ini akan memindahkan pelamar ke riwayat (menghilangkannya dari daftar aktif)
        $stmt_gagal = $conn->prepare("UPDATE lamaran SET status = 'Ditolak Final' WHERE id = ?");
        $stmt_gagal->bind_param("i", $lamaran_id);
        $stmt_gagal->execute();
        header('Location: index.php?pesan=ditolak');
        exit();
    }
}

// --- Logika untuk status keseluruhan (Administratif/Final) ---
if ($aksi) {
    $status_baru = '';
    switch ($aksi) {
        case 'lolos_admin':
            header("Location: kirim_notifikasi.php?id=$lamaran_id&status_baru=Lolos Administratif");
            exit();
        case 'gagal_admin':
            $status_baru = 'Gagal Administratif'; // Ini juga memindahkan ke riwayat
            break;
        case 'lolos_final':
            // KONVERSI JADI KARYAWAN HANYA TERJADI DI SINI
            $stmt_pelamar = $conn->prepare("SELECT l.user_id, dp.nama_lengkap, dp.nik, u.email, dp.no_telepon, l.posisi_yang_dilamar FROM lamaran l JOIN data_pelamar dp ON l.user_id = dp.user_id JOIN users u ON l.user_id = u.id WHERE l.id = ?");
            $stmt_pelamar->bind_param("i", $lamaran_id);
            $stmt_pelamar->execute();
            $data_karyawan = $stmt_pelamar->get_result()->fetch_assoc();
            $divisi = $data_karyawan['posisi_yang_dilamar']; // Mengambil divisi dari posisi yg dilamar

            $stmt_karyawan = $conn->prepare("INSERT INTO karyawan (user_id, nama_lengkap, nik, email, no_telepon, divisi, tanggal_bergabung) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt_karyawan->bind_param("isssss", $data_karyawan['user_id'], $data_karyawan['nama_lengkap'], $data_karyawan['nik'], $data_karyawan['email'], $data_karyawan['no_telepon'], $divisi);
            $stmt_karyawan->execute();
            
            $stmt_role = $conn->prepare("UPDATE users SET role = 'karyawan' WHERE id = ?");
            $stmt_role->bind_param("i", $data_karyawan['user_id']);
            $stmt_role->execute();

            header("Location: kirim_notifikasi.php?id=$lamaran_id&status_baru=Lolos Final");
            exit();
        case 'ditolak_final':
            $status_baru = 'Ditolak Final';
            break;
        default:
            header('Location: index.php');
            exit();
    }

    if (!empty($status_baru)) {
        $stmt = $conn->prepare("UPDATE lamaran SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status_baru, $lamaran_id);
        $stmt->execute();
    }
    header('Location: index.php');
    exit();
}
?>