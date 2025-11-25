<?php
require_once __DIR__ . '/config.php';
if(!isset($_SESSION['user'])){ $_SESSION['msg']='Silakan login untuk melihat peminjaman Anda.'; header('Location: login.php'); exit; }
$page_title='Peminjaman Saya';
include __DIR__ . '/inc/header.php';

$uid = intval($_SESSION['user']['id']);
// handle request action
if(isset($_GET['action']) && $_GET['action']==='request' && isset($_GET['id'])){
  $id=intval($_GET['id']);
  $b = $mysqli->query("SELECT * FROM borrows WHERE id=$id AND user_id=$uid AND status='borrowed'")->fetch_assoc();
  if($b){
    $mysqli->query("UPDATE borrows SET status='return_requested' WHERE id=$id");
    $_SESSION['msg'] = 'Permintaan pengembalian dikirim ke admin.';
    header('Location: my_borrows.php'); exit;
  } else {
    $_SESSION['msg'] = 'Tidak bisa meminta pengembalian.';
    header('Location: my_borrows.php'); exit;
  }
}

if(isset($_GET['action']) && $_GET['action']==='cancel' && isset($_GET['id'])){
  $id=intval($_GET['id']);
  $b = $mysqli->query("SELECT * FROM borrows WHERE id=$id AND user_id=$uid AND status='return_requested'")->fetch_assoc();
  if($b){
    $mysqli->query("UPDATE borrows SET status='borrowed' WHERE id=$id");
    $_SESSION['msg'] = 'Permintaan pengembalian dibatalkan.';
  }
  header('Location: my_borrows.php'); exit;
}

$rows = $mysqli->query("SELECT br.*, b.title, b.cover_path FROM borrows br LEFT JOIN books b ON br.book_id=b.id WHERE br.user_id=$uid ORDER BY br.borrowed_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<main class="container-fluid">
  <h1>Peminjaman Saya</h1>
  <div class="panel">
    <?php if(empty($rows)): ?>
      <p>Anda belum meminjam buku apapun.</p>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td><strong><?php echo esc($r['title']); ?></strong></td>
              <td><?php echo esc($r['borrowed_at']); ?></td>
              <td><?php echo esc($r['due_at']); ?></td>
              <td><?php echo esc($r['returned_at']); ?></td>
              <td><?php echo esc($r['status']); ?></td>
              <td>
                <?php if($r['status']==='borrowed'): ?>
                  <a class="btn" href="?action=request&id=<?php echo intval($r['id']); ?>">Minta Pengembalian</a>
                <?php elseif($r['status']==='return_requested'): ?>
                  <span style="color:#c67">Menunggu persetujuan</span>
                  <a class="btn btn-ghost" href="?action=cancel&id=<?php echo intval($r['id']); ?>" style="margin-left:8px">Batalkan</a>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>
