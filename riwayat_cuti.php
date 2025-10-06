<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['karyawan', 'penanggung_jawab'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

$stmt_karyawan = $conn->prepare("SELECT id FROM karyawan WHERE user_id = ?");
$stmt_karyawan->bind_param("i", $user_id);
$stmt_karyawan->execute();
$karyawan = $stmt_karyawan->get_result()->fetch_assoc();

if (!$karyawan) { die("Error: Data karyawan tidak ditemukan."); }
$karyawan_id = $karyawan['id'];

// --- PERBAIKAN DI SINI: Menambahkan filter AND jenis_cuti != 'KHL' ---
$stmt_riwayat = $conn->prepare("SELECT * FROM pengajuan_cuti WHERE karyawan_id = ? AND jenis_cuti != 'KHL' ORDER BY tanggal_pengajuan DESC");
$stmt_riwayat->bind_param("i", $karyawan_id);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Riwayat Cuti Pribadi</title>
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
                    <li class="active"><a href="riwayat_cuti.php"><i class="fas fa-history"></i> Riwayat Cuti Pribadi</a></li>
                    <li><a href="pengajuan_cuti_khl.php"><i class="fas fa-umbrella-beach"></i> Pengajuan Cuti KHL</a></li>
                    <li><a href="riwayat_cuti_khl.php"><i class="fas fa-list-alt"></i> Riwayat Cuti KHL</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="welcome-message"><h2>Riwayat Pengajuan Cuti Pribadi</h2><p>Berikut adalah riwayat cuti pribadi Anda (non-KHL).</p></div>
                <a href="logout.php" class="logout-button-dashboard"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </header>
            <section class="content-table card">
                <table>
                    <thead><tr><th>ID</th><th>Jenis Cuti</th><th>Tanggal Mulai</th><th>Selesai</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if ($result_riwayat->num_rows > 0): while($row = $result_riwayat->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['jenis_cuti']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_selesai'])); ?></td>
                            <td><span class="status-badge-ajuan <?php echo strtolower(str_replace(' ', '-', $row['status_pengajuan'])); ?>"><?php echo htmlspecialchars($row['status_pengajuan']); ?></span></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5">Anda belum memiliki riwayat cuti pribadi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>