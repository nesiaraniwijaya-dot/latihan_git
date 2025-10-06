<?php
$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'yayasan_purba_danarta';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>