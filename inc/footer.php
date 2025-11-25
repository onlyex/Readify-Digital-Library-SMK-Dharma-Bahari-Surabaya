</main>
<footer class="site-footer">
  <div class="container-fluid footer-inner">
    <div class="footer-grid">
      <div class="footer-col">
        <h4 class="footer-brand">Perpustakaan Dharma Bahari Surabaya</h4>
        <p class="footer-desc"> Koleksi buku lengkap — cari, pinjam, dan jelajahi dunia ilmu pengetahuan. Sistem manajemen perpustakaan sederhana untuk komunitas Anda.</p>
      </div>
      <div class="footer-col">
        <h5>Quick Links</h5>
        <ul class="footer-links">
          <li><a href="/perpustakaan/index.php">Beranda</a></li>
          <li><a href="/perpustakaan/catalog.php">Katalog</a></li>
          <li><a href="/perpustakaan/announcements.php">Pengumuman</a></li>
          <li><a href="/perpustakaan/login.php">Masuk</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Kontak</h5>
        <p class="footer-contact">Email: <a href="mailto:admin@perpus.local">admin@perpus.local</a></p>
        <p class="footer-contact">Tel: +62 812 3456 7890</p>
      </div>
      <div class="footer-col">
        <h5>Ikuti Kami</h5>
        <div class="social-icons">
          <a href="#" aria-label="Twitter" class="si"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 5.8c-.6.3-1.3.6-2 .7.7-.4 1.2-1.1 1.5-1.9-.7.4-1.5.7-2.4.9-.7-.7-1.7-1.1-2.8-1.1-2.1 0-3.8 1.7-3.8 3.8 0 .3 0 .7.1 1-3.2-.2-6-1.7-7.9-4-.3.6-.5 1.3-.5 2 0 1.4.7 2.6 1.8 3.4-.6 0-1.2-.2-1.7-.5v.1c0 1.9 1.4 3.5 3.3 3.9-.3.1-.7.1-1 .1-.3 0-.6 0-.9-.1.6 1.8 2.4 3.1 4.5 3.2-1.6 1.3-3.6 2-5.7 2-.4 0-.8 0-1.2-.1 2.1 1.3 4.6 2.1 7.3 2.1 8.8 0 13.6-7.3 13.6-13.6v-.6c.9-.6 1.6-1.4 2.2-2.3-.8.3-1.6.6-2.5.7z" fill="currentColor"/></svg></a>
          <a href="#" aria-label="Facebook" class="si"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 12c0-5.5-4.5-10-10-10S2 6.5 2 12c0 5 3.7 9.1 8.5 9.9v-7H8.4v-2.9h2.1V9.2c0-2.1 1.2-3.3 3-3.3.9 0 1.8.1 1.8.1v2h-1c-1 0-1.3.6-1.3 1.3v1.6h2.3l-.4 2.9h-1.9v7C18.3 21.1 22 17 22 12z" fill="currentColor"/></svg></a>
          <a href="#" aria-label="Instagram" class="si"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 6.2a4 4 0 100 8 4 4 0 000-8zm4.6-.9a1.1 1.1 0 11-2.2 0 1.1 1.1 0 012.2 0zM12 9.6a2.4 2.4 0 110 4.8 2.4 2.4 0 010-4.8z" fill="currentColor"/></svg></a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
    <div>© <?php echo date('Y'); ?> Perpustakaan Dharma Bahari Surabaya — All rights reserved</div>
      <div class="footer-small">Dibuat dengan ♥ untuk komunitas pembaca.</div>
    </div>
  </div>
</footer>
<?php
if(!empty($_SESSION['msg'])){
  $m = esc($_SESSION['msg']);
  echo "<script>document.addEventListener('DOMContentLoaded',function(){ if(window.showToast) window.showToast('". addslashes($m) ."'); });</script>";
  unset($_SESSION['msg']);
}
?>
</main>
<script src="/perpustakaan/assets/js/app.js"></script>
<script src="/perpustakaan/assets/js/auth.js"></script>
<script src="/perpustakaan/assets/js/slider.js"></script>
<script src="/perpustakaan/assets/js/nav_search.js"></script>
<script src="/perpustakaan/assets/js/profile.js"></script>
<script src="/perpustakaan/assets/js/admin.js"></script>
<script src="/perpustakaan/assets/js/slider-modern.js"></script>
<script src="/perpustakaan/assets/js/catalog-ui.js"></script>
<!-- catalog-ajax.js disabled: server-side render is now primary -->
<script>
document.addEventListener('DOMContentLoaded',function(){
  var f = document.getElementById('flash-msg'); if(!f) return; setTimeout(function(){ f.style.transition='opacity .4s'; f.style.opacity='0'; setTimeout(()=>f.remove(),450); },4000);
});
</script>
</body>
</html>
