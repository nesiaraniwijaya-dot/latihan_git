<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'direksi') { header("Location: ../login.php"); exit(); }

$pengajuan_id = $_GET['id'] ?? null;
$aksi = $_GET['aksi'] ?? null;
if (!$pengajuan_id || !$aksi) { header("Location: index.php?status=gagal"); exit(); }

// Logika persetujuan ini sama persis dengan milik Penanggung Jawab
// karena Direksi menyetujui cuti PJ (yang juga seorang karyawan)
$conn->begin_transaction();
try {
    $status_baru = ($aksi == 'setujui') ? 'Disetujui' : 'Ditolak';
    $id_direksi = $_SESSION['user_id'];
    
    $stmt_update_status = $conn->prepare("UPDATE pengajuan_cuti SET status_pengajuan = ?, diproses_oleh = ? WHERE id = ?");
    $stmt_update_status->bind_param("sii", $status_baru, $id_direksi, $pengajuan_id);
    $stmt_update_status->execute();

    if ($aksi == 'setujui') {
        $stmt_get_cuti = $conn->prepare("SELECT karyawan_id, jenis_cuti, jumlah_hari FROM pengajuan_cuti WHERE id = ?");
        $stmt_get_cuti->bind_param("i", $pengajuan_id);
        $stmt_get_cuti->execute();
        $cuti = $stmt_get_cuti->get_result()->fetch_assoc();

        if ($cuti['jenis_cuti'] == 'Tahunan' || $cuti['jenis_cuti'] == 'Lustrum') {
            $tahun_sekarang = date('Y');
            $stmt_potong_cuti = $conn->prepare("UPDATE jatah_cuti SET sisa = sisa - ? WHERE karyawan_id = ? AND jenis_cuti = ? AND tahun_berlaku = ?");
            $stmt_potong_cuti->bind_param("iiss", $cuti['jumlah_hari'], $cuti['karyawan_id'], $cuti['jenis_cuti'], $tahun_sekarang);
            $stmt_potong_cuti->execute();
        }
    }
    $conn->commit();
    header("Location: index.php?status=sukses");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    header("Location: index.php?status=gagal");
    exit();
}
?>