<?php
session_start();
require_once 'config/database.php';
$error = '';

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'pelamar': header("Location: pelamar_dashboard.php"); break;
        case 'karyawan': header("Location: karyawan_dashboard.php"); break;
        case 'administrator': header("Location: admin/index.php"); break;
        case 'penanggung_jawab': header("Location: penanggung_jawab/index.php"); break;
        case 'direksi': header("Location: direksi/index.php"); break;
        default: header("Location: login.php"); break;
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role, nama_lengkap FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nama'] = $user['nama_lengkap'];

            switch ($user['role']) {
                case 'pelamar': header("Location: pelamar_dashboard.php"); break;
                case 'karyawan': header("Location: karyawan_dashboard.php"); break;
                case 'administrator': header("Location: admin/index.php"); break;
                default: header("Location: login.php"); break;
            }
            exit();
        } else { $error = "Username atau password salah."; }
    } else { $error = "Username atau password salah."; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Yayasan Purba Danarta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="form-container" style="max-width: 450px;">
        <div class="login-logo"><img src="assets/img/logo.png" alt="Logo Yayasan Purba Danarta"></div>
        <?php if ($error): ?><div class="error-box"><p><?php echo $error; ?></p></div><?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <button type="submit">Login</button>
        </form>
        <div class="link-bawah">Belum punya akun? <a href="register.php">Daftar sebagai pelamar</a></div>
    </div>
</body>
</html>