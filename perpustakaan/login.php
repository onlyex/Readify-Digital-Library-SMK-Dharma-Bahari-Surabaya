<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if ($identifier === '' || $password === '') {
        $error = 'Semua field wajib diisi!';
    } else {
        $stmt = $mysqli->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $username, $emailDB, $hashedPass, $role);
                $stmt->fetch();
                if (password_verify($password, $hashedPass)) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int)$id,
                        'username' => $username,
                        'email' => $emailDB,
                        'role' => $role ?? 'user'
                    ];
                    // redirect according to role (admin -> admin dashboard)
                    if(isset($role) && $role === 'admin'){
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                } else {
                    $error = "Password salah!";
                }
        } else {
            $error = "Akun tidak ditemukan!";
        }
        $stmt->close();
    }
}
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<?php 
$extra_head = '<link rel="stylesheet" href="assets/css/login.css">';
$extra_body_class = 'login-page';
include 'inc/header.php'; 
?>
<div class="login-container">
    <div class="login-left">
        <img src="assets/img/login.jpeg" class="side-img" alt="Books">
    </div>
    <div class="login-right">
        <div class="form-box login-form-box fadeIn">
            <h2>Login</h2>
            <p>Masuk ke akun Anda</p>
            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="identifier" placeholder="Username atau Email" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <p class="bottom-text">Belum punya akun? <a href="register.php">Daftar</a></p>
        </div>
    </div>
</div>
<?php include 'inc/footer.php'; ?>
<!-- Animasi login -->
<script src="assets/js/login.js"></script>