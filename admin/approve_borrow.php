<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){
    echo json_encode(['ok'=>0,'msg'=>'Unauthorized']); exit;
}
$id = isset($_POST['id'])? intval($_POST['id']) : 0;
if(!$id){ echo json_encode(['ok'=>0,'msg'=>'Invalid id']); exit; }
$r = $mysqli->query("SELECT * FROM borrows WHERE id=$id")->fetch_assoc();
if(!$r){ echo json_encode(['ok'=>0,'msg'=>'Record not found']); exit; }
if($r['status'] !== 'requested'){ echo json_encode(['ok'=>0,'msg'=>'Not a requested record']); exit; }
$book_id = intval($r['book_id']);
$b = $mysqli->query("SELECT stock FROM books WHERE id=$book_id")->fetch_assoc();
if(!$b || $b['stock']<=0){ echo json_encode(['ok'=>0,'msg'=>'Insufficient stock']); exit; }

// approve: set to borrowed, set borrowed_at and due_at, decrement stock
$mysqli->query("UPDATE borrows SET status='borrowed', borrowed_at=NOW(), due_at=DATE_ADD(NOW(), INTERVAL 14 DAY) WHERE id=$id");
$mysqli->query("UPDATE books SET stock=stock-1 WHERE id=$book_id");

// insert notification for the user
$user_id = intval($r['user_id']);
$book = $mysqli->query("SELECT title FROM books WHERE id=$book_id")->fetch_assoc();
$title = $book? $mysqli->real_escape_string($book['title']) : 'buku';
$msg = "Permintaan pinjam untuk \"" . $title . "\" telah disetujui. Silakan cek riwayat peminjaman Anda.";
$link = '/perpustakaan/my_borrows.php';
$stmt = $mysqli->prepare('INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)');
if($stmt){ $stmt->bind_param('iss', $user_id, $msg, $link); $stmt->execute(); $stmt->close(); }

echo json_encode(['ok'=>1,'msg'=>'Approved','id'=>$id,'book_id'=>$book_id]);
exit;
?>
