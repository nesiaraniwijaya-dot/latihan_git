<?php
session_start();
require_once '../config/database.php';

// Pastikan yang mengakses adalah penanggung jawab yang sedang login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil divisi penanggung jawab
$stmt_pj = $conn->prepare("SELECT divisi FROM karyawan WHERE user_id = ?");
$stmt_pj->bind_param("i", $user_id);
$stmt_pj->execute();
$divisi_pj = $stmt_pj->get_result()->fetch_assoc()['divisi'];

// Ambil HANYA Cuti KHL yang sudah disetujui di divisi tersebut
$stmt_events = $conn->prepare("
    SELECT k.nama_lengkap, pc.tanggal_mulai, pc.tanggal_selesai
    FROM pengajuan_cuti pc
    JOIN karyawan k ON pc.karyawan_id = k.id
    WHERE k.divisi = ? AND pc.status_pengajuan = 'Disetujui' AND pc.jenis_cuti = 'KHL'
");
$stmt_events->bind_param("s", $divisi_pj);
$stmt_events->execute();
$result = $stmt_events->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $endDate = new DateTime($row['tanggal_selesai']);
    $endDate->modify('+1 day');

    $events[] = [
        'title' => $row['nama_lengkap'] . ' (KHL)',
        'start' => $row['tanggal_mulai'],
        'end'   => $endDate->format('Y-m-d'),
        'color' => '#ffc107', // Warna kuning untuk KHL
        'textColor' => '#212529'
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
?>