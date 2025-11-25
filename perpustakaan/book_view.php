<?php require_once __DIR__ . '/config.php'; include 'inc/header.php'; $id = isset($_GET['id'])?intval($_GET['id']):0; $res=$mysqli->query("SELECT b.*, GROUP_CONCAT(c.name SEPARATOR ', ') as categories FROM books b LEFT JOIN book_category bc ON b.id=bc.book_id LEFT JOIN categories c ON bc.category_id=c.id WHERE b.id=$id GROUP BY b.id"); $b=$res->fetch_assoc(); ?>
<main class="container-fluid">
  <?php if(!$b){ echo '<p>Book not found</p>'; include 'inc/footer.php'; exit; } ?>
  <div class="book-detail">
    <img src="<?php echo $b['cover_path']?esc($b['cover_path']):'/assets/img/book-placeholder.png'; ?>" class="cover-large">
    <div class="info">
      <h1><?php echo esc($b['title']); ?></h1>
      <p><strong>Author:</strong> <?php echo esc($b['author']); ?></p>
      <p><strong>Publisher:</strong> <?php echo esc($b['publisher']); ?> (<?php echo intval($b['publication_year']); ?>)</p>
      <p><strong>ISBN:</strong> <?php echo esc($b['isbn']); ?></p>
      <p><strong>Categories:</strong>
        <?php
          $cats_arr = $b['categories']?explode(', ',$b['categories']):[];
          foreach($cats_arr as $c){ echo '<span class="chip">'.esc($c).'</span> '; }
        ?>
      </p>
      <div class="desc-short">
        <?php echo nl2br(esc(mb_strimwidth($b['description'],0,600,'...'))); ?>
        <?php if(mb_strlen($b['description'])>600): ?>
          <a href="#" id="readMore">Lebih lanjut</a>
        <?php endif; ?>
      </div>
      <div class="desc-full hidden">
        <?php echo nl2br(esc($b['description'])); ?>
        <p><a href="#" id="readLess">Tutup</a></p>
      </div>
      <div class="book-actions">
        <?php if($b['pdf_path']): ?><a class="btn btn-download" href="<?php echo esc($b['pdf_path']); ?>" download><svg viewBox="0 0 24 24" width="18" height="18"><path d="M5 20h14v-2H5v2zM12 3v10l3-3h-2V3h-2z"/></svg> Unduh</a><?php endif; ?>
        <?php if(isset($_SESSION['user'])): ?>
            <?php if(intval($b['stock'])>0): ?>
                <?php
                  $already_requested = false; $already_borrowed = false;
                  if(isset($_SESSION['user'])){
                    $uid = intval($_SESSION['user']['id']);
                    $q = $mysqli->query("SELECT status FROM borrows WHERE user_id=$uid AND book_id=".intval($b['id'])." ORDER BY id DESC LIMIT 1");
                    if($q && $rowq=$q->fetch_assoc()){
                      if($rowq['status']=='requested') $already_requested = true;
                      if($rowq['status']=='borrowed') $already_borrowed = true;
                    }
                  }
                ?>
                <?php if($already_requested): ?>
                  <div class="stock-badge">Permintaan pinjam terkirim (menunggu persetujuan)</div>
                <?php elseif($already_borrowed): ?>
                  <div class="stock-badge">Anda sedang meminjam buku ini</div>
                <?php elseif(intval($b['stock'])>0): ?>
                  <div class="book-meta">
                    <?php
                      // compute average rating
                      $avg = 0; $rv = $mysqli->query("SELECT AVG(rating) as a, COUNT(*) as c FROM reviews WHERE book_id={$b['id']}")->fetch_assoc();
                      if($rv && $rv['c']>0) $avg = floatval($rv['a']);
                    ?>
                    <div class="rating">
                      <?php for($i=1;$i<=5;$i++){ $cl = ($i <= round($avg))? 'on' : ''; echo '<span class="star '.$cl.'">★</span>'; } ?>
                      <span class="rating-count"><?php echo intval($rv['c']); ?> ulasan</span>
                    </div>
                  </div>
            <form method="post" action="borrow.php" style="display:inline"><input type="hidden" name="book_id" value="<?php echo $b['id']; ?>"><button class="btn btn-primary" type="submit"><svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 2l4 4h-3v6h-2V6H8l4-4zM5 22h14v-2H5v2z"/></svg> Pinjam</button></form>
          <?php else: ?>
            <div class="stock-badge out">Stok habis</div>
          <?php endif; ?>
        <?php else: ?>
          <a class="btn btn-outline" href="login.php">Masuk untuk pinjam</a>
        <?php endif; ?>
      </div>
      <?php else: ?>
        <p>Please login to borrow.</p>
      <?php endif; ?>
    </div>
  </div>

  <section class="reviews">
    <h3>Reviews & Ratings</h3>
    <?php
      if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['rating']) && isset($_POST['comment']) && isset($_SESSION['user'])){
        $rating = intval($_POST['rating']); $comment = $mysqli->real_escape_string($_POST['comment']); $uid = intval($_SESSION['user']['id']);
        $mysqli->query("INSERT INTO reviews (user_id,book_id,rating,comment,created_at) VALUES ($uid,$id,$rating,'$comment',NOW())");
        header('Location: book_view.php?id='.$id);
        exit;
      }
      $rev = $mysqli->query("SELECT r.*, u.username FROM reviews r LEFT JOIN users u ON r.user_id=u.id WHERE r.book_id=$id ORDER BY r.created_at DESC");
      while($row=$rev->fetch_assoc()){
        echo '<div class="rev"><strong>'.esc($row['username']).'</strong> - '.intval($row['rating']).'/5<p>'.nl2br(esc($row['comment'])).'</p><small>'.esc($row['created_at']).'</small></div>';
      }
    ?>
    <?php if(isset($_SESSION['user'])): ?>
      <form method="post" class="review-form">
        <label>Rating<select name="rating"><?php for($i=5;$i>=1;$i--) echo "<option>$i</option>"; ?></select></label>
        <label>Comment<textarea name="comment" required></textarea></label>
        <button class="btn" type="submit">Submit Review</button>
      </form>
    <?php else: ?><p>Login to leave a review.</p><?php endif; ?>
  </section>
</main>
<?php include 'inc/footer.php'; ?>