<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page-1)*$perPage;

$books = [];
$total = 0;
if($category){
    // count
    $cntStmt = $mysqli->prepare('SELECT COUNT(DISTINCT b.id) as c FROM books b JOIN book_category bc ON b.id=bc.book_id WHERE bc.category_id=?');
    if($cntStmt){ $cntStmt->bind_param('i',$category); $cntStmt->execute(); $r = $cntStmt->get_result(); $total = $r?$r->fetch_assoc()['c']:0; $cntStmt->close(); }
    $stmt = $mysqli->prepare('SELECT DISTINCT b.id,b.title,b.author,b.publication_year,b.cover_path FROM books b JOIN book_category bc ON b.id=bc.book_id WHERE bc.category_id=? ORDER BY b.id DESC LIMIT ? OFFSET ?');
    if($stmt){ $stmt->bind_param('iii',$category,$perPage,$offset); $stmt->execute(); $res = $stmt->get_result(); if($res) $books = $res->fetch_all(MYSQLI_ASSOC); $stmt->close(); }
} else {
    $r = $mysqli->prepare('SELECT COUNT(*) as c FROM books');
    if($r){ $r->execute(); $rr = $r->get_result(); $total = $rr?$rr->fetch_assoc()['c']:0; $r->close(); }
    $stmt = $mysqli->prepare('SELECT id,title,author,publication_year,cover_path FROM books ORDER BY id DESC LIMIT ? OFFSET ?');
    if($stmt){ $stmt->bind_param('ii',$perPage,$offset); $stmt->execute(); $res = $stmt->get_result(); if($res) $books = $res->fetch_all(MYSQLI_ASSOC); $stmt->close(); }
}

$has_more = ($offset + count($books)) < intval($total);

echo json_encode(['books'=>$books,'page'=>$page,'per_page'=>$perPage,'total'=>intval($total),'has_more'=>$has_more]);
exit;

?>
