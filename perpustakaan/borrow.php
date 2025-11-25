<?php
require_once __DIR__ . '/config.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
  header('Location: catalog.php'); exit;
}

if(empty($_SESSION['user'])){
  $_SESSION['msg'] = 'Silakan login untuk meminjam buku.';
  header('Location: login.php'); exit;
}

$book_id = isset($_POST['book_id'])? intval($_POST['book_id']) : 0;
if(!$book_id){ $_SESSION['msg'] = 'ID buku tidak valid.'; header('Location: catalog.php'); exit; }

$b = $mysqli->query("SELECT id,stock FROM books WHERE id=". $book_id)->fetch_assoc();
if(!$b){ $_SESSION['msg'] = 'Buku tidak ditemukan.'; header('Location: catalog.php'); exit; }
if(intval($b['stock']) <= 0){ $_SESSION['msg'] = 'Maaf, buku sedang tidak tersedia (stok 0).'; header('Location: book_view.php?id='.$book_id); exit; }

$user_id = intval($_SESSION['user']['id']);

// create borrow request with 'requested' status; admin must approve. Do not decrement stock yet.
$ins = $mysqli->query("INSERT INTO borrows (user_id,book_id,created_at,status) VALUES ($user_id,$book_id,NOW(),'requested')");
if(!$ins){ $_SESSION['msg'] = 'Gagal membuat permintaan pinjam (DB error).'; header('Location: book_view.php?id='.$book_id); exit; }

$_SESSION['msg'] = 'Permintaan pinjam telah dibuat. Tunggu persetujuan admin.';
header('Location: book_view.php?id='.$book_id);
exit;

?>
