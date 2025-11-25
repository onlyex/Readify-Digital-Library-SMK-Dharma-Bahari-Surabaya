<?php
require_once __DIR__ . '/config.php';
if(!isset($_SESSION['user'])){ $_SESSION['msg']='Silakan login untuk melihat profil.'; header('Location: login.php'); exit; }
$page_title = 'Profil Saya';
include __DIR__ . '/inc/header.php';

$uid = intval($_SESSION['user']['id']);
$user = $mysqli->query("SELECT id,username,email,role,created_at,avatar_path FROM users WHERE id=$uid LIMIT 1")->fetch_assoc();
?>
<main class="container-fluid">
  <div class="panel" style="max-width:720px;margin:20px auto">
    <h1><svg viewBox="0 0 24 24" width="40" height="40" style="vertical-align:middle;margin-right:8px"><path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z" fill="#05304a"/></svg> <span class="visually-hidden">Profil Saya</span></h1>
    <p class="admin-sub">Informasi akun dan preferensi</p>
    <table class="table" style="margin-top:12px">
      <tr><th>ID</th><td><?php echo intval($user['id']); ?></td></tr>
      <tr><th>Username</th><td><?php echo esc($user['username']); ?></td></tr>
      <tr><th>Email</th><td><?php echo esc($user['email']); ?></td></tr>
      <tr><th>Role</th><td><?php echo esc($user['role']); ?></td></tr>
      <tr><th>Bergabung</th><td><?php echo esc($user['created_at']); ?></td></tr>
    </table>
    <div style="margin-top:12px;display:flex;gap:10px;align-items:center">
      <a class="btn btn-primary" href="change_password.php">Ganti Password</a>
      <a class="btn btn-outline" href="my_borrows.php">Peminjaman Saya</a>
      <?php if($_SESSION['user']['role']==='admin'): ?><a class="btn btn-outline" href="admin/dashboard.php">Dashboard</a><?php endif; ?>
      <form method="post" action="profile.php" enctype="multipart/form-data" style="margin-left:auto;display:flex;gap:8px;align-items:center">
        <label class="input-file">
          <input type="file" name="avatar" accept="image/*">
          <span class="btn btn-ghost">Upload</span>
        </label>
        <button class="btn" type="submit" name="save_avatar">Simpan</button>
      </form>
    </div>
  </div>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>

<?php
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['tmp_name']){
  $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
  $fn = 'uploads/avatars/'.time().'_'.rand(1000,9999).'.'.$ext;
  @mkdir(__DIR__.'/uploads/avatars',0755,true);
  if(move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__.'/'.$fn)){
    // remove existing if exists
    if(!empty($user['avatar_path']) && file_exists(__DIR__.$user['avatar_path'])) @unlink(__DIR__.$user['avatar_path']);
    $mysqli->query("UPDATE users SET avatar_path='".$mysqli->real_escape_string('/'.$fn)."' WHERE id=$uid");
    $_SESSION['msg'] = 'Avatar berhasil diupload.'; header('Location: profile.php'); exit;
  } else { $_SESSION['msg'] = 'Gagal upload avatar.'; header('Location: profile.php'); exit; }
}

