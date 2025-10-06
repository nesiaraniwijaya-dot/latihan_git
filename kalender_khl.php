<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penanggung_jawab') { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$stmt_pj = $conn->prepare("SELECT divisi FROM karyawan WHERE user_id = ?");
$stmt_pj->bind_param("i", $user_id);
$stmt_pj->execute();
$divisi_pj = $stmt_pj->get_result()->fetch_assoc()['divisi'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Kalender Cuti KHL</title>
    <link rel="stylesheet" href="../assets/css/manager_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
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
                <div class="header-left"><h1 class="title">Kalender Cuti KHL</h1><p class="subtitle">Kalender cuti KHL untuk Divisi <?php echo htmlspecialchars($divisi_pj); ?></p></div>
            </header>
            <section class="card"><div id="calendar-khl"></div></section>
        </main>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar-khl');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
          events: 'api_cuti_khl_events.php'
        });
        calendar.render();
      });
    </script>
</body>
</html>