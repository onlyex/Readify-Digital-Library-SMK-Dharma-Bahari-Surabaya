<?php
require_once __DIR__ . '/config.php';
if(!isset($_SESSION['user'])){ $_SESSION['msg']='Silakan login dulu.'; header('Location: login.php'); exit; }
$page_title = 'Ganti Password';
include __DIR__ . '/inc/header.php';

$userId = intval($_SESSION['user']['id']);
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $current = isset($_POST['current'])?$_POST['current']:'';
    $new = isset($_POST['new'])?$_POST['new']:'';
    $confirm = isset($_POST['confirm'])?$_POST['confirm']:'';

    if($current === '' || $new === '' || $confirm === ''){
        $error = 'Semua field harus diisi.';
    } elseif($new !== $confirm){
        $error = 'Password baru dan konfirmasi tidak cocok.';
    } elseif(strlen($new) < 6){
        $error = 'Password minimal 6 karakter.';
    } else {
        $row = $mysqli->query("SELECT password FROM users WHERE id=$userId")->fetch_assoc();
        if(!$row || !password_verify($current, $row['password'])){
            $error = 'Password saat ini salah.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $mysqli->query("UPDATE users SET password='".$mysqli->real_escape_string($hash)."' WHERE id=$userId");
            $_SESSION['msg'] = 'Password berhasil diubah.';
            // optional: regenerate session id
            session_regenerate_id(true);
            header('Location: '.(($_SESSION['user']['role']==='admin')?'/perpustakaan/admin/dashboard.php':'/perpustakaan/profile.php'));
            exit;
        }
    }
}
?>
<main class="container-fluid">
  <div class="panel" style="max-width:640px;margin:24px auto">
    <h1>Ganti Password</h1>
    <?php if($error): ?><p style="color:#c0392b"><?php echo esc($error); ?></p><?php endif; ?>
    <form method="post">
      <label>Password Saat ini
        <input class="form-control" type="password" name="current" id="current">
      </label>
      <label>Password Baru
        <input class="form-control" type="password" name="new" id="newpwd">
      </label>
      <label>Konfirmasi Password
        <input class="form-control" type="password" name="confirm" id="confpwd">
      </label>
      <div style="margin-top:10px;display:flex;gap:10px;align-items:center">
        <button class="btn btn-primary" type="submit">Simpan</button>
        <button type="reset" class="btn btn-ghost">Reset</button>
        <label style="margin-left:auto;display:flex;align-items:center;gap:8px"><input type="checkbox" id="showpw"> Tampilkan password</label>
      </div>
    </form>
  </div>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded',function(){
  var cb = document.getElementById('showpw');
  if(!cb) return;
  cb.addEventListener('change',function(){
    var v = this.checked ? 'text' : 'password';
    document.getElementById('current').type=v;
    document.getElementById('newpwd').type=v;
    document.getElementById('confpwd').type=v;
  });
});
</script>
