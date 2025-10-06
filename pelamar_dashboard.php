<?php
session_start();
require_once 'config/database.php';

// Proteksi halaman, hanya untuk pelamar
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'pelamar') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- PERUBAHAN DI SINI: Mengambil juga status tes individu ---
$stmt_lamaran = $conn->prepare("
    SELECT
        dp.nama_lengkap,
        l.posisi_yang_dilamar,
        l.status,
        l.status_psikotes,
        l.status_kesehatan,
        l.tanggal_lamaran,
        l.id_pelamar_custom
    FROM lamaran AS l
    JOIN data_pelamar AS dp ON l.user_id = dp.user_id
    WHERE l.user_id = ?
    ORDER BY l.tanggal_lamaran DESC
    LIMIT 1
");
$stmt_lamaran->bind_param("i", $user_id);
$stmt_lamaran->execute();
$result_lamaran = $stmt_lamaran->get_result();
$lamaran = $result_lamaran->fetch_assoc();

if (!$lamaran) {
    die("Data lamaran tidak ditemukan.");
}

// --- LOGIKA BARU: Menentukan status yang akan ditampilkan ---
$status_display = $lamaran['status']; // Nilai default
$current_main_status = $lamaran['status'];

if ($current_main_status == 'Lolos Administratif' || $current_main_status == 'Seleksi Psikotes & Kesehatan') {
    if ($lamaran['status_psikotes'] == 'Belum Dinilai') {
        $status_display = 'Seleksi Psikotes';
    } elseif ($lamaran['status_psikotes'] == 'Lulus' && $lamaran['status_kesehatan'] == 'Belum Dinilai') {
        $status_display = 'Seleksi Tes Kesehatan';
    } elseif ($lamaran['status_psikotes'] == 'Lulus' && $lamaran['status_kesehatan'] == 'Lulus') {
        $status_display = 'Menunggu Keputusan Final';
    }
}


// Mengambil notifikasi PERSONAL untuk pelamar ini
$stmt_notif = $conn->prepare("SELECT * FROM notifikasi_pelamar WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$notifikasi = $stmt_notif->get_result()->fetch_assoc();

function time_ago($datetime) { /* ... isi fungsi tetap sama ... */ }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pelamar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="top-bar">
        <div class="logo"><img src="assets/img/logo.png" alt="Logo"><span>Pelamar</span></div>
        <a href="logout.php" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="dashboard-container">
        <div class="card welcome-card">
            <h2>Selamat Datang, <?php echo htmlspecialchars($lamaran['nama_lengkap']); ?></h2>
            <p>Dashboard Pelamar</p>
        </div>

        <div class="dashboard-grid">
            <div class="main-content">
                <div class="card">
                    <div class="card-header"><div class="card-icon status-icon"><i class="fa-solid fa-briefcase"></i></div><h3>Status Lamaran Kerja</h3></div>
                    <div class="status-content">
                        <p class="status-title"><?php echo htmlspecialchars($status_display); ?></p>
                        <span class="status-badge">Under Review</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><div class="card-icon announcement-icon"><i class="fa-solid fa-bullhorn"></i></div><h3>Important Announcement</h3></div>
                    <div class="card-body">
                        <?php if ($notifikasi): ?>
                            <p class="announcement-text"><?php echo nl2br(htmlspecialchars($notifikasi['isi_pesan'])); ?></p>
                            <p class="announcement-time">Posted <?php echo time_ago($notifikasi['created_at']); ?></p>
                        <?php else: ?>
                            <p class="announcement-text">Belum ada pengumuman untuk Anda saat ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="sidebar-content">
                <div class="card">
                    <ul class="detail-list">
                        <li><span>Posisi yang Dilamar</span><strong><?php echo htmlspecialchars($lamaran['posisi_yang_dilamar']); ?></strong></li>
                        <li><span>ID Pelamar</span><strong><?php echo htmlspecialchars($lamaran['id_pelamar_custom'] ?? 'Belum Ditetapkan'); ?></strong></li>
                        <li><span>Tanggal Kirim</span><strong><?php echo date('d M, Y', strtotime($lamaran['tanggal_lamaran'])); ?></strong></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card help-card">
            <h3>Butuh Bantuan?</h3>
            <div class="help-grid">
                <a href="mailto:career.purba.danarta@gmail.com" class="help-item-link">
                    <div class="help-item">
                        <div class="help-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div class="help-text"><strong>Email Support</strong><span>career.purba.danarta@gmail.com</span></div>
                    </div>
                </a>
                <a href="https://wa.me/6281234567890" target="_blank" class="help-item-link">
                    <div class="help-item">
                        <div class="help-icon"><i class="fa-solid fa-phone"></i></div>
                        <div class="help-text"><strong>Phone Support</strong><span>+62 812-3456-7890</span></div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>