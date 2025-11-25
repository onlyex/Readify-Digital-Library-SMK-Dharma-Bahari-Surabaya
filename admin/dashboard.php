<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!=='admin'){
    header('Location: ../index.php'); exit;
}
$page_title='Admin Dashboard';
include '../inc/header.php';
// fetch counts
$totBooks = $mysqli->query('SELECT COUNT(*) as c FROM books')->fetch_assoc()['c'] ?? 0;
$totUsers = $mysqli->query('SELECT COUNT(*) as c FROM users')->fetch_assoc()['c'] ?? 0;
$totBorrows = $mysqli->query('SELECT COUNT(*) as c FROM borrows WHERE status="borrowed"')->fetch_assoc()['c'] ?? 0;
 $totCats = $mysqli->query('SELECT COUNT(*) as c FROM categories')->fetch_assoc()['c'] ?? 0;
 $totAnns = $mysqli->query('SELECT COUNT(*) as c FROM announcements')->fetch_assoc()['c'] ?? 0;
?>
<main class="container-fluid admin-wrapper">
  <div class="admin-header">
    <div>
      <h1 class="admin-title">Admin Dashboard</h1>
      <div class="admin-sub">Ringkasan singkat aktivitas perpustakaan</div>
    </div>
    <div class="admin-actions">
        <div class="quick-links">
          <div class="quick-links-grid">
            <a class="quick-link-card manage" href="books.php">
              <div class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Books"><path d="M3 5a2 2 0 012-2h12a1 1 0 011 1v14a1 1 0 01-1 1H5a2 2 0 01-2-2V5zM5 5h12V4H5a1 1 0 00-1 1v0z"/></svg>
              </div>
              <div>Tambah / Edit Buku</div>
              <div class="count"><?php echo intval($totBooks); ?></div>
            </a>
            <a class="quick-link-card categories" href="categories.php">
              <div class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Categories"><path d="M3 7a2 2 0 012-2h4a1 1 0 001-1V3h6a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
              </div>
              <div>Kelola Kategori</div>
              <div class="count"><?php echo intval($totCats); ?></div>
            </a>
            <a class="quick-link-card borrows" href="borrows.php">
              <div class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Borrows"><path d="M21 12a9 9 0 10-2.6 6.1l1.7-1.7A7 7 0 1119 12h2v-1l4 4-4 4v-1h-2z"/></svg>
              </div>
              <div>Kelola Peminjaman</div>
              <div class="count"><?php echo intval($totBorrows); ?></div>
            </a>
            <a class="quick-link-card users" href="users.php">
              <div class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Users"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM6 8c0-1.66 1.34-3 3-3s3 1.34 3 3-1.34 3-3 3-3-1.34-3-3zM6 14c-2.67 0-8 1.34-8 4v1h20v-1c0-2.66-5.33-4-8-4H6z"/></svg>
              </div>
              <div>Kelola Pengguna</div>
              <div class="count"><?php echo intval($totUsers); ?></div>
            </a>
          </div>
        </div>
        <div style="margin-left:12px;">
          <a class="btn btn-outline" href="/perpustakaan/change_password.php">Ganti Password</a>
        </div>
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon">B</div>
      <div class="stat-body">
        <div class="stat-value"><?php echo intval($totBooks); ?></div>
        <div class="stat-label">Jumlah Buku</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon">U</div>
      <div class="stat-body">
        <div class="stat-value"><?php echo intval($totUsers); ?></div>
        <div class="stat-label">Pengguna Terdaftar</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon">⟳</div>
      <div class="stat-body">
        <div class="stat-value"><?php echo intval($totBorrows); ?></div>
        <div class="stat-label">Peminjaman Aktif</div>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start">
    <div class="panel">
      <h3>Recent Activity</h3>
      <p class="admin-sub">Terakhir 10 aktivitas peminjaman / pengembalian / pengumuman.</p>
      <ul style="margin:12px 0 0;padding-left:18px;color:var(--muted);">
        <?php
          $rows = $mysqli->query('SELECT br.id, u.username, b.title, br.borrowed_at, br.status FROM borrows br LEFT JOIN users u ON br.user_id=u.id LEFT JOIN books b ON br.book_id=b.id ORDER BY br.borrowed_at DESC LIMIT 10');
          if($rows){
            while($r = $rows->fetch_assoc()){
              echo '<li style="margin-bottom:8px;"><strong>'.esc($r['username']).'</strong> → '.esc($r['title']).' <small style="color:var(--muted);">('.esc($r['status']).' @ '.esc($r['borrowed_at']).')</small></li>';
            }
          } else {
            echo '<li style="color:var(--muted)">No recent activity</li>';
          }
        ?>
      </ul>
    </div>

    <div class="panel">
      <h3>Quick Links</h3>
      <div class="quick-links">
        <a href="books.php">Tambah / Edit Buku</a>
        <a href="categories.php">Kelola Kategori</a>
        <a href="announcements.php">Buat Pengumuman</a>
        <a href="users.php">Kelola Pengguna</a>
      </div>
    </div>
  </div>

</main>
<?php include '../inc/footer.php'; ?>
