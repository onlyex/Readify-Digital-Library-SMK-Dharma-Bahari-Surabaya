<?php
require_once __DIR__ . '/config.php';
$page_title = 'Pengumuman';
include __DIR__ . '/inc/header.php';

$anns = [];
try{
  $res = $mysqli->query("SELECT id,title,content,created_at FROM announcements ORDER BY created_at DESC LIMIT 20");
  if($res) $anns = $res->fetch_all(MYSQLI_ASSOC);
}catch(Exception $e){
  $anns = [];
}
?>
<main class="container-fluid">
  <h1>Pengumuman</h1>
  <?php if(empty($anns)): ?>
    <?php
    // sample announcements to show while DB empty
    $sample = [
      ['title'=>'Perpustakaan Tutup Sementara','created_at'=>date('Y-m-d H:i'),'content'=>'Perpustakaan akan tutup pada hari libur nasional. Silakan periksa jam operasional kami.'],
      ['title'=>'Donasi Buku Baru','created_at'=>date('Y-m-d H:i',strtotime('-2 days')),'content'=>'Terima kasih atas donasi buku terbaru. Beberapa koleksi baru sudah tersedia di rak.']
    ];
    foreach($sample as $s): ?>
      <article class="ann-card">
        <h3><?php echo htmlspecialchars($s['title']); ?></h3>
        <div class="meta"><?php echo htmlspecialchars($s['created_at']); ?></div>
        <div><?php echo nl2br(htmlspecialchars($s['content'])); ?></div>
      </article>
    <?php endforeach; ?>
  <?php else: ?>
    <?php foreach($anns as $a): ?>
      <article class="ann-card">
        <h3><?php echo esc($a['title']); ?></h3>
        <div class="meta"><?php echo esc($a['created_at']); ?></div>
        <div><?php echo nl2br(esc($a['content'])); ?></div>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/inc/footer.php'; ?>
