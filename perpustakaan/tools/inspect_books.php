<?php
// tools/inspect_books.php
// Temporary debug helper to inspect `books`, `book_category`, and `categories` tables.
// Open in browser: http://localhost/perpustakaan/tools/inspect_books.php

require_once __DIR__ . '/../inc/config.php';
header('Content-Type: text/plain; charset=utf-8');
echo "Inspecting database environment for books\n";

echo "\n-- MySQL server info --\n";
echo "Host: " . DB_HOST . "\n";
echo "DB: " . DB_NAME . "\n";

// counts
function safe_count($mysqli, $sql){
    $r = $mysqli->query($sql);
    if(!$r) return ['ok'=>0,'error'=>$mysqli->error];
    $row = $r->fetch_assoc();
    return ['ok'=>1,'count'=>intval($row[array_keys($row)[0]])];
}

$c1 = safe_count($mysqli, 'SELECT COUNT(*) as c FROM books');
$c2 = safe_count($mysqli, 'SELECT COUNT(*) as c FROM book_category');
$c3 = safe_count($mysqli, 'SELECT COUNT(*) as c FROM categories');

if(!$c1['ok']){ echo "Failed to count books: " . $c1['error'] . "\n"; }
else echo "Books rows: " . $c1['count'] . "\n";
if(!$c2['ok']){ echo "Failed to count book_category: " . $c2['error'] . "\n"; }
else echo "Book-Category rows: " . $c2['count'] . "\n";
if(!$c3['ok']){ echo "Failed to count categories: " . $c3['error'] . "\n"; }
else echo "Categories rows: " . $c3['count'] . "\n";

// show sample books
echo "\n-- Sample books (up to 12) --\n";
$res = $mysqli->query('SELECT id,title,author,publication_year,cover_path,pdf_path,stock,description FROM books ORDER BY id DESC LIMIT 12');
if(!$res){ echo "Query failed: " . $mysqli->error . "\n"; }
else{
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    if(empty($rows)){
        echo "(no rows)\n";
    } else {
        foreach($rows as $r){
            echo "ID: " . intval($r['id']) . "\n";
            echo " Title: " . ($r['title'] ?? '') . "\n";
            echo " Author: " . ($r['author'] ?? '') . "\n";
            echo " Year: " . ($r['publication_year'] ?? '') . "\n";
            echo " Stock: " . ($r['stock'] ?? '') . "\n";
            echo " cover_path (DB): " . ($r['cover_path'] ?? '') . "\n";
            // resolve to filesystem
            $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : rtrim(__DIR__, '/');
            $resolved = $r['cover_path'] ?? '';
            if($resolved){
                // if not absolute, try prefixing /perpustakaan/
                if(strpos($resolved, '/') !== 0 && !preg_match('#^https?://#i', $resolved)){
                    $resolved_web = '/perpustakaan/' . ltrim($resolved, '/');
                } else {
                    $resolved_web = $resolved;
                }
                $fs = $doc . $resolved_web;
                echo " resolve -> " . $resolved_web . "\n";
                echo " filesystem path: " . $fs . "\n";
                echo " exists: " . (file_exists($fs) ? 'YES' : 'NO') . "\n";
            }
            // categories for this book
            $bid = intval($r['id']);
            $cr = $mysqli->query("SELECT c.id,c.name FROM categories c JOIN book_category bc ON c.id=bc.category_id WHERE bc.book_id=$bid");
            if($cr && $cr->num_rows){
                $cnames = array_column($cr->fetch_all(MYSQLI_ASSOC), 'name');
                echo " categories: " . implode(', ', $cnames) . "\n";
            } else {
                echo " categories: (none)\n";
            }
            echo "-------------------------\n";
        }
    }
}

// show whether get_books.php would return any rows for category 0
echo "\n-- API get_books.php test (category=0,page=1) --\n";
ob_start();
$_GET['category'] = 0; $_GET['page']=1;
include __DIR__ . '/../api/get_books.php';
$api_out = ob_get_clean();
if(!$api_out) echo "(no output)\n"; else echo $api_out . "\n";

echo "\n-- End of diagnostics --\n";

?>