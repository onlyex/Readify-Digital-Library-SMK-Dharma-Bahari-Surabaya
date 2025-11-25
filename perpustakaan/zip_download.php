<?php
require_once __DIR__ . '/config.php';
include_once __DIR__ . '/inc/header.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: catalog.php'); exit;
}
if(empty($_POST['book_ids']) || !is_array($_POST['book_ids'])){
    header('Location: catalog.php'); exit;
}
$ids = array_map('intval', $_POST['book_ids']);
if(empty($ids)){ header('Location: catalog.php'); exit; }

$tmpDir = sys_get_temp_dir();
$zipName = 'books_'.time().'_'.rand(1000,9999).'.zip';
$zipPath = $tmpDir.DIRECTORY_SEPARATOR.$zipName;

$zip = new ZipArchive();
if($zip->open($zipPath, ZipArchive::CREATE)!==TRUE){
    die('Could not create ZIP file.');
}

$added = 0;
$stmt = $mysqli->prepare('SELECT id,title,pdf_path FROM books WHERE id IN ('.implode(',',array_fill(0,count($ids),'?')).')');
// bind params dynamically
$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()){
    if(!empty($row['pdf_path'])){
        // pdf_path expected like /uploads/pdfs/xxx.pdf or uploads/pdfs/xxx.pdf
        $p = ltrim($row['pdf_path'],'/');
        $fs = __DIR__ . '/' . $p;
        if(file_exists($fs) && is_readable($fs)){
            $localName = preg_replace('/[^a-zA-Z0-9_\-\.]/','_', $row['title']).'_'.$row['id'].'.pdf';
            $zip->addFile($fs, $localName);
            $added++;
        }
    }
}
$zip->close();

if($added===0){
    @unlink($zipPath);
    header('Location: catalog.php'); exit;
}

// stream zip to user
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="'.$zipName.'"');
header('Content-Length: '.filesize($zipPath));
readfile($zipPath);
@unlink($zipPath);
exit;
?>
<?php include_once __DIR__ . '/inc/footer.php'; ?>
