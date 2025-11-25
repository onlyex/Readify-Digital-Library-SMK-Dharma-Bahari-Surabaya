<?php
require_once __DIR__ . '/config.php';
$page = 'categories';
$page_title = 'Kategori';
include __DIR__ . '/inc/header.php';

// fetch categories if table exists (include thumb_path)
$cats = [];
try{
  $res = $mysqli->query("SELECT id,name,description,thumb_path FROM categories ORDER BY name ASC");
  if($res) $cats = $res->fetch_all(MYSQLI_ASSOC);
}catch(Exception $e){
  $cats = [];
}
?>
<main class="container-fluid">
  <h1>Kategori Buku</h1>
  <div class="cat-grid">
  <?php if(empty($cats)): ?>
    <?php
    // fallback sample categories
    $sample = [
      ['name'=>'Fantasy','description'=>'Dunia imajinatif & cerita epik','thumb'=>'/perpustakaan/assets/img/genres/fantasy.jpg'],
      ['name'=>'Science Fiction','description'=>'Teknologi dan masa depan','thumb'=>'/perpustakaan/assets/img/genres/scifi.jpg'],
      ['name'=>'History','description'=>'Kisah dan peristiwa nyata','thumb'=>'/perpustakaan/assets/img/genres/history.jpg'],
      ['name'=>'Romance','description'=>'Cerita cinta & hubungan','thumb'=>'/perpustakaan/assets/img/genres/romance.jpg'],
      ['name'=>'Children','description'=>'Buku anak-anak & pendidikan','thumb'=>'/perpustakaan/assets/img/genres/children.jpg'],
      ['name'=>'Mystery','description'=>'Teka-teki & thriller','thumb'=>'/perpustakaan/assets/img/genres/mystery.jpg']
    ];
    foreach($sample as $s): ?>
      <a class="cat-card" href="/perpustakaan/catalog.php?genre=<?php echo urlencode($s['name']); ?>">
        <div class="cat-thumb-wrap">
          <img loading="lazy" src="<?php echo $s['thumb']; ?>" alt="<?php echo htmlspecialchars($s['name']); ?>" class="cat-thumb">
          <div class="cat-overlay"><span class="btn btn-outline">Lihat</span></div>
        </div>
        <h4><?php echo htmlspecialchars($s['name']); ?></h4>
        <p><?php echo htmlspecialchars($s['description']); ?></p>
      </a>
    <?php endforeach; ?>
  <?php else: ?>
    <?php foreach($cats as $c): ?>
      <a class="cat-card" href="/perpustakaan/catalog.php?category=<?php echo intval($c['id']); ?>">
        <div class="cat-thumb-wrap">
          <?php
            $thumb = !empty($c['thumb_path']) ? '/' . ltrim($c['thumb_path'], '/') : '/perpustakaan/assets/img/book-placeholder.png';
          ?>
          <img loading="lazy" src="<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo esc($c['name']); ?>" class="cat-thumb">
          <div class="cat-overlay"><span class="btn btn-outline">Lihat</span></div>
        </div>
        <h4><?php echo esc($c['name']); ?></h4>
        <?php if(!empty($c['description'])): ?><p><?php echo esc($c['description']); ?></p><?php endif; ?>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/inc/footer.php'; ?>
