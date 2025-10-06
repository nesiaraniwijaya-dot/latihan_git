<?php

echo "<div style='font-family: monospace; border: 2px solid blue; padding: 15px; margin: 10px; background: #f0f8ff;'>";
echo "<h1>Memulai Tes Diagnostik Login Admin...</h1>";

// --- Tes 1: Koneksi ke Database ---
echo "<h2>Tes 1: Koneksi Database</h2>";
require_once 'config/database.php';

if ($conn) {
    echo "<p style='color:green; font-weight:bold;'>BERHASIL: Koneksi ke database 'yayasan_purba_danarta' sukses.</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>GAGAL: Tidak bisa terhubung ke database. Periksa file config/database.php</p>";
    die(); // Hentikan jika koneksi gagal
}

// --- Tes 2: Mencari Pengguna Administrator ---
echo "<h2>Tes 2: Mencari Pengguna 'administrator'</h2>";
$username_to_test = 'administrator';
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username_to_test);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    echo "<p style='color:green; font-weight:bold;'>BERHASIL: Pengguna dengan username '" . htmlspecialchars($username_to_test) . "' ditemukan.</p>";
    echo "<h3>Data yang Ditemukan:</h3>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";

    // --- Tes 3: Verifikasi Password ---
    echo "<h2>Tes 3: Verifikasi Password 'admin123'</h2>";
    $password_to_test = 'admin123';
    
    echo "<p>Mencoba membandingkan password: '<strong>" . $password_to_test . "</strong>'</p>";
    echo "<p>Dengan hash dari database: '<strong>" . htmlspecialchars($user['password']) . "</strong>'</p>";

    if (password_verify($password_to_test, $user['password'])) {
        echo "<h2 style='color:green; font-weight:bold; border: 2px solid green; padding:10px;'>SUKSES! Password Cocok. Seharusnya login bisa berhasil.</h2>";
    } else {
        echo "<h2 style='color:red; font-weight:bold; border: 2px solid red; padding:10px;'>GAGAL! Password TIDAK Cocok. Ini adalah penyebabnya.</h2>";
        echo "<p>Pastikan Anda sudah menjalankan perintah UPDATE password di phpMyAdmin dengan benar.</p>";
    }

} else {
    echo "<p style='color:red; font-weight:bold;'>GAGAL: Pengguna dengan username '" . htmlspecialchars($username_to_test) . "' tidak ditemukan di tabel 'users'.</p>";
}

echo "</div>";

?>