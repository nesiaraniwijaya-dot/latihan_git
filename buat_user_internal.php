<?php
require_once 'config/database.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Jika rolenya penanggung jawab, ambil divisi. Jika tidak, set ke NULL.
    $divisi = ($role == 'penanggung_jawab') ? $_POST['divisi'] : NULL;

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $conn->begin_transaction();
    try {
        // Masukkan ke tabel 'users'
        $stmt_user = $conn->prepare("INSERT INTO users (username, email, nama_lengkap, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt_user->bind_param("sssss", $username, $email, $nama_lengkap, $hashed_password, $role);
        $stmt_user->execute();
        $user_id = $stmt_user->insert_id;

        // Masukkan juga ke tabel 'karyawan'
        $stmt_karyawan = $conn->prepare("INSERT INTO karyawan (user_id, nama_lengkap, nik, email, no_telepon, divisi, tanggal_bergabung) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $nik_dummy = '0000000000000000';
        $telp_dummy = '0000000000';
        $stmt_karyawan->bind_param("isssss", $user_id, $nama_lengkap, $nik_dummy, $email, $telp_dummy, $divisi);
        $stmt_karyawan->execute();

        $conn->commit();
        $message = "SUKSES! Pengguna '$username' dengan peran '$role' berhasil dibuat.";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "GAGAL! Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Alat Pendaftaran Internal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-container { max-width: 600px; } 
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; } 
        .success { background-color: #d4edda; color: #155724; } 
        .error { background-color: #f8d7da; color: #721c24; }
        .hidden { display: none; } /* Class untuk menyembunyikan elemen */
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Alat Pendaftaran Pengguna Internal</h2>
        <p>Gunakan form ini untuk membuat akun Administrator, Penanggung Jawab, atau Direksi.</p>

        <?php if ($message): ?>
            <div class="message <?php echo (strpos($message, 'SUKSES') !== false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="buat_user_internal.php" method="POST">
            <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
            <div class="form-group"><label>Password</label><input type="text" name="password" required></div>
            <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group">
                <label>Role / Peran</label>
                <select id="role_select" name="role" required>
                    <option value="administrator">Administrator</option>
                    <option value="penanggung_jawab">Penanggung Jawab</option>
                    <option value="direksi">Direksi</option>
                </select>
            </div>
            
            <div class="form-group hidden" id="divisi_form_group">
                <label>Pilih Divisi yang Ditanggungjawabkan</label>
                <select name="divisi">
                    <option value="Training">Training</option>
                    <option value="Konsultasi">Konsultasi</option>
                    <option value="Wisma">Wisma</option>
                    <option value="Keuangan">Keuangan</option>
                    <option value="Sekretariat">Sekretariat</option>
                </select>
            </div>
            
            <button type="submit">Buat Pengguna</button>
        </form>
    </div>

    <script>
        // Ambil elemen select untuk role dan form group untuk divisi
        const roleSelect = document.getElementById('role_select');
        const divisiFormGroup = document.getElementById('divisi_form_group');

        // Buat fungsi untuk menampilkan/menyembunyikan pilihan divisi
        function toggleDivisiSelect() {
            if (roleSelect.value === 'penanggung_jawab') {
                divisiFormGroup.classList.remove('hidden'); // Tampilkan
            } else {
                divisiFormGroup.classList.add('hidden'); // Sembunyikan
            }
        }

        // Jalankan fungsi saat ada perubahan pada pilihan role
        roleSelect.addEventListener('change', toggleDivisiSelect);
    </script>
</body>
</html>