<?php
require_once __DIR__ . '/inc/config.php';
header('Content-Type: application/json');
if(!isset($_SESSION['user'])){ echo json_encode(['ok'=>0,'msg'=>'Unauthorized']); exit; }
$uid = intval($_SESSION['user']['id']);
$mysqli->query("UPDATE notifications SET is_read=1 WHERE user_id=$uid AND is_read=0");
echo json_encode(['ok'=>1]);
exit;
?>
