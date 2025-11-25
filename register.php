<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config.php';
$page_title = 'Register';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = isset($_POST['username']) ? trim($_POST['username']) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';
  if ($username === '' || $email === '' || $password === '') {
    $error = 'All fields are required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email address.';
  } else {
    $e = $mysqli->real_escape_string($email);
    $u = $mysqli->real_escape_string($username);
    $chk = $mysqli->query("SELECT id FROM users WHERE email='" . $e . "' OR username='" . $u . "' LIMIT 1");
    if ($chk && $chk->num_rows > 0) {
      $error = 'Email or username already in use.';
    } else {
      $pw = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $mysqli->prepare('INSERT INTO users (username,email,password,role) VALUES (?,?,?,"user")');
      if ($stmt) {
        $stmt->bind_param('sss', $username, $email, $pw);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$newId, 'username' => $username, 'email' => $email, 'role' => 'user'];
        $_SESSION['msg'] = 'Account created and logged in.';
        header('Location: index.php');
        exit;
      } else {
        $error = 'Could not create account (DB error).';
      }
    }
  }
}
// inject extra_head agar header.php bisa menambah <link> khusus halaman ini
$extra_head = '<link rel="stylesheet" href="assets/css/login.css">';
$extra_body_class = 'register-page';
include 'inc/header.php';
?>
<div class="register-container">
  <div class="register-right">
    <div class="form-box register-form-box fadeIn">
      <h2>Daftar</h2>
      <p>Buat akun baru</p>
      <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="input-group">
          <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="input-group">
          <input type="email" name="email" placeholder="E-Mail" required>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn">Daftar</button>
      </form>
      <p class="bottom-text">Sudah punya akun? <a href="login.php">Masuk</a></p>
    </div>
  </div>
  <div class="register-left">
    <img src="assets/img/login.jpeg" class="side-img">
  </div>
</div>
<?php include 'inc/footer.php'; ?>
<!-- Animasi register -->
<script src="assets/js/login.js"></script>