<?php
require_once 'config/database.php';
$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- LOGIKA PENGECEKAN ATURAN BARU ---

    $nik_pelamar = $_POST['nik'];
    $email_pelamar = $_POST['email'];
    $divisi_lamaran = $_POST['divisi_lamaran'];

    // Aturan 2: Cek apakah NIK terdaftar sebagai mantan karyawan
    $stmt_cek_mantan = $conn->prepare("SELECT id FROM karyawan WHERE nik = ? AND status_karyawan = 'Resign'");
    $stmt_cek_mantan->bind_param("s", $nik_pelamar);
    $stmt_cek_mantan->execute();
    if ($stmt_cek_mantan->get_result()->num_rows > 0) {
        $errors[] = "Maaf, Anda terdeteksi sebagai mantan karyawan dan tidak dapat mengajukan lamaran kembali.";
    }
    $stmt_cek_mantan->close();

    // Aturan 1: Cek apakah pelamar pernah ditolak di divisi yang sama
    if (empty($errors)) {
        $stmt_cek_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_cek_user->bind_param("s", $email_pelamar);
        $stmt_cek_user->execute();
        $result_user = $stmt_cek_user->get_result();
        if ($result_user->num_rows > 0) {
            $user = $result_user->fetch_assoc();
            $user_id = $user['id'];
            
            $stmt_cek_divisi = $conn->prepare("SELECT id FROM lamaran WHERE user_id = ? AND divisi_lamaran = ? AND (status LIKE '%Gagal%' OR status LIKE '%Ditolak%')");
            $stmt_cek_divisi->bind_param("is", $user_id, $divisi_lamaran);
            $stmt_cek_divisi->execute();
            if ($stmt_cek_divisi->get_result()->num_rows > 0) {
                $errors[] = "Maaf, Anda sudah pernah ditolak untuk melamar di divisi ini. Silakan pilih divisi lain.";
            }
            $stmt_cek_divisi->close();
        }
        $stmt_cek_user->close();
    }
    
    // Jika tidak ada error dari aturan di atas, lanjutkan proses registrasi
    if (empty($errors)) {
        try {
            // (Kode registrasi menggunakan Stored Procedure tetap sama seperti sebelumnya)
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $ipk = !empty($_POST['ipk']) ? $_POST['ipk'] : NULL;
            $gaji = !empty($_POST['perkiraan_gaji']) ? $_POST['perkiraan_gaji'] : NULL;
            
            // Panggil Stored Procedure (posisi_yang_dilamar kita isi dengan nama divisi)
            $stmt = $conn->prepare("CALL DaftarkanPelamarBaru(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssssds",
                $_POST['username'], $_POST['email'], $_POST['nama_lengkap'], $hashed_password,
                $divisi_lamaran, // Menggunakan divisi sebagai posisi
                $_POST['nik'], $_POST['gender'], $_POST['agama'], $_POST['alamat'],
                $_POST['nomor_telepon'], $_POST['kontak_darurat'], $_POST['pendidikan_terakhir'],
                $ipk, $gaji
            );
            
            $stmt->execute();
            // Perbarui juga kolom divisi_lamaran
            $update_divisi_query = "UPDATE lamaran SET divisi_lamaran = ? WHERE id = (SELECT LAST_INSERT_ID())";
            $stmt_update_divisi = $conn->prepare($update_divisi_query);
            $stmt_update_divisi->bind_param("s", $divisi_lamaran);
            $stmt_update_divisi->execute();
            
            // ... sisa kode untuk upload file ...
            $success_message = "Pendaftaran berhasil! Lamaran Anda sedang kami proses.";
        } catch (Exception $e) {
            if (isset($conn) && $conn->errno == 1062) { $errors[] = "Username atau Email sudah terdaftar."; } 
            else { $errors[] = "Terjadi kesalahan: " . $e->getMessage(); }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Lamaran Kerja</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <div class="login-logo"><img src="assets/img/logo.png" alt="Logo Yayasan"></div>
        <h2>Formulir Lamaran Kerja</h2>
        <p>Silakan lengkapi formulir dengan benar dan tepat. (*) Wajib diisi</p>
        
        <?php if (!empty($errors)): ?><div class="error-box"><?php foreach ($errors as $e): echo "<p>$e</p>"; endforeach; ?></div><?php endif; ?>
        <?php if ($success_message): ?><div class="success-box"><p><?php echo $success_message; ?></p></div>
        <?php else: ?>
            <form action="register.php" method="POST" enctype="multipart/form-data">
                <h4>Informasi Pribadi & Lamaran</h4>
                <div class="form-row">
                    <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama_lengkap" placeholder="Contoh: Budi Santoso" required></div>
                    
                    <div class="form-group"><label>Posisi yang Dilamar *</label><input type="text" name="divisi" placeholder="Contoh: Training" required></div>
                </div>

                <div class="form-row">
                    <div class="form-group"><label>NIK *</label><input type="text" name="nik" placeholder="16 digit NIK Anda" required maxlength="16" pattern="\d{16}" title="Harus 16 digit angka"></div>
                    <div class="form-group"><label>Gender *</label><select name="gender" required><option value="">Pilih Jenis Kelamin</option><option>Laki-laki</option><option>Perempuan</option></select></div>
                </div>
                <div class="form-group"><label>Agama *</label><select name="agama" required><option value="">Pilih Agama</option><option>Islam</option><option>Kristen</option><option>Katolik</option><option>Hindu</option><option>Buddha</option><option>Konghuchu</option><option>Kepercayaan</option></select></div>
                <div class="form-group"><label>Alamat *</label><textarea name="alamat" rows="3" placeholder="Alamat sesuai dengan KTP dan tempat tinggal terbaru" required></textarea></div>
                <h4>Kontak</h4>
                <div class="form-row"><div class="form-group"><label>Nomor Telepon *</label><input type="tel" name="nomor_telepon" placeholder="Contoh: 081234567890" required></div><div class="form-group"><label>Email *</label><input type="email" name="email" placeholder="Contoh: budi.santoso@email.com" required></div></div>
                <div class="form-group"><label>Kontak Darurat</label><input type="text" name="kontak_darurat" placeholder="Contoh: Siti (Ibu) - 081987654321"></div>
                <h4>Pendidikan dan Gaji yang Diharapkan</h4>
                <div class="form-row"><div class="form-group"><label>Pendidikan Terakhir *</label><select name="pendidikan_terakhir" required><option value="">Pilih Tingkat Pendidikan</option><option>SMA/SMK</option><option>D3</option><option>S1</option><option>S2</option><option>S3</option></select></div><div class="form-group"><label>IPK</label><input type="text" name="ipk" placeholder="Contoh: 3.75"></div><div class="form-group"><label>Gaji yang Diharapkan</label><input type="number" name="perkiraan_gaji" placeholder="Contoh: 5000000"></div></div>
                <h4>Unggah Dokumen</h4>
                <div class="form-row"><div class="form-group"><label>Surat Lamaran *</label><input type="file" name="surat_lamaran" required></div><div class="form-group"><label>CV/Resume *</label><input type="file" name="cv_resume" required></div></div>
                <div class="form-row"><div class="form-group"><label>Pasfoto Formal *</label><input type="file" name="pasfoto" required></div><div class="form-group"><label>Kartu Identitas (Optional)</label><input type="file" name="ktp"></div></div>
                <div class="form-row"><div class="form-group"><label>Ijazah & Transkrip Nilai *</label><input type="file" name="ijazah" required></div><div class="form-group"><label>Dokumen Pendukung (Optional)</label><input type="file" name="dokumen_pendukung"></div></div>
                <h4>Buat Akun</h4>
                <div class="form-row"><div class="form-group"><label>Username *</label><input type="text" name="username" placeholder="Buat username unik" required></div><div class="form-group"><label>Password *</label><input type="password" name="password" placeholder="Buat password yang kuat" required></div></div>

                <div class="button-group">
                    <button type="reset">Kembali</button>
                    <button type="submit">Kirim</button>
                </div>
            </form>
        <?php endif; ?>
         <div class="link-bawah">Sudah punya akun? <a href="login.php">Login di sini</a></div>
    </div>
</body>
</html>