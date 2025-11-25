<?php
// Load configuration (shim may point to inc/config.php)
require_once __DIR__ . '/config.php';
$page_title='Home';
$page = 'index';
// include header from inc/ if available — fallback with clear error
$headerPath = __DIR__ . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'header.php';
if(file_exists($headerPath)){
  include $headerPath;
} else {
  // attempt common alternative
  $alt = __DIR__ . DIRECTORY_SEPARATOR . 'header.php';
  if(file_exists($alt)){
    include $alt;
  } else {
    http_response_code(500);
    echo "<h1>Include error</h1><p>Missing header file. Expected <code>inc/header.php</code>.</p>";
    exit;
  }

}
  // If no explicit slider background was provided, use the first book cover
  if(empty($slider_bg) && !empty($rows)){
    $first = $rows[0];
    $slider_bg = $first['cover_path'] ?? ($first['cover'] ?? '/perpustakaan/assets/img/book-placeholder.png');
  }

  // Prefer explicit slide images placed in `assets/img` named "slide 1", "slide 2", "slide 3"
  $slider_bg_list = [];
  for($i=1;$i<=3;$i++){
    $candidates = ["slide $i.jpeg","slide $i.jpg","slide $i.png"];
    foreach($candidates as $cand){
      $local = __DIR__ . '/assets/img/' . $cand;
      if(file_exists($local)){
        $slider_bg_list[] = '/perpustakaan/assets/img/' . $cand;
        break;
      }
    }
  }

  // If no custom slides found, fall back to single background (book cover) if available
  if(empty($slider_bg_list)){
    if(!empty($slider_bg)) $slider_bg_list = [$slider_bg];
  }

  ?>
<!-- Hero moved into slider caption for a cleaner layout -->

<!-- Large full-width slider area -->
<!-- Smart Slider: dynamic, modern, responsive -->
<?php
// Usage: set `$data` before this block, e.g.:
// $data = mysqli_query($conn, "SELECT id, judul, penulis, cover FROM buku ORDER BY id DESC LIMIT 10");
// The slider will consume the mysqli_result in `$data` and WILL NOT execute any
// fallback queries. If `$data` is not provided, the slider shows an empty state.

// Optional: provide a custom background image for the smart slider by setting
// the PHP variable `$slider_bg` to a web-accessible path (relative or absolute).
// Example (uncomment and adjust path):
// $slider_bg = '/perpustakaan/uploads/slider-bg.jpg';

// Auto-populate `$data` from a known books table if not provided.
// Try the English `books` table first, then the Indonesian `buku` table as a fallback.
// This makes the slider show books you import or create via admin tools.
if((!isset($data) || !$data) && isset($mysqli)){
  $r = $mysqli->query("SELECT id, title, author, cover_path FROM books ORDER BY id DESC LIMIT 12");
  if($r && $r->num_rows){
    $data = $r;
  } else {
    // try Indonesian schema `buku` (judul, penulis, cover)
    $r2 = $mysqli->query("SELECT id, judul AS title, penulis AS author, cover AS cover_path FROM buku ORDER BY id DESC LIMIT 12");
    if($r2 && $r2->num_rows){
      $data = $r2;
    }
  }
}

function _h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
$rows = [];
if(isset($data) && $data){
  // support both object and procedural mysqli result handling
  if(is_object($data) && method_exists($data, 'fetch_assoc')){
    while($r = $data->fetch_assoc()) $rows[] = $r;
  } else {
    // procedural style (resource)
    while($r = mysqli_fetch_assoc($data)) $rows[] = $r;
  }
}
?>

<?php
// choose first available bg (slide images preferred, then explicit slider_bg)
$hero_bg = _h($slider_bg_list[0] ?? ($slider_bg ?? '/perpustakaan/assets/img/slide 1.jpeg'));
?>
<section id="smartSliderModern" class="smart-slider-modern" aria-label="Smart Book Slider" data-bg-list='<?php echo htmlspecialchars(json_encode($slider_bg_list), ENT_QUOTES); ?>' style="background-image: url('<?php echo $hero_bg; ?>'); background-size: cover; background-position: center center; background-repeat: no-repeat; background-attachment: scroll;">
  <!-- background applied to section element to avoid other overlays hiding the hero -->
  <div class="ss-bg" aria-hidden="true" style="display:none; background-image: url('<?php echo $hero_bg; ?>');"></div>
  <div class="ss-bg ss-bg-alt" aria-hidden="true" style="display:none; background-image: none; opacity: 0;"></div>
  <div class="ss-inner">
    <aside class="ss-left-col">
      <div class="ss-welcome">
        <h1>Selamat Datang di Website SMK Dharma Bahari</h1>
        <p>Website ini menyediakan katalog buku perpustakaan dan berbagai informasi sekolah. Temukan buku pelajaran, referensi, dan bahan bacaan untuk mendukung proses belajar mengajar serta kegiatan ekstrakurikuler.</p>
        <p style="margin-top:10px;color:var(--muted);font-size:13px">Gunakan menu untuk menelusuri katalog, ajukan permintaan pinjam, dan lihat pengumuman terbaru. Jika belum memiliki akun, silakan daftar terlebih dahulu.</p>
      </div>
      <div class="ss-left-actions">
        <a class="btn btn-primary" href="/perpustakaan/register.php">Daftar</a>
        <a class="btn btn-outline" href="/perpustakaan/login.php">Login</a>
      </div>
    </aside>

    <div class="ss-right-col">
      <div class="ss-viewport" aria-hidden="false">
        <div class="ss-track">
          <?php if(empty($rows)): ?>
            <div class="ss-slide">
              <div class="ss-card empty">
                <div class="ss-cover-placeholder"></div>
                <div class="ss-meta">
                  <h3>Tidak ada buku</h3>
                  <p>Belum ada buku terbaru untuk ditampilkan.</p>
                </div>
              </div>
            </div>
          <?php else: foreach($rows as $r):
              $title = $r['title'] ?? ($r['judul'] ?? 'Untitled');
              $author = $r['author'] ?? ($r['penulis'] ?? '');
              $cover = $r['cover_path'] ?? ($r['cover'] ?? '/perpustakaan/assets/img/book-placeholder.png');
          ?>
          <div class="ss-slide" role="group" aria-label="<?php echo _h($title); ?>">
            <article class="ss-card" tabindex="0">
              <div class="ss-cover-wrap">
                <img class="ss-cover" src="<?php echo _h($cover); ?>" alt="<?php echo _h($title); ?> cover" loading="lazy">
              </div>
              <div class="ss-meta">
                <h3 class="ss-title"><?php echo _h($title); ?></h3>
                <p class="ss-author"><?php echo _h($author); ?></p>
              </div>
            </article>
          </div>
          <?php endforeach; endif; ?>
        </div>
      
        <!-- controls placed inside viewport so buttons sit beside the slider -->
        <button class="ss-btn ss-prev" aria-label="Previous slide">&lsaquo;</button>
        <button class="ss-btn ss-next" aria-label="Next slide">&rsaquo;</button>
      </div>
      <div class="ss-controls">
        <div class="ss-dots" role="tablist" aria-label="Slider pagination"></div>
      </div>
    </div>
  </div>
</section>


<!-- Smart Book Slider: Populer / Baru / Rekomendasi -->
<section class="section alt">
  <div class="container-fluid">
    <h2>Buku Pilihan</h2>
    <p>Telusuri koleksi singkat: Populer, Baru, dan Rekomendasi.</p>
    <?php
      // Popular: by number of borrows (if borrows table exists)
      $popularBooks = [];
      $q = "SELECT b.id,b.title,b.author,b.cover_path,COUNT(br.id) AS borrows FROM books b LEFT JOIN borrows br ON br.book_id=b.id GROUP BY b.id ORDER BY borrows DESC LIMIT 12";
      $r = $mysqli->query($q);
      if($r) $popularBooks = $r->fetch_all(MYSQLI_ASSOC);

      // New books: by newest id
      $newBooks = [];
      $r2 = $mysqli->query("SELECT id,title,author,cover_path FROM books ORDER BY id DESC LIMIT 12");
      if($r2) $newBooks = $r2->fetch_all(MYSQLI_ASSOC);

      // Recommended: by average rating
      $recBooks = [];
      $r3 = $mysqli->query("SELECT b.id,b.title,b.author,b.cover_path,IFNULL(AVG(r.rating),0) as avg_rating FROM books b LEFT JOIN reviews r ON r.book_id=b.id GROUP BY b.id ORDER BY avg_rating DESC LIMIT 12");
      if($r3) $recBooks = $r3->fetch_all(MYSQLI_ASSOC);
    ?>

    <div class="smart-book-slider">
      <div class="smart-tabs" role="tablist" aria-label="Buku Pilihan Tabs">
        <button class="tab active" data-type="popular" role="tab">Populer</button>
        <button class="tab" data-type="new" role="tab">Baru</button>
        <button class="tab" data-type="rec" role="tab">Rekomendasi</button>
      </div>

      <div class="smart-carousels">
        <div class="carousel active" data-type="popular">
          <button class="car-prev" aria-label="Sebelumnya">‹</button>
          <div class="carousel-track">
            <?php foreach($popularBooks as $b): ?>
              <div class="slide-card">
                <a href="/perpustakaan/book_view.php?id=<?php echo intval($b['id']); ?>">
                  <img src="<?php echo htmlspecialchars($b['cover_path']?:'/perpustakaan/assets/img/book-placeholder.png'); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>"/>
                  <div class="sc-title"><?php echo htmlspecialchars($b['title']); ?></div>
                  <div class="sc-meta"><?php echo htmlspecialchars($b['author']); ?></div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="car-next" aria-label="Berikutnya">›</button>
        </div>

        <div class="carousel" data-type="new">
          <button class="car-prev" aria-label="Sebelumnya">‹</button>
          <div class="carousel-track">
            <?php foreach($newBooks as $b): ?>
              <div class="slide-card">
                <a href="/perpustakaan/book_view.php?id=<?php echo intval($b['id']); ?>">
                  <img src="<?php echo htmlspecialchars($b['cover_path']?:'/perpustakaan/assets/img/book-placeholder.png'); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>"/>
                  <div class="sc-title"><?php echo htmlspecialchars($b['title']); ?></div>
                  <div class="sc-meta"><?php echo htmlspecialchars($b['author']); ?></div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="car-next" aria-label="Berikutnya">›</button>
        </div>

        <div class="carousel" data-type="rec">
          <button class="car-prev" aria-label="Sebelumnya">‹</button>
          <div class="carousel-track">
            <?php foreach($recBooks as $b): ?>
              <div class="slide-card">
                <a href="/perpustakaan/book_view.php?id=<?php echo intval($b['id']); ?>">
                  <img src="<?php echo htmlspecialchars($b['cover_path']?:'/perpustakaan/assets/img/book-placeholder.png'); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>"/>
                  <div class="sc-title"><?php echo htmlspecialchars($b['title']); ?></div>
                  <div class="sc-meta"><?php echo htmlspecialchars($b['author']); ?></div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="car-next" aria-label="Berikutnya">›</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Combined Categories & Books Section -->
<section class="section">
  <div class="container-fluid">
    <h2>Kategori & Buku</h2>
    <p>Telusuri kategori di sebelah kiri dan lihat buku terkait di sebelah kanan. Klik kategori untuk memfilter.</p>
    <?php
      // load categories
      $cats = [];
      $r = $mysqli->query("SELECT id,name,description,thumb_path FROM categories ORDER BY name ASC");
      if($r) $cats = $r->fetch_all(MYSQLI_ASSOC);
      $selected_cat = isset($_GET['category']) ? intval($_GET['category']) : 0;
      // determine selected category name for header
      $selected_cat_name = '';
      if($selected_cat && !empty($cats)){
        foreach($cats as $cc){ if(intval($cc['id']) === $selected_cat){ $selected_cat_name = $cc['name']; break; } }
      }
      // load books; if a category is selected, join book_category
      if($selected_cat){
        $stmt = $mysqli->prepare('SELECT b.id,b.title,b.author,b.publication_year,b.cover_path FROM books b JOIN book_category bc ON b.id=bc.book_id WHERE bc.category_id=? GROUP BY b.id ORDER BY b.id DESC LIMIT 24');
        if($stmt){ $stmt->bind_param('i', $selected_cat); $stmt->execute(); $res = $stmt->get_result(); $books = $res->fetch_all(MYSQLI_ASSOC); $stmt->close(); } else { $books = []; }
      } else {
        $books = $mysqli->query('SELECT id,title,author,publication_year,cover_path FROM books ORDER BY id DESC LIMIT 24')->fetch_all(MYSQLI_ASSOC);
      }
    ?>

    <div class="combined-grid" style="display:grid;grid-template-columns:280px 1fr;gap:18px;align-items:start">
      <aside>
        <div class="panel">
          <h3 style="margin-top:0">Kategori</h3>
          <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px">
            <a href="/perpustakaan/" class="btn btn-outline category-link<?php echo $selected_cat? '':' category-active'; ?>" data-category="0" style="text-align:left;">Semua Kategori</a>
            <?php foreach($cats as $c): $cid=intval($c['id']);
                // count books in category
                $countR = $mysqli->prepare('SELECT COUNT(DISTINCT b.id) as c FROM books b JOIN book_category bc ON b.id=bc.book_id WHERE bc.category_id=?');
                $catCount = 0;
                if($countR){ $countR->bind_param('i',$cid); $countR->execute(); $rr = $countR->get_result(); if($rr) $catCount = intval($rr->fetch_assoc()['c']); $countR->close(); }
            ?>
              <a href="/perpustakaan/?category=<?php echo $cid; ?>" class="btn btn-ghost category-link<?php echo ($selected_cat===$cid)?' category-active':''; ?>" data-category="<?php echo $cid; ?>" style="text-align:left;display:flex;gap:10px;align-items:center;">
                <img src="<?php echo htmlspecialchars($c['thumb_path']?:'/perpustakaan/assets/img/book-placeholder.png'); ?>" alt="" style="width:40px;height:48px;object-fit:cover;border-radius:6px;flex-shrink:0">
                <span style="flex:1"><?php echo htmlspecialchars($c['name']); ?></span>
                <span style="color:var(--muted);font-size:13px;margin-left:6px"><?php echo $catCount; ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>

      <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <h3 style="margin:0">Buku<?php echo $selected_cat_name ? ' — '.htmlspecialchars($selected_cat_name) : ''; ?></h3>
          <?php if($selected_cat): ?><a class="btn btn-outline" href="/perpustakaan/catalog.php?category=<?php echo $selected_cat; ?>">Lihat Semua di Katalog</a><?php endif; ?>
        </div>

        <div id="booksGrid" class="catalog-grid">
          <?php if(empty($books)): ?>
            <div class="panel">Tidak ada buku ditemukan untuk kategori ini.</div>
          <?php else: foreach($books as $b): $bid = intval($b['id']); ?>
            <div class="book-card">
              <img class="book-cover" src="<?php echo htmlspecialchars($b['cover_path']?:'/perpustakaan/assets/img/book-placeholder.png'); ?>" alt="cover">
              <h3 style="margin:0 0 6px"><?php echo htmlspecialchars($b['title']); ?></h3>
              <div class="book-meta"><?php echo htmlspecialchars($b['author']) ?> • <?php echo intval($b['publication_year']) ?></div>
              <div style="margin-top:10px">
                <a class="btn btn-primary" href="/perpustakaan/book_view.php?id=<?php echo $bid; ?>">Detail</a>
                <a class="btn btn-outline" href="/perpustakaan/catalog.php?id=<?php echo $bid; ?>">Lihat di Katalog</a>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
        <div style="text-align:center;margin-top:16px">
          <button id="loadMoreBtn" class="btn btn-outline" data-page="1" style="display:none">Muat Lebih</button>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// AJAX category filtering and load-more
(function(){
  var catLinks = document.querySelectorAll('.category-link');
  var booksGrid = document.getElementById('booksGrid');
  var loadMore = document.getElementById('loadMoreBtn');
  var currentCategory = <?php echo json_encode($selected_cat); ?>;
  var currentPage = 1;

  function renderBooks(items, replace){
    var html = '';
    if(!items || items.length===0){ html = '<div class="panel">Tidak ada buku ditemukan untuk kategori ini.</div>'; }
    else{
      items.forEach(function(b){
        var cover = b.cover_path? b.cover_path : '/perpustakaan/assets/img/book-placeholder.png';
        html += '<div class="book-card">' +
                '<img class="book-cover" src="'+escapeHtml(cover)+'" alt="cover">' +
                '<h3 style="margin:0 0 6px">'+escapeHtml(b.title)+'</h3>' +
                '<div class="book-meta">'+escapeHtml(b.author)+' • '+(b.publication_year?parseInt(b.publication_year):'')+'</div>' +
                '<div style="margin-top:10px">' +
                '<a class="btn btn-primary" href="/perpustakaan/book_view.php?id='+parseInt(b.id)+'">Detail</a> ' +
                '<a class="btn btn-outline" href="/perpustakaan/catalog.php?id='+parseInt(b.id)+'">Lihat di Katalog</a>' +
                '</div></div>';
      });
    }
    if(replace) booksGrid.innerHTML = html; else booksGrid.insertAdjacentHTML('beforeend', html);
    // animate newly added cards
    try{
      var count = (items && items.length) ? items.length : 0;
      if(count>0){
        // small timeout to allow DOM insertion
        setTimeout(function(){
          // collect new cards
          var all = Array.from(booksGrid.querySelectorAll('.book-card'));
          var newCards = replace ? all : all.slice(-count);
          newCards.forEach(function(card, idx){
            card.classList.remove('show');
            setTimeout(function(){ card.classList.add('show'); }, idx*80);
          });
        }, 40);
      }
    }catch(e){ console.error(e); }
  }

  function escapeHtml(s){ if(!s) return ''; return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  function fetchBooks(category,page,append){
    // show skeleton placeholders while loading
    try{
      var skCount = 6;
      var skHtml = '';
      for(var i=0;i<skCount;i++){
        skHtml += '<div class="skeleton-card loading-skel">\n'
               + '<div class="skeleton-cover"></div>\n'
               + '<div class="skeleton-line short"></div>\n'
               + '<div class="skeleton-line mid"></div>\n'
               + '</div>';
      }
      if(append){ booksGrid.insertAdjacentHTML('beforeend', skHtml); }
      else { booksGrid.innerHTML = skHtml; }
    }catch(e){ console.error(e); }

    // set loadMore button to loading state (if present)
    if(loadMore){
      try{
        loadMore.disabled = true;
        loadMore.innerHTML = 'Memuat<span class="loading-spinner" aria-hidden="true"></span>';
      }catch(e){}
    }

    fetch('/perpustakaan/api/get_books.php?category='+encodeURIComponent(category)+'&page='+encodeURIComponent(page))
      .then(function(r){ return r.json(); })
      .then(function(data){
        // remove any loading skeletons before rendering real items
        try{ Array.from(booksGrid.querySelectorAll('.loading-skel')).forEach(function(n){ n.remove(); }); }catch(e){}
        if(page===1 && !append) renderBooks(data.books, true);
        else renderBooks(data.books, false);
        currentPage = data.page;
        if(data.has_more){ if(loadMore){ loadMore.style.display='inline-block'; loadMore.dataset.page = (data.page+1); loadMore.disabled = false; loadMore.innerHTML = 'Muat Lebih'; } }
        else { if(loadMore){ loadMore.style.display='none'; loadMore.disabled = false; loadMore.innerHTML = 'Muat Lebih'; } }
      }).catch(function(err){ console.error(err); // remove skeletons on error
        try{ Array.from(booksGrid.querySelectorAll('.loading-skel')).forEach(function(n){ n.remove(); }); }catch(e){}
        if(loadMore){ loadMore.disabled = false; loadMore.innerHTML = 'Muat Lebih'; }
      });
  }

  catLinks.forEach(function(a){ a.addEventListener('click', function(e){ e.preventDefault(); var cat = parseInt(this.dataset.category||0); currentCategory=cat; // update active class
      catLinks.forEach(function(x){ x.classList.remove('category-active'); }); this.classList.add('category-active');
      // reset grid and fetch first page
      fetchBooks(cat,1,false);
    }); });

  if(loadMore){ loadMore.addEventListener('click', function(e){ e.preventDefault(); var p = parseInt(this.dataset.page||1); fetchBooks(currentCategory,p,true); }); }

  // If initial page had selected category, ensure correct Load More visibility by hitting API
  (function init(){
    // set active class on initial selection
    catLinks.forEach(function(a){ if(parseInt(a.dataset.category||0)===currentCategory) a.classList.add('category-active'); });
    // check if more exists
    fetch('/perpustakaan/api/get_books.php?category='+encodeURIComponent(currentCategory)+'&page=1').then(r=>r.json()).then(function(d){ if(d.has_more){ loadMore.style.display='inline-block'; loadMore.dataset.page = 2; } else { if(loadMore) loadMore.style.display='none'; } }).catch(()=>{});
  })();
})();

// Animate any server-rendered book cards on initial load
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    try{
      var grid = document.getElementById('booksGrid');
      if(!grid) return;
      var cards = Array.from(grid.querySelectorAll('.book-card'));
      cards.forEach(function(card, idx){ card.classList.remove('show'); setTimeout(function(){ card.classList.add('show'); }, idx*60); });
    }catch(e){ }
  });
})();
</script>

<script>
// Smart book slider JS: tabs, horizontal carousel with prev/next and auto-scroll
(function(){
  function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
  function qsa(sel, ctx){ return Array.from((ctx||document).querySelectorAll(sel)); }

  var tabButtons = qsa('.smart-tabs .tab');
  var carousels = qsa('.smart-carousels .carousel');

  function switchTab(type){
    tabButtons.forEach(function(b){ b.classList.toggle('active', b.dataset.type===type); });
    carousels.forEach(function(c){ c.classList.toggle('active', c.dataset.type===type); });
  }

  tabButtons.forEach(function(b){ b.addEventListener('click', function(){ switchTab(this.dataset.type); }); });

  // carousel behavior
  carousels.forEach(function(car){
    var track = car.querySelector('.carousel-track');
    var prev = car.querySelector('.car-prev');
    var next = car.querySelector('.car-next');
    var autoInterval = null;

    function scrollByCard(dir){
      if(!track) return;
      var card = track.querySelector('.slide-card');
      if(!card) return;
      var cardW = card.getBoundingClientRect().width + 12; // gap approx
      track.scrollBy({ left: dir * cardW, behavior: 'smooth' });
    }

    if(prev) prev.addEventListener('click', function(){ scrollByCard(-1); resetAuto(); });
    if(next) next.addEventListener('click', function(){ scrollByCard(1); resetAuto(); });

    function startAuto(){ stopAuto(); autoInterval = setInterval(function(){ track.scrollBy({ left: 280, behavior: 'smooth' }); }, 4200); }
    function stopAuto(){ if(autoInterval) { clearInterval(autoInterval); autoInterval = null; } }
    function resetAuto(){ stopAuto(); setTimeout(startAuto, 2200); }

    car.addEventListener('mouseenter', stopAuto);
    car.addEventListener('mouseleave', startAuto);

    // ensure nice keyboard accessibility for prev/next
    [prev,next].forEach(function(btn){ if(btn) btn.setAttribute('tabindex',0); });

    // start autoplay only for visible carousel
    if(car.classList.contains('active')) startAuto();

    // when tab change, start/stop autoplay appropriately
    document.addEventListener('click', function(e){
      var active = car.classList.contains('active');
      if(active && !autoInterval) startAuto();
      if(!active && autoInterval) stopAuto();
    });
  });
})();
</script>

<!-- Background rotator removed to keep hero background static per user request. -->

<?php include 'inc/footer.php'; ?>
<?php
// Debug helper: enable by adding ?debug_slider=1 to the URL. This will outline slider elements
// and show computed background-color / z-index to help find overlays.
if(isset($_GET['debug_slider']) && $_GET['debug_slider'] == '1'){
  ?>
  <script>
  document.addEventListener('DOMContentLoaded', function(){
    var elems = ['#smartSliderModern','.smart-slider-modern','.smart-slider-modern .ss-bg','.smart-slider-modern .ss-inner','.smart-slider-modern .ss-viewport','.smart-slider-modern .ss-track','main','body','.container-fluid','.section','.panel'];
    var info = document.createElement('div');
    info.style.position='fixed'; info.style.left='8px'; info.style.bottom='8px'; info.style.zIndex=999999; info.style.maxWidth='420px'; info.style.padding='10px'; info.style.background='rgba(0,0,0,0.65)'; info.style.color='#fff'; info.style.fontSize='12px'; info.style.fontFamily='Inter,Arial,Helvetica,sans-serif'; info.style.borderRadius='8px'; info.id='__slider_debug'; document.body.appendChild(info);
    info.innerHTML = '<strong>Slider Debug</strong><br/>' + location.href + '<hr/>';

    function inspect(sel){
      try{
        var el = document.querySelector(sel);
        if(!el) return sel+': missing<br/>';
        var cs = window.getComputedStyle(el);
        var bg = cs.backgroundColor || cs.background || '';
        var zi = cs.zIndex || '';
        var op = cs.opacity || '';
        return sel+': bg=' + bg + ' / z=' + zi + ' / op=' + op + '<br/>';
      }catch(e){
        return sel+': error<br/>';
      }
    }

    elems.forEach(function(s){
      try{
        var res = inspect(s);
        info.innerHTML += res;
        var el = document.querySelector(s);
        if(el){ el.style.outline = '3px dashed rgba(255,200,0,0.9)'; el.style.outlineOffset = '-4px'; }
      }catch(e){}
    });

    // also scan all children of slider for any non-transparent backgrounds
    var slider = document.getElementById('smartSliderModern');
    if(slider){
      var nodes = slider.querySelectorAll('*');
      nodes.forEach(function(n){
        var cs = getComputedStyle(n);
        var bg = cs.backgroundColor;
        if(bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent'){
          n.style.boxShadow='0 0 0 3px rgba(255,0,0,0.14) inset';
        }
      });
    }
  }, false);
  </script>
  <?php
}
?>