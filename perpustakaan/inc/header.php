<?php
// inc/header.php 
require_once __DIR__ . '/config.php';
$base = '/perpustakaan/';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo isset($page_title)?htmlspecialchars($page_title)." - Perpustakaan Dharma Bahari Surabaya":'Perpustakaan Dharma Bahari Surabaya'; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $base; ?>assets/css/style_clean.css">
<link rel="stylesheet" href="<?php echo $base; ?>assets/css/theme_dashboard.css">
<link rel="stylesheet" href="<?php echo $base; ?>assets/css/header.css">
<link rel="stylesheet" href="<?php echo $base; ?>assets/css/footer.css">
<?php
if(isset($page)){
  if($page === 'index') echo '<link rel="stylesheet" href="'.$base.'assets/css/index.css">';
  if($page === 'index') echo '<link rel="stylesheet" href="'.$base.'assets/css/slider-modern.css">';
  if($page === 'catalog') echo '<link rel="stylesheet" href="'.$base.'assets/css/catalog.css">';
  if($page === 'categories') echo '<link rel="stylesheet" href="'.$base.'assets/css/categories.css">';
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php if(isset($extra_head)) echo $extra_head; ?>
</head>
<body<?php if(isset($extra_body_class)) echo ' class="'.htmlspecialchars($extra_body_class, ENT_QUOTES).'"'; ?>>
<header class="header-top">
  <div class="container-fluid navbar">
    <a class="brand" href="<?php echo $base; ?>index.php" title="Perpustakaan Dharma Bahari Surabaya">
      <img src="<?php echo $base; ?>assets/img/dbs.png" alt="Perpustakaan Dharma Bahari Surabaya" class="brand-logo" loading="lazy" width="44" height="44" decoding="async">
      <div class="brand-text">
        <span class="brand-name">Readify</span>
      </div>
    </a>
    <nav class="nav-links" role="navigation">
      <a href="<?php echo $base; ?>index.php">Home</a>
      <a href="<?php echo $base; ?>catalog.php">Daftar Buku</a>
      <a href="<?php echo $base; ?>announcements.php">Pengumuman</a>
      <?php if(isset($_SESSION['user'])): ?>
        <?php if(isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
          <a href="<?php echo $base; ?>admin/dashboard.php" title="Dashboard" class="nav-icon" style="margin-left:8px">
            <i class="fa-solid fa-chart-simple"></i>
            <span class="visually-hidden">Dashboard</span>
          </a>
        <?php endif; ?>

        <a href="<?php echo $base; ?>profile.php" title="Profil" class="nav-icon" style="margin-left:8px">
          <?php if(!empty($_SESSION['user']['avatar_path'])): ?>
            <img src="<?php echo $base . 'uploads/avatars/' . htmlspecialchars($_SESSION['user']['avatar_path']); ?>" alt="avatar" class="nav-avatar">
          <?php else: ?>
            <i class="fa-solid fa-user"></i>
          <?php endif; ?>
          <span class="visually-hidden">Profil</span>
        </a>

        <a href="<?php echo $base; ?>logout.php" title="Keluar" class="nav-icon" style="margin-left:8px">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span class="visually-hidden">Keluar</span>
        </a>
      <?php else: ?>
        <a href="<?php echo $base; ?>login.php" title="Masuk" class="nav-icon" style="margin-left:8px">
          <i class="fa-solid fa-right-to-bracket"></i>
          <span class="visually-hidden">Masuk</span>
        </a>
      <?php endif; ?>

      <button id="navSearchToggle" class="nav-icon" title="Cari" type="button" style="margin-left:8px;position:relative;">
        <i class="fa-solid fa-magnifying-glass" style="font-size:20px;"></i>
        <span class="visually-hidden">Cari</span>
      </button>
    </nav>
  </div>
  <div id="navSearchPanel" class="nav-search-wrapper" aria-hidden="true" style="display:none;position:absolute;top:60px;right:0;z-index:1000;width:100%;max-width:400px;padding:24px 24px 18px 24px;background:#fff;box-shadow:0 8px 32px rgba(3,24,40,0.14);border-radius:0 0 18px 18px;">
    <form action="<?php echo $base; ?>catalog.php" method="get" style="display:flex;gap:8px;align-items:center;background:#3498db;padding:10px 10px 10px 16px;border-radius:12px;box-shadow:0 4px 18px rgba(52,152,219,0.18);width:100%;">
      <input type="text" name="q" class="nav-search-input" placeholder="Cari buku..." autocomplete="off" style="flex:1;padding:12px 16px;border-radius:8px;border:2px solid #2176bd;background:#fff;color:#111;font-size:17px;">
      <button type="submit" class="btn btn-outline" style="background:#2176bd;color:#fff;border:none;padding:12px 18px;border-radius:8px;"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <button type="button" style="margin-top:10px;background:none;border:none;color:#fff;cursor:pointer;font-size:15px;float:right;" onclick="document.getElementById('navSearchPanel').style.display='none'">Tutup</button>
    <style>
    #navSearchPanel {
      background: transparent !important;
      box-shadow: none !important;
      transition: opacity .35s cubic-bezier(.4,0,.2,1), transform .35s cubic-bezier(.4,0,.2,1);
      opacity: 0;
      transform: translateY(-18px) scale(.98);
      pointer-events: none;
    }
    #navSearchPanel[aria-hidden="false"] {
      opacity: 1;
      transform: translateY(0) scale(1);
      pointer-events: auto;
    }
    #navSearchPanel button[type="button"] {
      color: #222 !important;
    }
    </style>
  </div>
</header>
<main class="container-fluid">
<script src="<?php echo $base; ?>assets/js/nav_search.js"></script>
